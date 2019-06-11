<?php

namespace App\Service\Setting;

use App\Repository\Setting\SettingRepository;
use App\Entity\Setting\Setting;

class SettingService {

    /**
     * @var SettingRepository
     */
    private $repository;

   
    /**
     * @param SettingRepository $repository
     */
    public function __construct(SettingRepository $repository) {
        $this->repository = $repository;
    }

    /**
     * get all company data
     * 
     * @return array
     */
    public function getCompanyData() {
        $data = [];
        
        $fields = [
            'company_name',
            'company_name_short',
            'company_address_street',
            'company_address_post_code',
            'company_address_city',
            'company_nip',
            'company_bank_account_number',
            'company_person_authorized_to_issue_invoices'
        ];
        
        // get values
        $values = $this->repository->findBy(['name' => $fields]);
        if (empty($values)) {
            return $data;
        }
        
        /* @var $value Setting */
        foreach ($values as $value) {
            $data[$value->getName()] = $value->getValue();
        }
        
        return $data;
    }
    
    /**
     * get parameter value
     * 
     * @param string $paramName
     * 
     * @return string
     */
    public function getParameter(string $paramName) {
        /* @var $item Setting */
        $item = $this->repository->findOneByName($paramName);
        return $item ? $item->getValue() : null;
    }
    
    /**
     * create parameter with value
     * 
     * @param string $paramName
     * @param string $paramValue
     * 
     * @return void
     */
    public function createParameter(string $paramName, string $paramValue = null) {
        /* @var $item Setting */
        $item = new Setting();
        $item->setName($paramName);
        $item->setValue($paramValue);
        $this->repository->save($item);
        
        return $item;
    }
    
    /**
     * set parameter value
     * 
     * @param string $paramName
     * @param string $paramValue
     * 
     * @return void
     */
    public function setParameter(string $paramName, string $paramValue) : void {
        /* @var $item Setting */
        $item = $this->repository->findOneByName($paramName);
        if (!$item) {
            $this->createParameter($paramName, $paramValue);
            return ;
        } else {
            $item->setValue($paramValue);
        }
        $this->repository->save($item);
    }

}
