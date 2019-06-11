<?php

namespace App\Domain\Bookkeeping\Tag\Type\Types;

use App\Domain\Bookkeeping\Tag\Type\Types\AbstractType;

class NoSettlementType extends AbstractType {
    /**
     * identifier
     */
    const TYPE_ID = 2;
    
    /**
     * name
     */
    const TYPE_NAME = 'brak rozliczenia';
   
}