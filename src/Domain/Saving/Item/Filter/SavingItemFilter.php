<?php

namespace App\Domain\Saving\Item\Filter;

use App\Domain\Common\Filter\CommonFilter;

/**
 * Filter parameters to get data from billing planned iem repository 
 *
 * @author Sebastian Chmiel
 */
class SavingItemFilter extends CommonFilter {
    /**
     * @var string
     */
    public $name;
    
    /**
     * @var bool
     */
    public $finished;
    
    /**
     * @var bool
     */
    public $used;
    
    /**
     * allowed fields to sort (if field not found, default is first of the list)
     * 
     * @var array
     */
    protected $allowedSortFields = ['name', 'dateFrom', 'amount'];
   
    /**
     * read parameters from array
     * 
     * @param array $paramsArray
     * 
     * @return void
     */
    public function readFromArray(array $paramsArray = []) {
        if (isset($paramsArray['name'])) {
            $this->name = $paramsArray['name'];
        }
        if (isset($paramsArray['finished'])) {
            $this->finished = ($paramsArray['finished'] == 1 ? 1 : ($paramsArray['finished'] == 2 ? 0 : null));
        }
        if (isset($paramsArray['used'])) {
            $this->used = ($paramsArray['used'] == 1 ? 1 : ($paramsArray['used'] == 2 ? 0 : null));
        }
    }
    
}
