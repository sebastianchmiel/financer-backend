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
use App\Entity\Bookkeeping\Billing\BillingMonth;
use App\Repository\Bookkeeping\Billing\BillingMonthRepository;
//use App\Form\Bookkeeping\Contractor\ContractorType;
use App\Domain\Bookkeeping\Billing\Filter\BillingMonthFilter;
use App\Service\Bookkeeping\Billing\BillingMonthDateResolver;
use App\Service\Bookkeeping\Billing\BillingMonthData;
use App\Service\Bookkeeping\Billing\BillingMonthSettlementService;
use App\Domain\Bookkeeping\Tag\Type\SettlementTypeCollection;

/**
 * @Route("/api/bookkeeping/billing/month")
 */
class BillingMonthController extends AbstractFOSRestController implements ClassResourceInterface {

    /**
     * Get month data and all items data
     *
     * @Route("/{date}", name="bookkeeping_billing_month_get_month_with_data", methods={"GET"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing")
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
            BillingMonthDateResolver $dateResolver,
            BillingMonthData $dataProvider,
            ParamFetcher $paramFetcher,
            \DateTime $date
        ) {

        try {
            $filter = new BillingMonthFilter();
            $filter->readFromParamFetcher($paramFetcher);
            $filter->readFromArray(['monthDate' => $dateResolver->getMonthDate($date)]);
        } catch (\Exception $ex) {
            return new View('Wrong parameters', Response::HTTP_BAD_REQUEST);
        }

        // get data
        try {
            $data = $dataProvider->getAllDataForMonth($filter);
        } catch (\Exception $ex) {
            return new View('Something wrong ('.$ex->getMessage().')', Response::HTTP_BAD_REQUEST);
        }
        
        return $data;
    }
    
    
    /**
     * Preview data to calculate settlement for month
     *
     * @Route("/{date}/settlementPreviewData", name="bookkeeping_billing_month_settlement_preview_data", methods={"GET"})
     * 
     * @SWG\Tag(name="Bookkeeping - billing")
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

     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="data", type="object"),
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
    public function settlementPreviewDataAction(
            BillingMonthDateResolver $dateResolver,
            BillingMonthSettlementService $settlementService,
            \DateTime $date
    ) {
    
        $billingMonth = $this->getRepository()->findOneByDate($dateResolver->getMonthDate($date));

        $settlementTypes = new SettlementTypeCollection();
        return [
            'items' => $settlementService->getItemsToCalcSettlement($billingMonth),
            'settlement' => $settlementService->getSettlementFullDataForMonth($billingMonth),
            'settlementTypes' => $settlementTypes->getTypesData()
        ];
    }
    
    /**
     * @return BillingMonthRepository
     */
    private function getRepository(): BillingMonthRepository {
        return $this->getDoctrine()->getManager()->getRepository(BillingMonth::class);
    }
    
}
