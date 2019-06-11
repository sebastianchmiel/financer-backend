<?php

namespace App\Controller\Api\Bookkeeping\Billing;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Bookkeeping\Billing\BillingPlannedItem;
use App\Repository\Bookkeeping\Billing\BillingPlannedItemRepository;
use App\Form\Bookkeeping\Billing\BillingPlannedItemType;
use App\Domain\Bookkeeping\Billing\Filter\BillingPlannedItemFilter;
use App\Domain\Bookkeeping\Billing\Type\TypeCollection;
use App\Domain\Bookkeeping\Billing\Type\Types\IncomeType;


/**
 * @Route("/api/bookkeeping/billing/planned-item")
 */
class BillingPlannedItemController extends AbstractFOSRestController implements ClassResourceInterface {

    /**
     * Gets a collection of the billing planned items
     * 
     * @Route("/", name="get_billing_planned_items", methods={"GET"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing planned item")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * ),
     * @QueryParam(name="currentPage", requirements="\d+", default="1", description="current page to show")
     * @QueryParam(name="perPage", requirements="\d+", default="50", description="Items count per page")
     * @QueryParam(name="sortBy", requirements="[a-zA-Z_]+", default="name", description="Field to sort by")
     * @QueryParam(name="sortDesc", requirements="(true|false)", default="false", description="Sort descrement order anabled")
     * @QueryParam(name="filter", default="{}", description="Serialized filters.")
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingPlannedItem::class)
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Returned when not found"
     * )
     * 
     * @Annotations\View(serializerGroups={
     *   "all"
     * })
     *
     * @return View
     */
    public function cgetAction(ParamFetcher $paramFetcher)
    {
        $result = [
            'totalRows' => 0,
            'items' => []
        ];
        
        try {
            $filter = new BillingPlannedItemFilter();
            $filter->readFromParamFetcher($paramFetcher);

            $result['totalRows'] = (int)$this->getRepository()->findByMultiParameters(true, $filter);
            $result['items'] = $this->getRepository()->findByMultiParameters(false, $filter);
            $result['types'] = (new TypeCollection())->getTypesData();
            
            return $result;
            
        } catch (\Exception $ex) {
            return new View('Wrong parameters ('.$ex->getMessage().')', Response::HTTP_BAD_REQUEST);
        }
        
        return $result;
    }
    
    /**
     * Create a single billing planned item
     *
     * @Route("/", methods={"POST"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing planned item")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="billing_planned_item",
     *     in="body",
     *     required=true,
     *     description="billing planned item data",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="dateFrom", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="dateTo", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="type", type="integer"),
     *          @SWG\Property(property="name", type="string"),
     *          @SWG\Property(property="date", type="string"),
     *          @SWG\Property(property="dateOfService", type="string"),
     *          @SWG\Property(property="dateOfPayment", type="string"),
     *          @SWG\Property(property="contractor", type="integer"),
     *          @SWG\Property(property="invoiceNumber", type="string"),
     *          @SWG\Property(property="description", type="string"),
     *          @SWG\Property(property="amountNet", type="integer"),
     *          @SWG\Property(property="amountGross", type="integer"),
     *          @SWG\Property(property="taxValue", type="integer"),
     *          @SWG\Property(property="taxPercent", type="integer"),
     *          @SWG\Property(property="paymentMethod", type="string"),
     *          @SWG\Property(property="onlyAsPattern", type="boolean"),
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingPlannedItem::class)
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returned when error"
     * )
     * 
     * @Annotations\View(serializerGroups={
     *   "summary"
     * })
     * 
     * @return View
     */
    public function itemPostAction(Request $request) {
        $parameters = $request->request->all();
        
        $item = new BillingPlannedItem();
        $form = $this->createForm(BillingPlannedItemType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_POST]);
        // remove ids
        unset($parameters['id']);
        if (isset($parameters['billingPlannedItemPositions']) && !empty($parameters['billingPlannedItemPositions'])) {
            foreach ($parameters['billingPlannedItemPositions'] as $key => $data) {
                unset($parameters['billingPlannedItemPositions'][$key]['id']);
            }
        }
        $form->submit($parameters);

        if (!$form->isValid()) {          
            return $form;
        }

        try {
            $this->getRepository()->save($item);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Contractor name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View($item, Response::HTTP_CREATED);
    }
    
    /**
     * Update a single billing planned item
     *
     * @Route("/", methods={"PATCH"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing planned item")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="billing_planned_item",
     *     in="body",
     *     required=true,
     *     description="billing planned item data",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="dateFrom", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="dateTo", type="date (YYYY-MM-DDD)"),
     *          @SWG\Property(property="type", type="integer"),
     *          @SWG\Property(property="date", type="string"),
     *          @SWG\Property(property="dateOfService", type="string"),
     *          @SWG\Property(property="dateOfPayment", type="string"),
     *          @SWG\Property(property="contractor", type="integer"),
     *          @SWG\Property(property="invoiceNumber", type="string"),
     *          @SWG\Property(property="description", type="string"),
     *          @SWG\Property(property="amountNet", type="integer"),
     *          @SWG\Property(property="amountGross", type="integer"),
     *          @SWG\Property(property="taxValue", type="integer"),
     *          @SWG\Property(property="taxPercent", type="integer"),
     *          @SWG\Property(property="paymentMethod", type="string"),
     *          @SWG\Property(property="onlyAsPattern", type="boolean"),
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=204,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingPlannedItem::class)
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returned when error"
     * )
     * 
     * @Annotations\View(serializerGroups={
     *   "summary"
     * })
     * 
     * @return View
     */
    public function itemPatchAction(Request $request) {
        $parameters = $request->request->all();
        
        // get params
        if (!isset($parameters['id'])) {
            return new View('Wrong parameters, id in contractor data is required', Response::HTTP_BAD_REQUEST);
        }
        $id = $parameters['id'];
        
        // get item
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            return new View('Billing planned item with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }
        
        $form = $this->createForm(BillingPlannedItemType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_PATCH]);
        unset($parameters['id']);
        // remove ids
        if ($parameters['type'] !== IncomeType::TYPE_ID) {
            $parameters['billingPlannedItemPositions'] = [];
        } else {
            if (isset($parameters['billingPlannedItemPositions']) && !empty($parameters['billingPlannedItemPositions'])) {
                foreach ($parameters['billingPlannedItemPositions'] as $key => $data) {
                    unset($parameters['billingPlannedItemPositions'][$key]['id']);
                }
            }
        }
        
        $form->submit($parameters);
        if (!$form->isValid()) {          
            return $form;
        }

        try {
            $this->getRepository()->save($item);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Contractor name should by unique', Response::HTTP_BAD_REQUEST);
        }
        
        return new View($item, Response::HTTP_CREATED);
    }
    
    
    /**
     * Delete existing Billing planned item 
     *
     * @Route("/{id}", methods={"DELETE"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing planned item")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     required=true,
     *     description="billing planned item id"
     * )
     * 
     * 
     * @SWG\Response(
     *     response=204,
     *     description="Returned when successful"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returned when error"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Returned when not found"
     * )
     * 
     * @Annotations\View(serializerGroups={
     *   "summary"
     * })
     * 
     * @return View
     */
    public function deleteAction(int $id)
    {
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            return new View('Billing item with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }
        
        $this->getRepository()->delete($item);

        return new View(null, Response::HTTP_NO_CONTENT);
    }
    
    /**
     * @return BillingPlannedItemRepository
     */
    private function getRepository(): BillingPlannedItemRepository {
        return $this->getDoctrine()->getManager()->getRepository(BillingPlannedItem::class);
    }
    
}
