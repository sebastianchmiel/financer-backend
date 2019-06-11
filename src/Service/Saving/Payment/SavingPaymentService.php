<?php

namespace App\Service\Saving\Payment;

use App\Service\Saving\Account\SavingAccountService;
use App\Repository\Saving\Item\SavingItemRepository;
use App\Repository\Saving\Item\SavingItemHistoryRepository;
use App\Entity\Saving\Item\SavingItem;
use App\Entity\Saving\Item\SavingItemHistory;

class SavingPaymentService {

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
     * get payment for items and create history and change account values
     * 
     * @param array
     * 
     * @return array
     */
    public function addPayment(array $data) {
        if (empty($data['items'])) {
            return ;
        }
        
        // verify sum amount items is lower then main amount
        $amountItemsSum = 0;
        foreach ($data['items'] as $item) {
            $amountItemsSum += $item['amount'];
        }
        if ($data['amount'] < $amountItemsSum) {
            throw new \Exception('Suma kwot na poszczególne pozycje jest większa niż kwota wpłaty!');
        }
        $usedAmount = $amountItemsSum;
        $restAmount = $data['amount'] - $usedAmount;

        // find saving items
        $savingItemsIds = $ids = array_column($data['items']->toArray(), 'id');
        $savingItems = $this->savingItemRepository->getItemsById($savingItemsIds);
        
        $diffKey = array_diff($savingItemsIds, array_keys($savingItems));
        if (!empty($diffKey)) {
            throw new \Exception('Nie znaleziono w bazie pozycji o id: '.join(', ', $diffKey));
        }
        
        // save item and history
        foreach ($data['items'] as $item) {
            $this->addSinglePayment($savingItems[$item['id']], $item['amount'], $data['name'], $data['date']);
        }
        
        // additional changes
        switch ($data['type']) {
            case 'fromAccount':
                $this->savingAccountService->updateAmounts([
                    'balanceDistributed' => $this->savingAccountService->getBalanceDistributed() + $usedAmount,
                    'balanceForDistribution' => $this->savingAccountService->getBalanceForDistribution() - $usedAmount
                ]);
                break;
            case 'fromItem':
                // find source item
                /* @var $sourceItem SavingItem */
                $sourceItem = $this->savingItemRepository->findOneById($data['sourceItemId']);
                if ($sourceItem) {
                    $sourceItem->setAmountCollected($sourceItem->getAmountCollected() - $usedAmount);
                    $history = new SavingItemHistory();
                    $history->setSavingItem($sourceItem);
                    $history->setDate($data['date']);
                    $history->setAmount(-1 * $usedAmount);
                    $history->setName('Przeniesienie do innych pozycji');
                    $this->savingItemHistoryRepository->persist($history);
                }
                break;
            default:
               // change account data
                $this->savingAccountService->addPayment($usedAmount, $restAmount); 
        }
        
        $this->savingItemHistoryRepository->flush();
    }
    
    /**
     * add single payment - increase collected amount and add history
     * 
     * @param SavingItem $savingItem
     * @param int $amount
     * @param string $name
     * @param \DateTime $date
     * 
     * @return void
     */
    public function addSinglePayment(SavingItem $savingItem, int $amount, string $name, \DateTime $date) : void {
        // item
        $savingItem->setAmountCollected($savingItem->getAmountCollected() + $amount);
        if ($savingItem->getAmountCollected() >= $savingItem->getAmount()) {
            $savingItem->setFinished(true);
        } else {
            $savingItem->setFinished(false);
        }
        
        // history
        $history = new SavingItemHistory();
        $history->setSavingItem($savingItem);
        $history->setDate($date);
        $history->setAmount($amount);
        $history->setName($name);
        $this->savingItemHistoryRepository->persist($history);
    }
    
}
