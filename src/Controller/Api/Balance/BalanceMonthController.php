<?php

namespace App\Controller\Api\Balance;

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
use App\Service\Bookkeeping\Billing\BillingMonthDateResolver;
use App\Service\Bookkeeping\Billing\BillingMonthData;
use App\Service\Bookkeeping\Billing\BillingMonthSettlementService;
use App\Repository\Bookkeeping\Billing\BillingMonthRepository;
use App\Domain\Balance\Data\BalanceData;
use App\Domain\Balance\Filter\BalanceMonthFilter;
use App\Domain\Bookkeeping\Tag\Collection\TagCollection;

/**
 * @Route("/api/balance/month")
 */
class BalanceMonthController extends AbstractFOSRestController implements ClassResourceInterface {

    /**
     * Get blaance items from month data 
     *
     * @Route("/{date}", name="balance_month_get_month_with_data", methods={"GET"})
     * 
     * @SWG\Tag(name="Balance - items")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * ),
     * @SWG\Parameter(
     *     name="date",
     *     in="path",
     *     type="string",
     *     description="billing month RRRR-MM-DD"
     * )
     * @QueryParam(name="currentPage", requirements="\d+", default="1", description="current page to show")
     * @QueryParam(name="perPage", requirements="\d+", default="50", description="Items count per page")
     * @QueryParam(name="sortBy", requirements="[a-zA-Z_]+", default="date", description="Field to sort by")
     * @QueryParam(name="sortDesc", requirements="(true|false)", default="false", description="Sort descrement order anabled")
     * @QueryParam(name="filter", default="{}", description="Serialized filters.")

     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="date", type="string"),
     *         @SWG\Property(property="finished", type="boolean")
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
    public function getMonthWithDataAction(
            BalanceData $balanceData,
            BillingMonthDateResolver $dateResolver,
            TagCollection $tagCollection,
            
            BillingMonthData $dataProvider,
            BillingMonthRepository $billingMonthRepository,
            BillingMonthSettlementService $settlementService,
            ParamFetcher $paramFetcher,
            \DateTime $date
    ) {
        $data = [
            'items' => [],
            'tags' => $tagCollection->getAllAsArray(),
        ];
        
        $balanceData->setMonthDate($dateResolver->getMonthDate($date));
        
        try {
            $filter = new BalanceMonthFilter();
            $filter->readFromParamFetcher($paramFetcher);
            $filter->readFromArray(['monthDate' => $dateResolver->getMonthDate($date)]);
            $balanceData->setFilter($filter);
        } catch (\Exception $ex) {
            return new View('Wrong parameters', Response::HTTP_BAD_REQUEST);
        }
        
        // get data
        try {
            $data['items'] = $balanceData->getData();
        } catch (\Exception $ex) {
            return new View('Something wrong ('.$ex->getMessage().' '.$ex->getFile().':'.$ex->getLine().')', Response::HTTP_BAD_REQUEST);
        }
        
        return $data;
    }
    
    /**
     * Save merged bank statmenet items to billing item or tag
     *
     * @Route("/save-merged", methods={"POST"}, name="balance_save_merge")
     * 
     * @SWG\Tag(name="Balance - items")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="data",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(
     *              type="object",
     *              @SWG\Property(property="balanceItemId", type="integer"),
     *              @SWG\Property(property="tagId", type="integer"),
     *              @SWG\Property(property="billanceItemId", type="integer")
     *         )
     *      ),
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Returned when successful"
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
    public function saveMergedAction(BalanceData $balanceData, Request $request) {
        /* @var $data array */
        $data = $request->request->get('data');
        
        try {
            $mergedItemsCount = $balanceData->saveMerged($data);
            return new View('Poprawnie połączono '.$mergedItemsCount.' pozycji.', Response::HTTP_OK);
        } catch (\Exception $ex) {
            return new View('Wystąpił błąd ('.$ex->getMessage().')', Response::HTTP_BAD_REQUEST);
        }
    }
    
    /**
     * Save manual tag to balance item
     *
     * @Route("/save-single-manual", methods={"POST"}, name="balance_save_single_manual")
     * 
     * @SWG\Tag(name="Balance - items")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="data",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="balanceItemId", type="integer"),
     *         @SWG\Property(property="tagId", type="integer"),
     *      ),
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Returned when successful"
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
    public function saveSingleManualAction(BalanceData $balanceData, Request $request) {
        /* @var $data array */
        $data = $request->request->get('data');
        
        $balanceItemId = (int)$data['balanceItemId'] ?? null;
        $tagId = (int)$data['tagId'] ?? null;

        try {
            $mergedItemsCount = $balanceData->saveSingleManual($balanceItemId, $tagId);
            return new View('Pozycja została zapisana poprawnie.', Response::HTTP_OK);
        } catch (\Exception $ex) {
            return new View('Wystąpił błąd ('.$ex->getMessage().')', Response::HTTP_BAD_REQUEST);
        }
    }
    
    /**
     * Remove tag from balance item
     *
     * @Route("/remove-tag", methods={"POST"}, name="balance_remove_tag")
     * 
     * @SWG\Tag(name="Balance - items")
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * )
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="data",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="balanceItemId", type="integer")
     *      ),
     * )
     * 
     * @SWG\Response(
     *     response=201,
     *     description="Returned when successful"
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
    public function removeTagAction(BalanceData $balanceData, Request $request) {
        /* @var $data array */
        $data = $request->request->get('data');
        
        $balanceItemId = (int)$data['balanceItemId'] ?? null;

        try {
            $balanceData->removeTag($balanceItemId);
            return new View('Pozycja została pomyślnie usunięta.', Response::HTTP_OK);
        } catch (\Exception $ex) {
            return new View('Wystąpił błąd ('.$ex->getMessage().')', Response::HTTP_BAD_REQUEST);
        }
    }
    
}
