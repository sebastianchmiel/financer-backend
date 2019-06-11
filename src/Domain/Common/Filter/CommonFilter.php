<?php

namespace App\Domain\Common\Filter;

use FOS\RestBundle\Request\ParamFetcher;

/**
 * Filter parameters to get data 
 *
 * @author Sebastian Chmiel
 */
abstract class CommonFilter {
    /**
     * current page
     * 
     * @var int
     */
    public $currentPage = 1;
    
    /**
     * items per page
     * 
     * @var int
     */
    public $perPage = 100;
    
    /**
     * field name to sort
     * 
     * @var string
     */
    public $sortBy = null;
    
    /**
     * sort direction 
     * 
     * @var string
     */
    public $sortDirection = 'DESC';
    
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
     * read filters values from param fetcher
     * 
     * @param ParamFetcher $paramFetcher
     */
    public function readFromParamFetcher(ParamFetcher $paramFetcher) {
        $this->readFromJson($paramFetcher->get('filter'));
        
        $this->currentPage = (int)$paramFetcher->get('currentPage');
        $this->perPage = (int)$paramFetcher->get('perPage');
        $this->sortBy = $this->validSortField($paramFetcher->get('sortBy'));
        $this->sortDirection = 'true' === $paramFetcher->get('sortDesc') ? 'desc' : 'asc';
    }
    
    /**
     * valid and return correct sort field
     * 
     * @param string $field
     * 
     * @return string
     */
    private function validSortField(string $field) : string {
        // convert to camel case with first lower case
        $field = lcfirst(str_replace('_', '', ucwords($field, '_')));
        // verify
        if (!in_array($field, $this->allowedSortFields)) {
            return reset($this->allowedSortFields);
        }
        return $field;
    }
    
    /**
     * read parameters from array
     * 
     * @param array $paramsArray
     * 
     * @return void
     */
    abstract public function readFromArray(array $paramsArray = []);
}
