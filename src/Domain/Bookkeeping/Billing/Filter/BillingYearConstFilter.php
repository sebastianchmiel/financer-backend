<?php

namespace App\Domain\Bookkeeping\Billing\Filter;

use App\Domain\Common\Filter\CommonFilter;

/**
 * Filter parameters to get data from BillingYearConst repository 
 *
 * @author Sebastian Chmiel
 */
class BillingYearConstFilter extends CommonFilter {
    /**
     * @var int
     */
    public $year;
    
    /**
     * allowed fields to sort (if field not found, default is first of the list)
     * 
     * @var array
     */
    protected $allowedSortFields = ['year', 'taxFreeAllowanceAmount', 'deductionFromIncomeTaxAmount', 'incomeTaxPercent'];
   
    /**
     * read parameters from array
     * 
     * @param array $paramsArray
     * 
     * @return void
     */
    public function readFromArray(array $paramsArray = []) {
        if (isset($paramsArray['year'])) {
            $this->year = $paramsArray['year'];
        }
    }
    
}
