<?php

namespace App\Domain\Bookkeeping\Tag\Type\Types;

use App\Domain\Bookkeeping\Tag\Type\Types\AbstractType;

class HalfSettlementType extends AbstractType {
    /**
     * identifier
     */
    const TYPE_ID = 3;
    
    /**
     * name
     */
    const TYPE_NAME = 'pół VATu reszta dochodówka';
   
}