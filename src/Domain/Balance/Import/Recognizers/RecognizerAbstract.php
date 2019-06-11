<?php

namespace App\Domain\Balance\Import\Recognizers;

use App\Domain\Balance\Import\Encoder\Encoder;

/**
 *
 * @author Sebastian
 */
abstract class RecognizerAbstract {

    /**
     * file charset encoder
     * 
     * @var Encoder
     */
    protected $encoder;
    
    /**
     * path to imported file
     * 
     * @var string
     */
    protected $filePath;
    
    /**
     * @param string $filePath
     */
    public function __construct($filePath) {
        $this->encoder = new Encoder($filePath);
        $this->filePath = $filePath;
    }
    
    /**
     * recognize file data and return in model structure
     */
    abstract public function recognizeData();
    
    /**
     * remove multiple white characters
     * 
     * @param string $value
     * 
     * @return string
     */
    protected function removeMultiWhiteChars($value) {
        return preg_replace('/\s+/', ' ', $value);
    }
    
}
