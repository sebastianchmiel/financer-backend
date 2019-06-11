<?php

namespace App\Domain\Balance\Data;

use App\Repository\Balance\BalanceItemRepository;
use App\Entity\Balance\BalanceItem;
use App\Domain\Balance\Filter\BalanceMonthFilter;
use App\Repository\Bookkeeping\Billing\BillingMonthRepository;
use App\Service\Bookkeeping\Billing\BillingMonthData;
use App\Service\Bookkeeping\Billing\BillingMonthSettlementService;
use App\Entity\Bookkeeping\Billing\BillingMonth;
use App\Entity\Bookkeeping\Billing\BillingItem;
use App\Repository\Bookkeeping\Billing\BillingItemRepository;
use App\Domain\Bookkeeping\Tag\Type\SettlementTypeCollection;
use App\Repository\Bookkeeping\Tag\TagRepository;
use App\Entity\Bookkeeping\Tag\Tag;

class BalanceData {
    
    /**
     * month date
     * @var \DateTime
     */
    private $monthDate;
    
    /**
     * @var BalanceMonthFilter
     */
    private $filter;
    
    /**
     * @var BillingMonthRepository
     */
    private $billingMonthRepository;
    
    /**
     * @var BalanceItemRepository
     */
    private $balanceItemRepository;
    
    /**
     * @var BillingMonthData
     */
    private $billingMonthDataProvider;
    
    /**
     * @var BillingMonthSettlementService
     */
    private $settlementService;
    
    /**
     * @var BillingMonth
     */
    private $billingMonth;
    
    /**
     * @var BillingItemRepository
     */
    private $billingItemRepository;
    
    /**
     * @var array
     */
    private $settlementData;
    
    /**
     * @var array
     */
    private $balanceItemData;
    
    /**
     * @var TagRepository
     */
    private $tagRepository;
    
    /**
     * @param BillingMonthRepository $billingMonthRepository
     */
    public function __construct(
            BillingMonthRepository $billingMonthRepository,
            BillingMonthData $billingMonthDataProvider,
            BillingMonthSettlementService $settlementService,
            BillingItemRepository $billingItemRepository,
            BalanceItemRepository $balanceItemRepository,
            TagRepository $tagRepository
    ) {
        $this->billingMonthRepository = $billingMonthRepository;
        $this->billingMonthDataProvider = $billingMonthDataProvider;
        $this->billingItemRepository = $billingItemRepository;
        $this->settlementService = $settlementService;
        $this->balanceItemRepository = $balanceItemRepository;
        $this->tagRepository = $tagRepository;
    }
    
    /**
     * get summary data
     * 
     * @return array
     */
    public function getData() {
        $data = [];
        
        // get billing month
        $this->getBillingMonth();
        
        // get settlement data
        $this->getSettlementData();

        // get balance item data
        $this->getBalanceItemData();
        
        // merge data from balance and settlement
        $data = $this->mergeBalanceDataAndSettlement();
        
        return $data;
    }
    
    /**
     * merge balance data and settlement data 
     * 
     * @return array
     */
    private function mergeBalanceDataAndSettlement() {
        $data = [
            'balanceItems' => [],
            'unassignedSettlementItems' => []
        ];
        
        $settlementItems = unserialize(serialize($this->settlementData['items']));
        
        // get first tag from settlment data
        if (is_array($settlementItems) && !empty($settlementItems)) {
            foreach ($settlementItems as $itemKey => $item) {
                if (array_key_exists('tags', $item)) {
                    if (is_array($item['tags']) && !empty($item['tags'])) {
                        foreach ($item['tags'] as $tag) {
                            $settlementItems[$itemKey]['tagId'] = $tag;
                            break;
                        }
                        unset($settlementItems[$itemKey]['tags']);
                    }
                }
            }
        }
        
        if (is_array($this->balanceItemData) && !empty($this->balanceItemData)) {
            /* @var $balanceItem array */
            foreach ($this->balanceItemData as $balanceItemId => $balanceItem) {
                $data['balanceItems'][$balanceItemId] = $balanceItem;
                $data['balanceItems'][$balanceItemId]['billingItem'] = null;

                if ($balanceItem['billingItemId']) {
                    foreach ($settlementItems as $billingItemId => $settlementItem) {
                        if ($balanceItem['billingItemId'] === $billingItemId) {
                            $data['balanceItems'][$balanceItemId]['billingItem'] = $settlementItem;
                            unset($settlementItems[$billingItemId]);
                        }
                    }
                }
            }
        }
        $data['unassignedSettlementItems'] = $settlementItems;
        
        return $data;
    }
    
    /**
     * get balance item data
     * 
     * @return array
     */
    private function getBalanceItemData() {
        $dataDb = $this->balanceItemRepository->getItemsForMonthDate($this->filter);
        /* @var $item BalanceItem */
        foreach ($dataDb as $item) {
            $this->balanceItemData[$item->getId()] = [
                'id' => $item->getId(),
                'dateOperation' => $item->getDateOperation(),
                'datePosting' => $item->getDatePosting(),
                'description' => $item->getDescription(),
                'title' => $item->getTitle(),
                'senderReceiver' => $item->getSenderReceiver(),
                'accountNumber' => $item->getAccountNumber(),
                'amount' => $item->getAmount(),
                'balance' => $item->getBalance(),
                'billingItemId' => $item->getBillingItem() ? $item->getBillingItem()->getId() : null,
                'tagId' => $item->getTag() ? $item->getTag()->getId() : null,
            ];
        }
        
        return $this->balanceItemData;
    }
    
    /**
     * get settlement data
     */
    private function getSettlementData() {
        $settlementTypes = new SettlementTypeCollection();
        $this->settlementData = [
            'items' => $this->billingMonth ? $this->settlementService->getItemsToCalcSettlement($this->billingMonth) : [],
            'settlement' => null,
            'settlementTypes' => $settlementTypes->getTypesData()
        ];
        // filter items by tag
        if (!empty($this->filter->tags) && !empty($this->settlementData['items'])){
            foreach ($this->filter->tags as $tag) {
                foreach ($this->settlementData['items'] as $itemKey => $item) {
                    if (!in_array($tag, $item['tags'])) {
                        //var_dump('REMOVE SETTLEMENT ITEM! '.$itemKey);
                        unset ($this->settlementData['items'][$itemKey]);
                        continue;
                    }
                }
            }
        }
    }
    
    /**
     * get billing month object
     */
    private function getBillingMonth() {
        $this->billingMonth = $this->billingMonthRepository->findOneByDate($this->monthDate);
    }
    
    /**
     * set month date
     * @param \DateTime $monthDate
     */
    public function setMonthDate(\DateTime $monthDate) {
        $this->monthDate = $monthDate;
    }
    
    /**
     * set filter
     * @param BalanceMonthFilter $filter
     */
    public function setFilter(BalanceMonthFilter $filter) {
        $this->filter = $filter;
    }
    
    /**
     * save merged data (balance item with tag or billing item)
     * return count of saved merged items
     * 
     * @param array $data
     * 
     * @return int
     * 
     * @throws \Exception
     */
    public function saveMerged(array $data) {
        $savedItems = 0;
        
        if (empty($data)) {
            return $savedItems;
        }
        
        foreach ($data as $item) {
            /* @var $balanceItem BalanceItem */
            $balanceItem = $this->balanceItemRepository->findOneById($item['balanceItemId']);
            if (!$balanceItem) {
                throw new \Exception('Nie znaleziono pozycji w bilansie o identyfikatorze '.$item['balanceItemId']);
            }
            
            if (isset($item['tagId'])) {
                /* @var $tag Tag */
                $tag = $this->tagRepository->findOneById($item['tagId']);
                if (!$tag) {
                    throw new \Exception('Nie znaleziono tagu o identyfikatorze '.$item['tagId']);
                }
                $balanceItem->setTag($tag);
                $savedItems++;
            } elseif (isset($item['billingItemId'])) {
                /* @var $billingItem BillingItem */
                $billingItem = $this->billingItemRepository->findOneById($item['billingItemId']);
                if (!$billingItem) {
                    throw new \Exception('Nie znalezionu pozycji w zestawieniu o identyfikatorze '.$item['billingItemId']);
                }
                $balanceItem->setBillingItem($billingItem);
                $savedItems++;
            }
        }
        
        $this->balanceItemRepository->flush();
        return $savedItems;
    }
    
    /**
     * save manual assigned tag to balance item
     * 
     * @param int $balanceItemId
     * @param int $tagId
     * 
     * @return void
     * 
     * @throws \Exception
     */
    public function saveSingleManual(int $balanceItemId, int $tagId) {
        if (!$balanceItemId || !$tagId) {
            return ;
        }
        
        /* @var $balanceItem BalanceItem */
        $balanceItem = $this->balanceItemRepository->findOneById($balanceItemId);
        if (!$balanceItem) {
            throw new \Exception('Nie znaleziono pozycji w bilansie o identyfikatorze '.$balanceItemId);
        }
            
        /* @var $tag Tag */
        $tag = $this->tagRepository->findOneById($tagId);
        if (!$tag) {
            throw new \Exception('Nie znaleziono tagu o identyfikatorze '.$tagId);
        }
        
        // set tag
        $balanceItem->setTag($tag);
        
        // save
        $this->balanceItemRepository->flush();
    }
    
    /**
     * remove tag from balance item
     * 
     * @param int $balanceItemId
     * 
     * @return void
     * 
     * @throws \Exception
     */
    public function removeTag(int $balanceItemId) {
        if (!$balanceItemId) {
            throw new \Exception('Nie przekazano identyfikatora pozycji w bilansie');
        }
        
        /* @var $balanceItem BalanceItem */
        $balanceItem = $this->balanceItemRepository->findOneById($balanceItemId);
        if (!$balanceItem) {
            throw new \Exception('Nie znaleziono pozycji w bilansie o identyfikatorze '.$balanceItemId);
        }
            
        // remove tag
        $balanceItem->setTag(null);
        
        // save
        $this->balanceItemRepository->flush();
    }
}
