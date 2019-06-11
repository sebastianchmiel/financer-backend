<?php

namespace App\Domain\Bookkeeping\Tag\Type;

use App\Domain\Bookkeeping\Tag\Type\Types\AbstractType;
use App\Domain\Bookkeeping\Tag\Type\Types\FullSettlementType;
use App\Domain\Bookkeeping\Tag\Type\Types\HalfSettlementType;
use App\Domain\Bookkeeping\Tag\Type\Types\NoSettlementType;
use App\Domain\Bookkeeping\Tag\Type\Types\SkipType;

class SettlementTypeCollection {
    /**
     * items
     * 
     * @var array
     */
    private $items = [];
        
    /**
     * set collection 
     */
    public function __construct() {
        $this->items[] = new FullSettlementType();
        $this->items[] = new HalfSettlementType();
        $this->items[] = new NoSettlementType();
        $this->items[] = new SkipType();
    }
    
    /**
     * get data for all types as array
     * 
     * @return array
     */
    public function getTypesData() : array {
        $data = [];
        
        if (empty($this->items)) {
            return $data;
        }
        
        /* @var $item AbstractType */
        foreach ($this->items as $item) {
            $data[$item->getId()] = $item->getData();
        }
        
        return $data;
    }
}