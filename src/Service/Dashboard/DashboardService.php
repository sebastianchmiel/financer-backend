<?php

namespace App\Service\Dashboard;

use App\Service\Setting\SettingService;
use App\Service\Bookkeeping\Stat\Stat;

class DashboardService {

    /**
     * @var SettingService
     */
    private $settingService;
    
    /**
     * @var Stat
     */
    private $statService;
    

    /**
     * @param SettingService $settingService
     * @param Stat $statService
     */
    public function __construct(SettingService $settingService, Stat $statService) {
        $this->settingService = $settingService;
        $this->statService = $statService;
    }

    /**
     * get data for small chart
     * 
     * @param int $chartSmallId
     *
     * @return array
     */
    public function getChartSmallData(int $chartSmallId) {
        // get data for chart by id
        $chartData = [
            'label' => $this->settingService->getParameter('dashboard_chart_mini'.$chartSmallId.'_label') ?? '',
            'desc' => $this->settingService->getParameter('dashboard_chart_mini'.$chartSmallId.'_desc') ?? '',
            'type' => $this->settingService->getParameter('dashboard_chart_mini'.$chartSmallId.'_type') ?? '',
            'backgroundColor' => $this->settingService->getParameter('dashboard_chart_mini'.$chartSmallId.'_background_color') ?? '',
            'color' => $this->settingService->getParameter('dashboard_chart_mini'.$chartSmallId.'_color') ?? ''
        ];
        
        $tagId = (int)$this->settingService->getParameter('dashboard_chart_mini'.$chartSmallId.'_tag');
        
        // get chart data
        $dateFrom = new \DateTime(((new \DateTime('now'))->modify('-7months'))->format('Y-m').'-01 00:00:00.000000');
        $dateTo = new \DateTime((new \DateTime('now'))->format('Y-m-t').' 23:59:59.999999');
        $chartData['chartData'] = $this->statService->getTagStats($tagId, $dateFrom, $dateTo);
                
        return $chartData;        
    }

    /**
     * get main chart data
     * 
     * @return array
     */
    public function getMainChartData() {
        $dateTo = new \DateTime((new \DateTime('now'))->format('Y-m-t').' 23:59:59.999999');
        $dateFrom = new \DateTime((clone $dateTo)->modify('-12months')->format('Y-m-').'01 00:00:00');
        
        return $this->statService->getSettlementStats($dateFrom, $dateTo);
    }
}
