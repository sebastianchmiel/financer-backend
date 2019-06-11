<?php

namespace App\Domain\Balance\Import\Recognizers;

use App\Domain\Balance\Import\Recognizers\RecognizerAbstract;
use App\Domain\Balance\BankStatement\Model\BankStatementModel;

/**
 * Recognizer for mbank statement 
 *
 * @author Sebastian
 */
class MbankFileRecognizer extends RecognizerAbstract {

    /**
     * CSV file delimiter
     */
    const CSV_DELIMITER = ';';
    
    /**
     * recognize file data and return in model structure
     */
    public function recognizeData() {
        $items = [];
        
        // read file
        $file = fopen($this->filePath, "r");
        while (!feof($file)) {
            // read line as csv
            $linePart = fgetcsv($file, 0, self::CSV_DELIMITER);
            
            // check if line with billing data
            if (!$this->checkBillingItem($linePart)) {
                continue;
            }

            // recognize and add to collection
            $items[] = $this->recognizeSingleItem($linePart);
        }
        fclose($file);
        
        return $items;
    }

    /**
     * recognize single ban statement line to model object
     * 
     * @param array $linePart
     * 
     * @return BankStatementModel
     */
    private function recognizeSingleItem(array $linePart) {
        return new BankStatementModel(
                new \DateTime($linePart[0]),
                new \DateTime($linePart[1]),
                trim(trim($this->removeMultiWhiteChars($this->encoder->convertToUtf8($linePart[2]))), '\''),
                trim(trim($this->removeMultiWhiteChars($this->encoder->convertToUtf8($linePart[3]))), '\''),
                trim(trim($this->removeMultiWhiteChars($this->encoder->convertToUtf8($linePart[4]))), '\''),
                trim(trim($this->removeMultiWhiteChars($this->encoder->convertToUtf8($linePart[5]))), '\''),
                $this->convertAmount($linePart[6]),
                $this->convertAmount($linePart[7])
        );
    }
    
    /**
     * convert amount to int in gross
     * 
     * @param string $value
     * 
     * @return int
     */
    private function convertAmount($value) {
        $value = str_replace(',', '.', $value);
        $value = str_replace(' ', '', $value);
        return (int)round($value * 100);
    }
    
    /**
     * check if fiel item (line) is billing item
     * 
     * @param array $item
     * 
     * @return boolean
     */
    private function checkBillingItem(array $item) {
        if (count($item) === 9 && $item[0] !== '' && $item[0][0] !== '#') {
            return true;
        }
        return false;
    }
}
