<?php

namespace App\Domain\Balance\Filter;

use App\Domain\Common\Filter\CommonFilter;
use App\Domain\Bookkeeping\Billing\Filter\BillingMonthFilter;

/**
 * Filter parameters to get data from balance
 *
 * @author Sebastian Chmiel
 */
class BalanceMonthFilter extends BillingMonthFilter {
    /**
     * @var \DateTime
     */
    public $monthDate;
    
    /**
     * tags identifiers
     * 
     * @var array of int
     */
    public $tags;
    
    /**
     * allowed fields to sort (if field not found, default is first of the list)
     * 
     * @var array
     */
    protected $allowedSortFields = ['date', 'contractor', 'invoiceNumber', 'amountNet', 'amountGross', 'paid', 'copied', 'confiramtion'];
   
    /**
     * read parameters from array
     * 
     * @param array $paramsArray
     * 
     * @return void
     */
    public function readFromArray(array $paramsArray = []) {
        if (isset($paramsArray['monthDate'])) {
            $this->monthDate = $paramsArray['monthDate'];
        }
        if (isset($paramsArray['tags'])) {
            $this->tags = $paramsArray['tags'];
        }
    }
    
}
