<?php

namespace App\Controller\Api\Dashboard;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Dashboard\DashboardService;
use App\Service\Saving\Item\SavingItemService;

/**
 * @Route("/api/dashboard")
 */
class DashboardController extends AbstractFOSRestController implements ClassResourceInterface {

    /**
     * Gets a data for all view in dashboard
     * 
     * @Route("/data", name="get_dashboard_data", methods={"GET"})
     * 
     * @SWG\Tag(name="Dashboard")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     * ),
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Returned when successful",
     *     @SWG\Schema(
     *          @SWG\Items(
     *              type="object",
     *              @SWG\Property(property="chartSmall1", type="object"),
     *          )
     *     )
     * )
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
    public function data(DashboardService $dashboardService, SavingItemService $savingItemService){
        $result = [
            'chartSmall1' => [],
            'chartSmall2' => [],
            'chartSmall3' => [],
            'chartSmall4' => [],
            'mainChart' => [],
            'savingItemsCharts' => [],
        ];
        
        try {
            // chart small
            $result['chartSmall1'] = $dashboardService->getChartSmallData(1);
            $result['chartSmall2'] = $dashboardService->getChartSmallData(2);
            $result['chartSmall3'] = $dashboardService->getChartSmallData(3);
            $result['chartSmall4'] = $dashboardService->getChartSmallData(4);
            $result['mainChart'] = $dashboardService->getMainChartData();
            $result['savingItemsCharts'] = $savingItemService->getItemsDataForSimpleCharts();
        } catch (\Exception $ex) {
            return new View('Something was wrong ('.$ex->getMessage().' '.$ex->getFile().':'.$ex->getLine().')', Response::HTTP_BAD_REQUEST);
        }
        
        return $result;
    }
    
    
}
