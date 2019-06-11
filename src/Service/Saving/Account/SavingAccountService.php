<?php

namespace App\Service\Saving\Account;

use App\Service\Setting\SettingService;

class SavingAccountService {

    /**
     * @var SettingService
     */
    private $settingService;
    
    /**
     * @param SettingService $settingService
     */
    public function __construct(SettingService $settingService) {
        $this->settingService = $settingService;
    }

    /**
     * get all data for saving account
     * 
     * @return array
     */
    public function getAllData() {
        $data = [
            'bankName' => '',
            'accountNumber' => '',
            'percent' => 0,
            'balance' => 0,
            'balanceDistributed' => 0,
            'balanceForDistribution' => 0
        ];
        
        $paramPrefix = 'saving_account_';
        
        // read parameters from settings
        foreach ($data as $key => $value) {
            $data[$key] = $this->settingService->getParameter($paramPrefix . $key) ?? $value;
        }

        return $data;
    }
    
    /**
     * add payment
     * 
     * @param int $usedAmount
     * @param int $restAmount
     * 
     * @return void
     */
    public function addPayment(int $usedAmount, int $restAmount) {
        $this->settingService->setParameter('saving_account_balance', $this->settingService->getParameter('saving_account_balance') + ($usedAmount + $restAmount));        
        $this->settingService->setParameter('saving_account_balanceDistributed', $this->settingService->getParameter('saving_account_balanceDistributed') + $usedAmount);
        $this->settingService->setParameter('saving_account_balanceForDistribution', $this->settingService->getParameter('saving_account_balanceForDistribution') + $restAmount);
    }
    
    /**
     * update amounts
     * 
     * @param array $data
     */
    public function updateAmounts($data) {
        if (isset($data['balance'])) {
            $this->settingService->setParameter('saving_account_balance', $data['balance']);
        }
        if (isset($data['balanceDistributed'])) {
            $this->settingService->setParameter('saving_account_balanceDistributed', $data['balanceDistributed']);
        }
        if (isset($data['balanceForDistribution'])) {
            $this->settingService->setParameter('saving_account_balanceForDistribution', $data['balanceForDistribution']);
        }
    }

    /**
     * get balance
     * 
     * @return int
     */
    public function getBalance() : int {
        return $this->settingService->getParameter('saving_account_balance') ?? 0;
    }
    
    /**
     * get balance distributed
     * 
     * @return int
     */
    public function getBalanceDistributed() : int {
        return $this->settingService->getParameter('saving_account_balanceDistributed') ?? 0;
    }
    
    /**
     * get balance for distribution
     * 
     * @return int
     */
    public function getBalanceForDistribution() : int {
        return $this->settingService->getParameter('saving_account_balanceForDistribution') ?? 0;
    }
    
}
