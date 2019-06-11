<?php

namespace App\Controller\Api\Saving\Item;

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
use App\Entity\Saving\Item\SavingItem;
use App\Entity\Saving\Item\SavingItemHistory;
use App\Repository\Saving\Item\SavingItemRepository;
use App\Repository\Saving\Item\SavingItemHistoryRepository;
use App\Domain\Saving\Item\Filter\SavingItemFilter;
use App\Form\Saving\Item\SavingItemType;
use App\Service\Saving\Item\SavingItemService;

/**
 * @Route("/api/saving/item")
 */
class SavingItemController extends AbstractFOSRestController implements ClassResourceInterface {

    /**
     * Gets a collection of the saving items
     * 
     * @Route("/", name="get_saving_items", methods={"GET"})
     * 
     * @SWG\Tag(name="Saving - item")
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
     *         ref=@Model(type=SavingItem::class)
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
    public function cgetAction(SavingItemService $savingItemService, ParamFetcher $paramFetcher)
    {
        $result = [
            'totalRows' => 0,
            'items' => [],
            'summaryChartData' => [],
        ];
        
        try {
            $filter = new SavingItemFilter();
            $filter->readFromParamFetcher($paramFetcher);

            $result['totalRows'] = (int)$this->getRepository()->findByMultiParameters(true, $filter);
            $result['items'] = $this->getRepository()->findByMultiParameters(false, $filter);
            
            $dateFrom = new \DateTime(((new \DateTime('now'))->modify('-12months'))->format('Y-m').'-01 00:00:00');
            $dateTo = new \DateTime((new \DateTime('now'))->format('Y-m-t').' 23:59:59.999999');
            $result['summaryChartData'] = $savingItemService->getSummaryChartData($dateFrom, $dateTo);
            
            return $result;
            
        } catch (\Exception $ex) {
            return new View('Wrong parameters ('.$ex->getMessage().')', Response::HTTP_BAD_REQUEST);
        }
        
        return $result;
    }
    
    /**
     * Get a single saving item
     *
     * @Route("/{id}", name="get_saving_item", methods={"GET"})
     * 
     * @SWG\Tag(name="Saving - item")
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
     *     description="saving item id"
     * )
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=SavingItem::class)
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
            throw new NotFoundHttpException('Saving item with id ' . $id . ' does not exist');
        }

        return $item;
    }

    /**
     * Create a single saving item
     *
     * @Route("/", methods={"POST"})
     * 
     * @SWG\Tag(name="Saving - item")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="savingItem",
     *     in="body",
     *     required=true,
     *     description="saving item data",
     *     @SWG\Schema(
     *         ref=@Model(type=SavingItem::class)
     *     )
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         ref=@Model(type=SavingItem::class)
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

        $item = new SavingItem();
        $form = $this->createForm(SavingItemType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_POST]);
        $form->submit($parameters);
        if (!$form->isValid()) {          
            return $form;
        }

        try {
            $this->getRepository()->save($item);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Saving item name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View($item, Response::HTTP_CREATED);
    }

    
    /**
     * Update existing item from the submitted data
     *
     * @Route("/", methods={"PATCH"})
     * 
     * @SWG\Tag(name="Saving - item")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="savingItem",
     *     in="body",
     *     required=true,
     *     description="saving item data",
     *     @SWG\Schema(
     *         ref=@Model(type=SavingItem::class)
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
            return new View('Wrong parameters, id in saving item data is required', Response::HTTP_BAD_REQUEST);
        }
        $id = $parameters['id'];
        
        // get item
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            return new View('Saving item with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(SavingItemType::class, $item, ['csrf_protection' => false, 'method' => Request::METHOD_PATCH]);
        unset($parameters['id']);
        $form->submit($parameters);
        if (!$form->isValid()) {   
            return $form;
        }

        try {
            $this->getRepository()->save($item);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            return new View('Duplicate name. Saving item name should by unique', Response::HTTP_BAD_REQUEST);
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }
    
    /**
     * Delete existing Item 
     *
     * @Route("/{id}", methods={"DELETE"})
     * 
     * @SWG\Tag(name="Saving - item")
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
     *     description="saving item id"
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
    public function deleteAction(SavingItemService $savingItemService, int $id)
    {
        $item = $this->getRepository()->findOneById($id);
        if (!$item) {
            return new View('Saving item with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
        }
        
        try {
            $savingItemService->delete($item);
        } catch (\Exception $ex) {
            return new View('Something wrong', Response::HTTP_BAD_REQUEST);
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }
    
    /**
     * Get histories for saving item
     *
     * @Route("/history/{id}", name="saving_item_history", methods={"GET"})
     * 
     * @SWG\Tag(name="Saving - item")
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
     *     description="saving item id"
     * )

     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=SavingItemHistory::class)
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
    public function getHistories(int $id) {
        try {
            /* @var $item SavingItem */
            $item = $this->getRepository()->findOneById($id);
            if (!$item) {
                return new View('Saving item with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
            }
            
            return $this->getHistoryRepository()->findBy(['savingItem' => $item], ['date' => 'DESC']);
        } catch (\Exception $ex) {
            return new View('Something wrong', Response::HTTP_BAD_REQUEST);
        }

        return new View('Something wrong', Response::HTTP_BAD_REQUEST);
    }
    
    /**
     * Set item as used
     *
     * @Route("/set-used/{id}", name="saving_item_set_used", methods={"PATCH"})
     * 
     * @SWG\Tag(name="Saving - item")
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
     *     description="saving item id"
     * )

     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=SavingItem::class)
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
    public function setUsed(SavingItemService $savingItemService, int $id) {
        try {
            /* @var $item SavingItem */
            $item = $this->getRepository()->findOneById($id);
            if (!$item) {
                return new View('Saving item with id '.$id.' does not exist', Response::HTTP_NOT_FOUND);
            }
            
            $savingItemService->setUsed($item);
            return $item;
        } catch (\Exception $ex) {
            return new View('Something wrong', Response::HTTP_BAD_REQUEST);
        }

        return new View('Something wrong', Response::HTTP_BAD_REQUEST);
    }
    
    /**
     * @return SavingItemRepository
     */
    private function getRepository(): SavingItemRepository {
        return $this->getDoctrine()->getManager()->getRepository(SavingItem::class);
    }
    
    /**
     * @return SavingItemHistoryRepository
     */
    private function getHistoryRepository(): SavingItemHistoryRepository {
        return $this->getDoctrine()->getManager()->getRepository(SavingItemHistory::class);
    }
}
