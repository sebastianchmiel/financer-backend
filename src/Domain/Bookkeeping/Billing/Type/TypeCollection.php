<?php

namespace App\Domain\Bookkeeping\Billing\Type;

use App\Domain\Bookkeeping\Billing\Type\Types\AbstractType;
use App\Domain\Bookkeeping\Billing\Type\Types\CostType;
use App\Domain\Bookkeeping\Billing\Type\Types\IncomeType;
use App\Domain\Bookkeeping\Billing\Type\Types\UsType;
use App\Domain\Bookkeeping\Billing\Type\Types\ZusType;

class TypeCollection {
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
        $this->items[] = new CostType();
        $this->items[] = new IncomeType();
        $this->items[] = new UsType();
        $this->items[] = new ZusType();
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