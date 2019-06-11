<?php

namespace App\Service\Bookkeeping\Stat;

use App\Repository\Bookkeeping\Billing\BillingItemRepository;
use App\Repository\Bookkeeping\Billing\BillingMonthSettlementRepository;

class Stat {

    /**
     * @var BillingItemRepository
     */
    private $billingItemRepository;
    
    /**
     * @var BillingMonthSettlementRepository
     */
    private $billingMonthSettlementRepository;

    /**
     * @param BillingItemRepository $billingItemRepository
     * @param BillingMonthSettlementRepository $billingMonthSettlementRepository
     */
    public function __construct(BillingItemRepository $billingItemRepository, BillingMonthSettlementRepository $billingMonthSettlementRepository) {
        $this->billingItemRepository = $billingItemRepository;
        $this->billingMonthSettlementRepository = $billingMonthSettlementRepository;
    }

    /**
     * get stat for tag in date range per months
     * 
     * @param int $tagId
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     *
     * @return array
     */
    public function getTagStats(int $tagId, \DateTime $dateFrom, \DateTime $dateTo) {
        // prepare base ampty array
        $data = $this->generateEmptyArrayForDateRange($dateFrom, $dateTo);

        // get items
        $items = $this->billingItemRepository->getBillingItemsByTagIdAndDateRange($tagId, $dateFrom, $dateTo);
        if (empty($items)) {
            return $data;
        }
        
        foreach ($items as $item) {
            $data[$item['date']->format('Y-m').'-01'] += $item['amountGross'];
        }

        return $data;
    }

    /**
     * generate empty array with key as date and value as zero
     * 
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * 
     * @return array
     */
    public function generateEmptyArrayForDateRange(\DateTime $dateFrom, \DateTime $dateTo) {
        $data = [];
        
        $dateTemp = new \DateTime($dateFrom->format('Y-m').'-01');
        $dateToExtended = new \DateTime($dateTo->format('Y-m-t'));
        
        while ($dateTemp <= $dateToExtended) {
            $data[$dateTemp->format('Y-m-d')] = 0;
            $dateTemp->modify('+1month');
        }
        
        return $data;
    }
    
    /**
     * get settlement stats in date range
     * 
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * 
     * @return array
     */
    public function getSettlementStats(\DateTime $dateFrom, \DateTime $dateTo) : array {
        $data = [
            'labels' => [],
            'costSum' => [],
            'incomeSum' => [],
            'realIncome' => [],
            'taxSum' => [],
        ];
        
        // prepare base ampty array
        $dataSingle = $this->generateEmptyArrayForDateRange($dateFrom, $dateTo);
        $data['labels'] = array_keys($dataSingle);
        $data['costSum'] = json_decode(json_encode($dataSingle), true);
        $data['incomeSum'] = json_decode(json_encode($dataSingle), true);
        $data['realIncome'] = json_decode(json_encode($dataSingle), true);
        $data['taxSum'] = json_decode(json_encode($dataSingle), true);
        
        // get settlement data
        $dataDb = $this->billingMonthSettlementRepository->getSettlementDataForChartInDateRange($dateFrom, $dateTo);
        
        // rewrite
        if (empty($dataDb)) {
            return $data;
        }
        
        foreach ($dataDb as $item) {
            $date = $item['date']->format('Y-m-d');
            $data['costSum'][$date] = isset($item['costSum']) ? $item['costSum'] : 0;
            $data['incomeSum'][$date] = isset($item['incomeSum']) ? $item['incomeSum'] : 0;
            $data['realIncome'][$date] = isset($item['realIncome']) ? $item['realIncome'] : 0;
            $data['taxSum'][$date] = (isset($item['incomeTax']) ? $item['incomeTax'] : 0) + (isset($item['vatTax']) ? $item['vatTax'] : 0);
        }
        
        return $data;
    }
}
