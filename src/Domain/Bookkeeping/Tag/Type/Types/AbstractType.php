<?php

namespace App\Domain\Bookkeeping\Tag\Type\Types;

abstract class AbstractType {
    /**
     * get type identifier
     * 
     * @return int
     */
    public function getId() : int {
        return static::TYPE_ID;
    }
    
    /**
     * get type data as array
     * 
     * @return array
     */
    public function getData() : array {
        return [
            'id' => static::TYPE_ID,
            'name' => static::TYPE_NAME
        ];
    }
}