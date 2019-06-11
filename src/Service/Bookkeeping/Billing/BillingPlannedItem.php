<?php

namespace App\Service\Bookkeeping\Billing;

use App\Repository\Bookkeeping\Billing\BillingPlannedItemRepository;
use App\Entity\Bookkeeping\Billing\BillingPlannedItem as BillingPlannedItemEntity;

class BillingPlannedItem {

    /**
     * @var BillingPlannedItemRepository
     */
    private $repository;

    /**
     * @param BillingPlannedItemRepository $repository
     */
    public function __construct(BillingPlannedItemRepository $repository) {
        $this->repository = $repository;
    }

    /**
     * get all planned items for month
     * 
     * @param \DateTime $monthDate
     * 
     * @return array
     */
    public function getPlannedItemsForMonth(\DateTime $monthDate) : array {
        $items = [];
        
        // get items from db
        $plannedItemsDb = $this->repository->getPlannedItemsForMonth($monthDate);
        if (empty($plannedItemsDb)) {
            return $items;
        }
        
        /* @var $plannedItem BillingPlannedItemEntity */
        foreach ($plannedItemsDb as $plannedItem) {
            $items[$plannedItem->getId()] = $plannedItem;
        }
        
        return $items;
    }
}
