<?php

namespace App\Domain\Balance\Import;

use App\Domain\Balance\Import\Recognizers\RecognizerAbstract;
use App\Domain\Balance\Import\Recognizers\MbankFileRecognizer;
use App\Repository\Balance\BalanceItemRepository;

/**
 * Class for import bank statment
 *
 * @author Sebastian
 */
class Import {
    
    /**
     * @var BalanceItemRepository
     */
    private $balanceItemRepository;
    
    /**
     * @param BalanceItemRepository $balanceItemRepository
     */
    public function __construct(BalanceItemRepository $balanceItemRepository) {
        $this->balanceItemRepository = $balanceItemRepository;
    }
    
    /**
     * import file data
     * 
     * @param string $filePath
     * 
     * @return int saved items count to database (after check doubles)
     */
    public function importFile($filePath) {
        // get recognizer
        $recognizer = $this->getRecognizerForFile($filePath);
        
        // get data
        $data = $recognizer->recognizeData();
        
        // save data
        $savedItemsCount = $this->saveData($data);
        
        // remove file
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        return $savedItemsCount;
    }
    
    /**
     * get recognizer for imported file
     * already support only one bank - mbank
     * 
     * @param string $filePath
     * 
     * @return RecognizerAbstract
     */
    private function getRecognizerForFile($filePath) {
        return new MbankFileRecognizer($filePath);
    }
    
    /**
     * save data to database
     * return saved items
     * 
     * @param array $data
     * 
     * @return int
     */
    private function saveData(array $data) {
        $savedItemsCount = 0;
        
        if (empty($data)) {
            return $savedItemsCount;
        }
        
        /* @var $item BankStatementModel */
        foreach ($data as $item) {
            if ($this->balanceItemRepository->saveBankStatementModel($item, false)) {
                $savedItemsCount++;
            }
        }
        $this->balanceItemRepository->flush();
        
        return $savedItemsCount;
    }
}
