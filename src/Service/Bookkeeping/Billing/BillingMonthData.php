<?php

namespace App\Service\Bookkeeping\Billing;

use App\Repository\Bookkeeping\Billing\BillingMonthRepository;
use App\Repository\Bookkeeping\Billing\BillingItemRepository;
use App\Entity\Bookkeeping\Billing\BillingMonth;
use App\Domain\Bookkeeping\Billing\Type\TypeCollection;
use App\Domain\Bookkeeping\Billing\Filter\BillingMonthFilter;
use App\Service\Bookkeeping\Billing\BillingPlannedItem;
use App\Service\Bookkeeping\Billing\BillingMonthSettlementService;

class BillingMonthData {

    /**
     * @var BillingMonthRepository
     */
    private $repository;

    /**
     * @var BillingItemRepository
     */
    private $itemRepository;
    
    /**
     * @var BillingPlannedItem
     */
    private $plannedItemService;

    /**
     * @var BillingMonthSettlementService
     */
    private $billingMonthSettlement;
    
    /**
     * @param BillingMonthRepository $repository
     * @param BillingItemRepository  $itemRepository
     * @param BillingPlannedItem $plannedItemService
     * @param BillingMonthSettlementService $billingMonthSettlement
     */
    public function __construct(
            BillingMonthRepository $repository,
            BillingItemRepository $itemRepository,
            BillingPlannedItem $plannedItemService,
            BillingMonthSettlementService $billingMonthSettlement
    ) {
        $this->repository = $repository;
        $this->itemRepository = $itemRepository;
        $this->plannedItemService = $plannedItemService;
        $this->billingMonthSettlement = $billingMonthSettlement;
    }

    /**
     * get all data for Billing month
     * 
     * @param \DateTime $monthDate
     * 
     * @return array
     */
    public function getAllDataForMonth(BillingMonthFilter $filter) {
        // get billing month or create
        $billingMonth = $this->getBillingMonthOrCreate($filter->monthDate);
        $filter->billingMonthObject = $billingMonth;

        // base data
        $data = [
            'date' => $billingMonth->getDate()->format('Y-m-d'),
            'finished' => $billingMonth->getFinished(),
        ];

        // get item types
        $data['types'] = (new TypeCollection())->getTypesData();

        // get items
        $data['items'] = $this->getBillingItems($filter);

        // get warnings count
        $data['warningsCount'] = $this->getBillingMonthWarningsCount($filter);
        
        // get planned items
        $data['plannedItems'] = $this->plannedItemService->getPlannedItemsForMonth($filter->monthDate);
        
        // get settlement data
        $data['settlement'] = $this->billingMonthSettlement->getSettlementBaseDataForMonth($billingMonth);

        return $data;
    }

    /**
     * get billing month object or create when not exist
     * 
     * @param \DateTime $monthDate
     * 
     * @return BillingMonth
     */
    public function getBillingMonthOrCreate(\DateTime $monthDate): BillingMonth {
        $item = $this->repository->findOneByDate($monthDate);
        if (!$item) {
            $item = $this->createBillingMonth($monthDate);
        }

        return $item;
    }

    /**
     * create object for billing month
     * 
     * @param \DateTime $monthDate
     * 
     * @return BillingMonth
     */
    public function createBillingMonth(\DateTime $monthDate): BillingMonth {
        $item = new BillingMonth();
        $item->setDate($monthDate);
        $this->repository->save($item);

        return $item;
    }

    /**
     * get billing items for billing month object and other filters
     * 
     * @param BillingMonthFilter $filter
     * 
     * @return array['totalRows', 'items']
     */
    public function getBillingItems(BillingMonthFilter $filter): array {
        $result = [];

        $result['totalRows'] = (int)$this->itemRepository->findByMultiParameters(true, $filter);
        $result['items'] = $this->itemRepository->findByMultiParameters(false, $filter);

        return $result;
    }
    
    /**
     * get warning count in billing month
     * 
     * @param BillingMonthFilter $filter
     * 
     * @return int
     */
    public function getBillingMonthWarningsCount(BillingMonthFilter $filter) : int {
        $warnings = 0;

        $items = $this->itemRepository->getItemsWithWarningInBillingMonth($filter->billingMonthObject);
        if (empty($items)) {
            return $warnings;
        }
        
        foreach ($items as $item) {
            if (!$item['paid']) {
                $warnings++;
            }
            if (!$item['copied']) {
                $warnings++;
            }
            if (!$item['confirmation']) {
                $warnings++;
            }
        }

        return $warnings;
    }

    
}
