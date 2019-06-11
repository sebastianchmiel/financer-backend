<?php

namespace App\Domain\Bookkeeping\Tag\Type\Types;

use App\Domain\Bookkeeping\Tag\Type\Types\AbstractType;

class FullSettlementType extends AbstractType {
    /**
     * identifier
     */
    const TYPE_ID = 1;
    
    /**
     * name
     */
    const TYPE_NAME = 'pełne rozliczenie';
   
}