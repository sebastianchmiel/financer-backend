<?php

namespace App\Domain\Bookkeeping\Tag\Filter;

use App\Domain\Common\Filter\CommonFilter;

/**
 * Filter parameters to get data from billing planned iem repository 
 *
 * @author Sebastian Chmiel
 */
class TagFilter extends CommonFilter {
    /**
     * @var string
     */
    public $name;
    
    /**
     * allowed fields to sort (if field not found, default is first of the list)
     * 
     * @var array
     */
    protected $allowedSortFields = ['name', 'settlementType'];
   
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
    }
    
}
