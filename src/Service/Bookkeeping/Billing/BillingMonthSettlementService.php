<?php

namespace App\Service\Bookkeeping\Billing;

use App\Repository\Bookkeeping\Billing\BillingMonthRepository;
use App\Repository\Bookkeeping\Billing\BillingYearConstRepository;
use App\Repository\Bookkeeping\Billing\BillingMonthSettlementRepository;
use App\Repository\Bookkeeping\Billing\BillingItemRepository;
use App\Entity\Bookkeeping\Billing\BillingMonth;
use App\Entity\Bookkeeping\Billing\BillingItem;
use App\Entity\Bookkeeping\Billing\BillingMonthSettlement;
use App\Domain\Bookkeeping\Tag\Type\Types\SkipType;
use App\Domain\Bookkeeping\Tag\Type\Types\FullSettlementType;
use App\Domain\Bookkeeping\Tag\Type\Types\HalfSettlementType;
use App\Domain\Bookkeeping\Tag\Type\Types\NoSettlementType;
use App\Entity\Bookkeeping\Tag\Tag;
use App\Entity\Bookkeeping\Billing\BillingYearConst;
use App\Service\Bookkeeping\Billing\BillingMonthDateResolver;

class BillingMonthSettlementService {
    /**
     * @var BillingMonthRepository
     */
    private $billingMonthRepository;
    
    /**
     * @var BillingYearConstRepository
     */
    private $billingYearConstRepository;
    
    /**
     * @var BillingMonthSettlementRepository
     */
    private $billingMonthSettlementRepository;
    
    /**
     * @var BillingMonthDateResolver 
     */
    private $billingMonthDateResolver;
    
    /**
     * @var BillingItemRepository
     */
    private $billingItemRepository;


    public function __construct(
            BillingMonthRepository $billingMonthRepository,
            BillingYearConstRepository $billingYearConstRepository,
            BillingMonthSettlementRepository $billingMonthSettlementRepository,
            BillingItemRepository $billingItemRepository
    ) {
        $this->billingMonthRepository = $billingMonthRepository;
        $this->billingYearConstRepository = $billingYearConstRepository;
        $this->billingMonthSettlementRepository = $billingMonthSettlementRepository;
        $this->billingItemRepository = $billingItemRepository;
        
        $this->billingMonthDateResolver = new BillingMonthDateResolver();
    }
    
    /**
     * calc settlement for month date
     * 
     * @param \DateTime $date
     * @param array $settlementDataFromLastMonth
     * 
     * @return void
     */
    public function calcSettlementForMonthDate(\DateTime $date, array $settlementDataFromLastMonth = []) {
        /* @var $billingMonth BillingMonth */
        $billingMonth = $this->billingMonthRepository->findOneByDate($this->billingMonthDateResolver->getMonthDate($date));
        
        // year const for date 
        $yearConst = $this->getYearConstForDate($date);

        $items = $this->getItemsToCalcSettlement($billingMonth, $yearConst);

        // get data from last month
        $previousMonthDate = (clone $billingMonth->getDate())->modify('-1month');
        $settlementDataFromLastMonth = empty($settlementDataFromLastMonth) ? $this->getSettlementBaseDataFromMonth($previousMonthDate, $yearConst) : $settlementDataFromLastMonth;
        
        $costSum = 0;
        $incomeSum = 0;
        $incomeTax = 0;
        $vatTax = 0;
        $surplusIncomeTax = 0;
        $surplusVatTax = 0;
        $usedTaxFreeAllowance = 0;
        $restTaxFreeAllowance = null;
        $realIncome = 0;
        $additionalCostToRemoveFromRealIncome = 0;
        
        if (!empty($items)) {
            foreach ($items as $item) {
                if ($item['billanceAmount'] >= 0) {
                    $incomeSum += $item['billanceAmount'];
                } else {
                    $costSum += $item['billanceAmount'];
                }
                
                $additionalCostToRemoveFromRealIncome += $item['additionalCostToRemoveFromRealIncome'];
                
                $incomeTax += $item['incomeTax'];
                $vatTax += $item['vatTax'];
            }
        }
        
        // add data from previous months
        $incomeTax += $settlementDataFromLastMonth['surplusIncomeTax'];
        $vatTax += $settlementDataFromLastMonth['surplusVatTax'];
        
        // remove const values
        $incomeTax += $yearConst['deductionFromIncomeTaxAmount'];
        $deductionFromIncomeTaxAmount = $yearConst['deductionFromIncomeTaxAmount'];

        if ($settlementDataFromLastMonth['restTaxFreeAllowance']) {
            $taxFreeAllowanceToUse = min ((-1 * $incomeTax), $settlementDataFromLastMonth['restTaxFreeAllowance']);

            $incomeTax += $taxFreeAllowanceToUse;
            $usedTaxFreeAllowance = $taxFreeAllowanceToUse;
            $restTaxFreeAllowance = $settlementDataFromLastMonth['restTaxFreeAllowance'] - $usedTaxFreeAllowance;            
        } else {
            $restTaxFreeAllowance = 0;
        }

        // it to much (not should pay) rewrite to surplus
        if ($incomeTax > 0) {
            $surplusIncomeTax = $incomeTax;
            $incomeTax = 0;
        }
        if ($vatTax > 0) {
            $surplusVatTax = $vatTax;
            $vatTax = 0;
        }
        
        $realIncome = $incomeSum + $costSum + $incomeTax + $vatTax;
        
        // save
        /* @var $billingMonthSettlement BillingMonthSettlement */
        $billingMonthSettlement = null;
        if ($billingMonth->getBillingMonthSettlement()) {
            $billingMonthSettlement = $billingMonth->getBillingMonthSettlement();
        } else {
            $billingMonthSettlement = new BillingMonthSettlement();
            $billingMonthSettlement->setBillingMonth($billingMonth);
        }
        $billingMonthSettlement->setCostSum($costSum);
        $billingMonthSettlement->setIncomeSum($incomeSum);
        $billingMonthSettlement->setIncomeTax($incomeTax);
        $billingMonthSettlement->setVatTax($vatTax);
        $billingMonthSettlement->setSurplusIncomeTax($surplusIncomeTax);
        $billingMonthSettlement->setSurplusVatTax($surplusVatTax);
        $billingMonthSettlement->setUsedTaxFreeAllowance($usedTaxFreeAllowance);
        $billingMonthSettlement->setRestTaxFreeAllowance($restTaxFreeAllowance);
        $billingMonthSettlement->setRealIncome($realIncome);
        $billingMonthSettlement->setDeductionFromIncomeTaxAmount($deductionFromIncomeTaxAmount);
        $billingMonthSettlement->setAdditionalCostToRemoveFromRealIncome($additionalCostToRemoveFromRealIncome);
        
        $this->billingMonthSettlementRepository->save($billingMonthSettlement, false);
        
        // check if is last month
        $currentMonthDate = $this->billingMonthDateResolver->getMonthDate(new \DateTime('now'));
        if ($billingMonth->getDate() < $currentMonthDate) {
            $nextDate = (clone $billingMonth->getDate())->modify('+1month');
            $this->calcSettlementForMonthDate($nextDate, [
                'surplusIncomeTax' => $surplusIncomeTax,
                'surplusVatTax' => $surplusVatTax,
                'restTaxFreeAllowance' => $restTaxFreeAllowance,
            ]);
        } else {
            $this->billingMonthSettlementRepository->flush();
        }
    }
    
    /**
     * get settlement base data (surprise, rest tax free allowance) from month
     * 
     * @param \DateTime $monthDate
     * 
     * @return array
     */
    private function getSettlementBaseDataFromMonth(\DateTime $monthDate, array $yearConst) : array {
        $data = [
            'surplusIncomeTax' => 0,
            'surplusVatTax' => 0,
            'restTaxFreeAllowance' => null,
        ];

        // if last month (call from calc settlement for first monthy) get base values
        if ((int)$monthDate->format('m') === 12) {
            $data['restTaxFreeAllowance'] = $yearConst['taxFreeAllowanceAmount'];
        } else {
            /* @var $billingMonth BillingMonth */
            $billingMonth = $this->billingMonthRepository->findOneByDate($monthDate);
            if ($billingMonth && $billingMonth->getBillingMonthSettlement()) {
                $data['surplusIncomeTax'] = $billingMonth->getBillingMonthSettlement()->getSurplusIncomeTax() > 0 ? $billingMonth->getBillingMonthSettlement()->getSurplusIncomeTax() : 0;
                $data['surplusVatTax'] = $billingMonth->getBillingMonthSettlement()->getSurplusVatTax() < 0 ? $billingMonth->getBillingMonthSettlement()->getSurplusVatTax() : 0;
                $data['restTaxFreeAllowance'] = $billingMonth->getBillingMonthSettlement()->getRestTaxFreeAllowance();
            }
        }

        return $data;
    }

    /**
     * get settlement values for each items to calc summary settlement
     * 
     * @param BillingMonth $billingMonth
     * @param array $yearConst
     * 
     * @return array
     */
    public function getItemsToCalcSettlement(BillingMonth $billingMonth, array $yearConst = []) : array {
        $data = [];
        
        if (empty($yearConst)) {
            // year const for date 
            $yearConst = $this->getYearConstForDate($billingMonth->getDate());
        }
        
        // get ites with date of paid in this month
        $dateFrom = $billingMonth->getDate();
        $dateTo = new \DateTime($dateFrom->format('Y-m-t').' 23:59:59.999999');
        $itemsPaidInBillingMonth = $this->billingItemRepository->getItemsPaidInDateRange($dateFrom, $dateTo);
        
        // rewrite in one array
        $items = [];
        if (!empty($billingMonth->getBillingItems()->getValues())) {
            foreach ($billingMonth->getBillingItems()->getValues() as $item) {
                $items[$item->getId()] = $item;
            }
        }
        if (!empty($itemsPaidInBillingMonth)) {
            /* @var $itemPaid BillingItem */
            foreach ($itemsPaidInBillingMonth as $itemPaid) {
                if (!isset($items[$itemPaid->getId()])) {
                    $items[$itemPaid->getId()] = $itemPaid;
                }
            }
        }
        
        
        if (0 === count($items)) {
            return $data;
        }
        
        $isIncomeForIncomeTax = false;
        
        /* @var $billingItem BillingItem */
        foreach ($items as $billingItem) {
            $settlementType = $this->recognizeSettlementType($billingItem);
            $includeInBalance = $this->recognizeIncludeInBalance($billingItem);
            $includeInRealCost = $this->recognizeIncludeInRealIncome($billingItem);
            
            $forIncomeTax = $billingItem->getDate() >= $dateFrom && $billingItem->getDate() <= $dateTo ? true : false;
            $forVatTax = $billingItem->getDateOfPaid() >= $dateFrom && $billingItem->getDateOfPaid() <= $dateTo ? true : false;
            
            $balanceAmount = $includeInBalance && $forIncomeTax ? $this->calcBillanceAmountSettlementByType($billingItem, $settlementType) : null;
            
            $data[$billingItem->getId()] = [
                'billingItemId' => $billingItem->getId(),
                'contractor' => $billingItem->getContractor() ? $billingItem->getContractor()->getName() : null,
                'name' => $billingItem->getInvoiceNumber(),
                'amountNet' => $billingItem->getAmountNet(),
                'amountGross' => $billingItem->getAmountGross(),
                'typeSettlement' => $settlementType,
                'billanceAmount' => $balanceAmount,
                'incomeTax' => $forIncomeTax ? $this->calcIncomeTaxByType($billingItem, $settlementType, $yearConst) : null,
                'vatTax' => $forVatTax ? $this->calcVatTaxByType($billingItem, $settlementType) : null,
                'forIncomeTax' => $forIncomeTax,
                'forVatTax' => $forVatTax,
                'simulated' => false,
                'additionalCostToRemoveFromRealIncome' => !$includeInBalance && $includeInRealCost ? $billingItem->getAmountGross() : 0,
                'tags' => [],
                'date' => $billingItem->getDate(),
                'dateOfPaid' => $billingItem->getDateOfPaid(),
            ];
            
            // add tags
            /* @var $tag Tag */
            foreach ($billingItem->getTags() as $tag) {
                $data[$billingItem->getId()]['tags'][] = $tag->getId();
            }
            
            // is income item for calc income tax
            if ($billingItem->getAmountNet() > 0 && $forIncomeTax) {
                $isIncomeForIncomeTax = true;
            }
        }
        
        // if no income for income tax - create simulated invoice
        $simulatedItem = null;
        if (!$isIncomeForIncomeTax) {
            $simulatedItem = $this->generateSimulatedIncomeForIncomeTax($dateFrom, $dateTo, $yearConst);
        }
        if ($simulatedItem) {
            $data[0] = $simulatedItem;
        }

        return $data;
    }
    
    /**
     * recognize settlement type
     * 
     * @param BillingItem $billingItem
     * 
     * @return int
     */
    private function recognizeSettlementType(BillingItem $billingItem) : int {
        /* @var $firstTag Tag */
        $firstTag = count($billingItem->getTags()) > 0 ? $billingItem->getTags()[0] : null;
        if ($firstTag) {
            return $firstTag->getSettlementType() !== null ? $firstTag->getSettlementType() : SkipType::TYPE_ID;
        }
        return SkipType::TYPE_ID;
    }
    
    /**
     * recognize if billing item should be include in balance
     * 
     * @param BillingItem $billingItem
     * 
     * @return bool
     */
    private function recognizeIncludeInBalance(BillingItem $billingItem) : bool {
        /* @var $firstTag Tag */
        $firstTag = count($billingItem->getTags()) > 0 ? $billingItem->getTags()[0] : null;
        if ($firstTag) {
            return $firstTag->getIncludeInBalance();
        }
        return false;
    }
    
    /**
     * recognize if billing item should by include in real cost
     * 
     * @param BillingItem $billingItem
     * 
     * @return bool
     */
    private function recognizeIncludeInRealIncome(BillingItem $billingItem) : bool {
        /* @var $firstTag Tag */
        $firstTag = count($billingItem->getTags()) > 0 ? $billingItem->getTags()[0] : null;
        if ($firstTag) {
            return $firstTag->getIncludeInRealCost();
        }
        return false;
    }
    
    /**
     * calc billance amount settlement to sum billance
     * 
     * @param BillingItem $billingItem
     * @param int $settlementType
     * 
     * @return ?int
     */
    private function calcBillanceAmountSettlementByType(BillingItem $billingItem, int $settlementType) : ?int {
        switch ($settlementType) {
            case FullSettlementType::TYPE_ID:
            case HalfSettlementType::TYPE_ID:
            case NoSettlementType::TYPE_ID:
                return $billingItem->getAmountGross();
                break;
            case SkipType::TYPE_ID:
            default:
                return null;
        }
    }
    
    /**
     * calc income tax
     * 
     * @param BillingItem $billingItem
     * @param int $settlementType
     * 
     * @return ?int
     */
    private function calcIncomeTaxByType(BillingItem $billingItem, int $settlementType, array $yearConst) : ?int {
        switch ($settlementType) {
            case FullSettlementType::TYPE_ID:
                return -1 * $billingItem->getAmountNet() * $yearConst['incomeTaxPercent'] / 100;
                break;
            case HalfSettlementType::TYPE_ID:
                return -1 * ($billingItem->getAmountNet() + ($billingItem->getTaxValue() / 2)) * $yearConst['incomeTaxPercent'] / 100;
                break;
            case NoSettlementType::TYPE_ID:
            case SkipType::TYPE_ID:
            default:
                return null;
        }
    }
    
    /**
     * calc vat tax
     * 
     * @param BillingItem $billingItem
     * @param int $settlementType
     * 
     * @return ?int
     */
    private function calcVatTaxByType(BillingItem $billingItem, int $settlementType) : ?int {
        switch ($settlementType) {
            case FullSettlementType::TYPE_ID:
                return -1 * $billingItem->getTaxValue();
                break;
            case HalfSettlementType::TYPE_ID:
                return -1 * $billingItem->getTaxValue() / 2;
                break;
            case NoSettlementType::TYPE_ID:
            case SkipType::TYPE_ID:
            default:
                return null;
        }
    }
    
    /**
     * get settlement base data for billing month
     * 
     * @param BillingMonth $billingMonth
     * 
     * @return array
     */
    public function getSettlementBaseDataForMonth(BillingMonth $billingMonth) : array {
        $data = [
            'incomeTax' => 0,
            'vatTax' => 0,
            'realIncome' => 0,
            'quarterIncomeTax' => 0,
            'quarterVatTax' => 0,
            'taxSum' => 0,
            'quarterTaxSum' => 0,
            'additionalCostToRemoveFromRealIncome' => 0,
        ];
     
        // base billing month settlement values
        if ($billingMonth->getBillingMonthSettlement()) {
            $data['incomeTax'] = $billingMonth->getBillingMonthSettlement()->getIncomeTax();
            $data['vatTax'] = $billingMonth->getBillingMonthSettlement()->getVatTax();
            $data['realIncome'] = $billingMonth->getBillingMonthSettlement()->getRealIncome();
            $data['additionalCostToRemoveFromRealIncome'] = $billingMonth->getBillingMonthSettlement()->getAdditionalCostToRemoveFromRealIncome();
            $data['realIncomeAfterRemoveAdditionalCost'] = $data['realIncome'] + $data['additionalCostToRemoveFromRealIncome'];
        }
        
        // quarter values
        $data['quarterIncomeTax'] = $this->getSettlementQuarterIncomeTaxForDate($billingMonth->getDate());
        $data['quarterVatTax'] = $this->getSettlementQuarterVatTaxForDate($billingMonth->getDate());
        
        // sums
        $data['taxSum'] = $data['incomeTax'] + $data['vatTax'];
        $data['quarterTaxSum'] = $data['quarterIncomeTax'] + $data['quarterVatTax'];
        
        return $data;
    }
    
    /**
     * get settlement full data for billing month
     * 
     * @param BillingMonth $billingMonth
     * 
     * @return array
     */
    public function getSettlementFullDataForMonth(BillingMonth $billingMonth) : array {
        $data = [
            'costSum' => 0,
            'incomeSum' => 0,
            'incomeTax' => 0,
            'vatTax' => 0,
            'usedTaxFreeAllowance' => 0,
            'restTaxFreeAllowance' => 0,
            'surplusIncomeTax' => 0,
            'surplusVatTax' => 0,
            'realIncome' => 0,
            'quarterIncomeTax' => 0,
            'quarterVatTax' => 0,
            'taxSum' => 0,
            'quarterTaxSum' => 0,
            'deductionFromIncomeTaxAmount' => 0,
        ];
     
        // base billing month settlement values
        if ($billingMonth->getBillingMonthSettlement()) {
            $data['costSum'] = $billingMonth->getBillingMonthSettlement()->getCostSum();
            $data['incomeSum'] = $billingMonth->getBillingMonthSettlement()->getIncomeSum();
            $data['incomeTax'] = $billingMonth->getBillingMonthSettlement()->getIncomeTax();
            $data['vatTax'] = $billingMonth->getBillingMonthSettlement()->getVatTax();
            $data['usedTaxFreeAllowance'] = $billingMonth->getBillingMonthSettlement()->getUsedTaxFreeAllowance();
            $data['restTaxFreeAllowance'] = $billingMonth->getBillingMonthSettlement()->getRestTaxFreeAllowance();
            $data['surplusIncomeTax'] = $billingMonth->getBillingMonthSettlement()->getSurplusIncomeTax();
            $data['surplusVatTax'] = $billingMonth->getBillingMonthSettlement()->getSurplusVatTax();            
            $data['realIncome'] = $billingMonth->getBillingMonthSettlement()->getRealIncome();
            $data['deductionFromIncomeTaxAmount'] = $billingMonth->getBillingMonthSettlement()->getDeductionFromIncomeTaxAmount();
        }
        
        // quarter values
        $data['quarterIncomeTax'] = $this->getSettlementQuarterIncomeTaxForDate($billingMonth->getDate());
        $data['quarterVatTax'] = $this->getSettlementQuarterVatTaxForDate($billingMonth->getDate());
        
        // sums
        $data['taxSum'] = $data['incomeTax'] + $data['vatTax'];
        $data['quarterTaxSum'] = $data['quarterIncomeTax'] + $data['quarterVatTax'];
        
        return $data;
    }
    
    /**
     * get sum of income tax for quarter from puted month
     * 
     * @param \DateTime $date
     * 
     * @return int
     */
    private function getSettlementQuarterIncomeTaxForDate(\DateTime $date) : int {
        $dateRange = $this->getQuarterRangeForDate($date);
        return $this->billingMonthSettlementRepository->getSumIncomeTaxForDateRange($dateRange['begin'], $dateRange['end']);
    }
    
    /**
     * get sum of vat tax for quarter from puted month
     * 
     * @param \DateTime $date
     * 
     * @return int
     */
    private function getSettlementQuarterVatTaxForDate(\DateTime $date) : int {
        $dateRange = $this->getQuarterRangeForDate($date);
        return $this->billingMonthSettlementRepository->getSumVatTaxForDateRange($dateRange['begin'], $dateRange['end']);
    }
    
    /**
     * get quarter date range for date
     * 
     * @param \DateTime $date
     * 
     * @return array [begin, end]
     */
    private function getQuarterRangeForDate(\DateTime $date) : array {
        $monthNr = (int)$date->format('m');
        $quarter = floor(($monthNr -1) / 3) + 1;

        $rangeBeginMonth = (($quarter -1) * 3) + 1;
        $rangeEndMonth = $rangeBeginMonth + 2;
            
        return [
            'begin' => new \DateTime($date->format('Y').'-'.$rangeBeginMonth.'-01 00:00:00'),
            'end' => new \DateTime((new \DateTime($date->format('Y').'-'.$rangeEndMonth.'-01'))->format('Y-m-t').' 23:59:59.999999'),
        ];
    }
    
    /**
     * get year const for date
     * 
     * @param \DateTime $monthDate
     * 
     * @return array (taxFreeAllowanceAmount, deductionFromIncomeTaxAmount, incomeTaxPercent)
     */
    public function getYearConstForDate(\DateTime $monthDate) : array {
        $data = [
            'taxFreeAllowanceAmount' => 0,
            'deductionFromIncomeTaxAmount' => 0,
            'incomeTaxPercent' => 0
        ];
        
        $years = [(int)$monthDate->format('Y')];

        // if first date of year get data for previous and current month
        if (1 === (int)$monthDate->format('m')) {
            array_push($years, ((int)$monthDate->format('Y') - 1));
        }
        
        $yearConstDb = $this->billingYearConstRepository->findBy(['year' => $years], ['year' => 'DESC']);
        if (empty($yearConstDb)) {
            return $data;
        }
        
        $data['taxFreeAllowanceAmount'] = $yearConstDb[0]->getTaxFreeAllowanceAmount();
        $data['deductionFromIncomeTaxAmount'] = (1 === (int)$monthDate->format('m') ? $yearConstDb[1]->getDeductionFromIncomeTaxAmount() : $yearConstDb[0]->getDeductionFromIncomeTaxAmount());
        $data['incomeTaxPercent'] = $yearConstDb[0]->getIncomeTaxPercent();

        return $data;
    }
    
    /**
     * generate simulated invoice
     * 
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param array $yearConst
     * 
     * @return array
     */
    public function generateSimulatedIncomeForIncomeTax(\DateTime $dateFrom, \DateTime $dateTo, array $yearConst) {
        $dateFrom = (clone $dateFrom)->modify('-7month');
        
        // get last 5 income items in max 8 months
        $items = $this->billingItemRepository->getLastIncomeItems($dateFrom, $dateTo, 5);
        if (empty($items)) {
            return null;
        }
        $contractors = [];
        $amountNets = [];
        /* @var $item BillingItem */
        foreach ($items as $item) {
            // save contractor with count
            $contractor = $item->getContractor() ? $item->getContractor()->getName() : null;
            if (!isset($contractors[$contractor])) {
                $contractors[$contractor] = 0;
            }
            $contractors[$contractor]++;
            
            // save amounts
            $amountNets[] = $item->getAmountNet();
        }
        
        // contractor with max count
        $bestCommonContractor = array_keys($contractors, max($contractors))[0];
        
        // remove marginaly values
        $amountNet = 0;
        if (count($amountNets) >= 3) {
            sort($amountNets, SORT_NUMERIC);
            array_shift($amountNets);
            array_pop($amountNets);
        }
        if (count($amountNets) > 0) {
            $amountNet = (int)(array_sum($amountNets) / count($amountNets));
        }
        
        $amountGross = (int)($amountNet * 1.23);
        return [
            'contractor' => $bestCommonContractor,
            'name' => 'Symulowana faktura',
            'amountNet' => $amountNet,
            'amountGross' => $amountGross,
            'typeSettlement' => FullSettlementType::TYPE_ID,
            'billanceAmount' => $amountGross, // amoutn gross
            'incomeTax' => (-1 * $amountNet * $yearConst['incomeTaxPercent'] / 100),
            'vatTax' => null,
            'forIncomeTax' => true,
            'forVatTax' => false,
            'simulated' => true,
            'additionalCostToRemoveFromRealIncome' => 0,
        ];
    }
}
