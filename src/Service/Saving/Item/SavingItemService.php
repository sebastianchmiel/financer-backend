<?php

namespace App\Service\Saving\Item;

use App\Service\Saving\Account\SavingAccountService;
use App\Repository\Saving\Item\SavingItemRepository;
use App\Repository\Saving\Item\SavingItemHistoryRepository;
use App\Entity\Saving\Item\SavingItem;
use App\Entity\Saving\Item\SavingItemHistory;

class SavingItemService {

    /**
     * @var SavingAccountService
     */
    private $savingAccountService;
    
    /**
     * @var SavingItemRepository
     */
    private $savingItemRepository;
    
    /**
     * @var SavingItemHistoryRepository
     */
    private $savingItemHistoryRepository;
    
    /**
     * @param SavingAccountService $savingAccountService
     * @param SavingItemRepository $savingItemRepository
     * @param SavingItemHistoryRepository $savingItemHistoryRepository
     */
    public function __construct(SavingAccountService $savingAccountService, SavingItemRepository $savingItemRepository, SavingItemHistoryRepository $savingItemHistoryRepository) {
        $this->savingAccountService = $savingAccountService;
        $this->savingItemRepository = $savingItemRepository;
        $this->savingItemHistoryRepository = $savingItemHistoryRepository;
    }

    /**
     * set item as used (change status, reduc account values, add history)
     * 
     * @param SavingItem $item
     * 
     * @return void
     */
    public function setUsed(SavingItem $item) {
        // check if already is not used
        if ($item->getUsed()) {
            return;
        }

        // change status
        $item->setUsed(true);
        
        // reducte account amounts
        $this->savingAccountService->updateAmounts([
            'balance' => $this->savingAccountService->getBalance() - $item->getAmountCollected(),
            'balanceDistributed' => $this->savingAccountService->getBalanceDistributed() - $item->getAmountCollected(),
        ]);
        
        // add history
        $history = new SavingItemHistory();
        $history->setSavingItem($item);
        $history->setDate(new \DateTime('NOW'));
        $history->setAmount(-1 * $item->getAmountCollected());
        $history->setName('Wykorzystanie');
        $this->savingItemHistoryRepository->persist($history);
        
        // save all in database
        $this->savingItemHistoryRepository->flush();
    }
    
    /**
     * delete item
     * 
     * @param SavingItem $item
     */
    public function delete(SavingItem $item) {
        // add collected amoutn to acount balanceForDistrution
        $this->savingAccountService->updateAmounts([
            'balance' => $this->savingAccountService->getBalance() + $item->getAmountCollected(),
            'balanceForDistribution' => $this->savingAccountService->getBalanceForDistribution() + $item->getAmountCollected(),
        ]);
        
        // remove item
        $this->savingItemRepository->delete($item);
    }
    
    /**
     * get sumamry data from all history in last date range
     * 
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * 
     * @return array
     */
    public function getSummaryChartData(\DateTime $dateFrom, \DateTime $dateTo) : array {
        $data = [];
        
        // get history data
        $histories = $this->savingItemHistoryRepository->getItemsInDateRangeAsArray($dateFrom, $dateTo);
        if (empty($histories)) {
            return $data;
        }
        
        foreach ($histories as $history) {
            $dateKey = $history['date']->format('Y-m').'-01';
            
            // check exist month key - if not create
            if (!isset($data[$dateKey])) {
                $data[$dateKey] = 0;
            }
            
            $data[$dateKey] += $history['amount'];
        }
        
        return $data;
    }
    
    /**
     * get data for saving intems to simple charts
     * 
     * @param int $maxItems
     * 
     * @return array
     */
    public function getItemsDataForSimpleCharts(int $maxItems = 6) : array{
        $data = [];
        
        // get last active items
        $savingItems = $this->savingItemRepository->findBy(['finished' => false, 'used' => false], ['dateFrom' => 'ASC'], $maxItems);
        if (empty($savingItems)) {
            return $data;
        }
        
        /* @var $savingItem SavingItem */
        foreach ($savingItems as $savingItem) {
            $data[] = $this->getDataForSimpleChart($savingItem);
        }
        
        return $data;
    }
    
    /**
     * get data for simple chart for saving item
     * 
     * @param SavingItem $savingItem
     * 
     * @return array
     */
    public function getDataForSimpleChart(SavingItem $savingItem) : array {
        $data = [
            'name' => $savingItem->getName(),
            'percent' => 0,
            'expectedPercent' => 0,
            'expectedAmount' => 0,
            'amountCollected' => $savingItem->getAmountCollected(),
            'amountFinal' => 0,
            'status' => false,
            'dateFrom' => $savingItem->getDateFrom() ? $savingItem->getDateFrom()->format('Y-m-d') : null,
            'dateTo' => $savingItem->getDateTo() ? $savingItem->getDateTo()->format('Y-m-d') : null,
        ];
        
        // calc date diffs
        $dateNow = new \DateTime('now');
        if ($savingItem->getDateTo() < $dateNow) {
            $dateNow = $savingItem->getDateTo();
        }
        $diffToNowTemp = $dateNow->diff($savingItem->getDateFrom());
        $diffToNow = $diffToNowTemp->m + ($diffToNowTemp->y * 12);
        $diffToNowInDays = $diffToNowTemp->d + ($diffToNowTemp->m * 30) + ($diffToNowTemp->y * 365);
        $diffToEndTemp = $savingItem->getDateTo()->diff($savingItem->getDateFrom());
        $diffToEnd = $diffToEndTemp->m + ($diffToEndTemp->y * 12);
        $diffToEndInDays = $diffToEndTemp->d + ($diffToEndTemp->m * 30) + ($diffToEndTemp->y * 365);
        
        // calc dynamic amount
        $amountFinal = $savingItem->getAmount();
        if ($savingItem->getDynamicAmount()) {            
            $amountFinal = $diffToEnd > 0 ? (float)number_format(($savingItem->getAmount() * ($diffToEnd - $diffToNow) / $diffToEnd), 2, '.', '') : 0;
        }
        $data['amountFinal'] = $amountFinal;
        
        $data['expectedPercent'] = $diffToEndInDays !== 0 ? (float)number_format(($diffToNowInDays / $diffToEndInDays * 100), 2, '.', '') : 0;
        $data['percent'] = $amountFinal !== 0 ? (float)number_format(($savingItem->getAmountCollected() / $amountFinal * 100), 2, '.', '') : 0;

        $data['expectedAmount'] = (float)number_format((($data['expectedPercent'] * $amountFinal) / 100), 2, '.', '');
        
        $data['status'] = $data['percent'] >= $data['expectedPercent'];
        
        return $data;
    }
    
    /**
     * get groupped histories for all items (groupped by date and name)
     * 
     * @return array
     */
    public function getGrouppedHistories() : array {
        $data = [];
        
        $items = $this->savingItemHistoryRepository->findBy([], ['date' => 'DESC']);
        if (empty($items)) {
            return $data;
        }
        
        // group items (by date and names)
        /* @var $item SavingItemHistory */
        foreach ($items as $item) {
            $key = $item->getDate()->format('Y-m-d').'_'.$item->getName();
            
            // create group item if does not exist
            if (!isset($data[$key])) {
                $data[$key] = [
                    'name' => $item->getName(),
                    'amount' => 0,
                    'date' => $item->getDate()
                ];
            }
            
            // add amount
            $data[$key]['amount'] += $item->getAmount();
        }
        
        return $data;
    }
}
