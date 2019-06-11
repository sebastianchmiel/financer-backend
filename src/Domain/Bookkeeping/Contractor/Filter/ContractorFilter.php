<?php

namespace App\Domain\Bookkeeping\Contractor\Filter;

/**
 * Filter parameters to get data from contractor repository 
 *
 * @author Sebastian Chmiel
 */
class ContractorFilter {
    
    /**
     * filter name
     * 
     * @var string
     */
    public $name;
    
    /**
     * @param array $params - optional
     */
    public function __construct(array $params = []) {
        $this->readFromArray($params);
    }
    
    /**
     * read parameters from json
     * 
     * @param string $paramsJson
     * 
     * @return void
     */
    public function readFromJson($paramsJson) {
        $paramsArray = json_decode(($paramsJson ? $paramsJson : '{}'), true);
        $this->readFromArray($paramsArray);
    }
    
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
