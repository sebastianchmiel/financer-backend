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
use App\Entity\Bookkeeping\Billing\BillingYearConst;
use App\Repository\Bookkeeping\Billing\BillingYearConstRepository;
use App\Form\Bookkeeping\Billing\BillingYearConstType;
use App\Domain\Bookkeeping\Billing\Filter\BillingYearConstFilter;

/**
 * @Route("/api/bookkeeping/billing/billing-year-const")
 */
class BillingYearConstController extends AbstractFOSRestController implements ClassResourceInterface {

    /**
     * Gets a collection of the billing year const items
     * 
     * @Route("/", name="get_collection_billing_year_const", methods={"GET"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing year const")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * ),
     * @QueryParam(name="currentPage", requirements="\d+", default="1", description="current page to show")
     * @QueryParam(name="perPage", requirements="\d+", default="50", description="Items count per page")
     * @QueryParam(name="sortBy", requirements="[a-zA-Z_]+", default="year", description="Field to sort by")
     * @QueryParam(name="sortDesc", requirements="(true|false)", default="true", description="Sort descrement order anabled")
     * @QueryParam(name="filter", default="{}", description="Serialized filters.")
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingYearConst::class)
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
            $filter = new BillingYearConstFilter();
            $filter->readFromParamFetcher($paramFetcher);

            $result['totalRows'] = (int)$this->getRepository()->findByMultiParameters(true, $filter);
            $result['items'] = $this->getRepository()->findByMultiParameters(false, $filter);
            
            return $result;
            
        } catch (\Exception $ex) {
            return new View('Wrong parameters ('.$ex->getMessage().')', Response::HTTP_BAD_REQUEST);
        }
        
        return $result;
    }
    
    /**
     * Get a single billing year const
     *
     * @Route("/{id}", name="get_billing_year_const", methods={"GET"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing year const")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * ),
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="billing year const id"
     * )
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingYearConst::class)
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Returned when not found"
     * )
     * 
     * @Annotations\View(serializerGroups={
     *   "summary"
     * })
     */
    public function getAction(int $id) {
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            throw new NotFoundHttpException('Billing year const with id ' . $id . ' does not exist');
        }

        return $item;
    }

    /**
     * Create a single billing year const
     *
     * @Route("/", methods={"POST"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing year const")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="billingYearConst",
     *     in="body",
     *     required=true,
     *     description="billingYearConst data",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingYearConst::class)
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingYearConst::class)
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
    public function postAction(Request $request) {
        $parameters = $request->request->all();

        $item = new BillingYearConst();
        $form = $this->createForm(BillingYearConstType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_POST]);
        $form->submit($parameters);

        if (!$form->isValid()) {          
            return $form;
        }

        try {
            $this->getRepository()->save($item);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate year. Billing year const year should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View($item, Response::HTTP_CREATED);
    }

    
    /**
     * Update existing Item from the submitted data
     *
     * @Route("/", methods={"PATCH"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing year const")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="BillingYearConst",
     *     in="body",
     *     required=true,
     *     description="BillingYearConst data",
     *     @SWG\Schema(
     *         ref=@Model(type=BillingYearConst::class)
     *     )
     * )
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
    public function patchAction(Request $request)
    {
        // get params
        $parameters = $request->request->all();
        if (!isset($parameters['id'])) {
            return new View('Wrong parameters, id in BillingYearConst data is required', Response::HTTP_BAD_REQUEST);
        }
        $id = $parameters['id'];
        
        // get item
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            return new View('BillingYearConst with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(BillingYearConstType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_PATCH]);
        unset($parameters['id']);
        $form->submit($parameters);
        if (!$form->isValid()) {   
            return $form;
        }

        try {
            $this->getRepository()->save($item);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate year. BillingYearConst year should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }
    
    /**
     * Delete existing Item 
     *
     * @Route("/{id}", methods={"DELETE"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing year const")
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
     *     description="BillingYearConst id"
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
            return new View('BillingYearConst with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }
        
        $this->getRepository()->delete($item);

        return new View(null, Response::HTTP_NO_CONTENT);
    }
    
    
    /**
     * @return BillingYearConstRepository
     */
    private function getRepository(): BillingYearConstRepository {
        return $this->getDoctrine()->getManager()->getRepository(BillingYearConst::class);
    }

    
}
