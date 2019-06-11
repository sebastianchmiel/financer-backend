<?php

namespace App\Domain\Balance\BankStatement\Model;

/**
 * Description of BankStatementModel
 *
 * @author Sebastian
 */
class BankStatementModel {

    const HASH_DELIMITER = '#';

    /**
     * operation date
     * @var \DateTime
     */
    private $dateOperation;

    /**
     * posting date
     * @var \DateTime
     */
    private $datePosting;

    /**
     * item type description
     * @var string
     */
    private $description;

    /**
     * title
     * @var string
     */
    private $title;

    /**
     * sender/receiver
     * @var string
     */
    private $senderReceiver;

    /**
     * account number
     * @var string
     */
    private $accountNumber;

    /**
     * amount (in gross)
     * @var int
     */
    private $amount;

    /**
     * balance after opereation
     * @var int
     */
    private $balance;

    /**
     * hash generate from all data
     * @var string
     */
    private $hash;

    /**
     * @param \DateTime $dateOperation
     * @param \DateTime $datePosting
     * @param string $description
     * @param string $title
     * @param string $senderReceiver
     * @param string $accountNumber
     * @param int $amount
     * @param int $balance
     */
    public function __construct(
            \DateTime $dateOperation,
            \DateTime $datePosting,
            $description,
            $title,
            $senderReceiver,
            $accountNumber,
            $amount,
            $balance
    ) {
        $this->dateOperation = $dateOperation;
        $this->datePosting = $datePosting;
        $this->description = $description;
        $this->title = $title;
        $this->senderReceiver = $senderReceiver;
        $this->accountNumber = $accountNumber;
        $this->amount = $amount;
        $this->balance = $balance;

        $this->generateHash();
    }

    /**
     * generate hash from date and save it in hash field in this model
     * 
     * @return void
     */
    public function generateHash() {
        $this->hash = md5(
            ($this->dateOperation ? $this->dateOperation->format('YmdHis') : ''). self::HASH_DELIMITER .
            ($this->datePosting ? $this->datePosting->format('YmdHis') : ''). self::HASH_DELIMITER .
            $this->description . self::HASH_DELIMITER .
            $this->title . self::HASH_DELIMITER .
            $this->senderReceiver . self::HASH_DELIMITER .
            $this->accountNumber . self::HASH_DELIMITER .
            $this->amount . self::HASH_DELIMITER .
            $this->balance . self::HASH_DELIMITER
        );
    }
    
    /**
     * get date operation
     * @return \DateTime|null
     */
    public function getDateOperation() {
        return $this->dateOperation;
    }
    
    /**
     * get date posting
     * @return \DateTime|null
     */
    public function getDatePosting() {
        return $this->datePosting;
    }
    
    /**
     * get description
     * @return string|null
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * get title
     * @return string|null
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * get sender / receiver
     * @return string|null
     */
    public function getSenderReceiver() {
        return $this->senderReceiver;
    }

    /**
     * get account number
     * @return string|null
     */
    public function getAccountNumber() {
        return $this->accountNumber;
    }

    /**
     * get amount
     * @return int|null
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * get balance
     * @return int|null
     */
    public function getBalance() {
        return $this->balance;
    }

    /**
     * get hash
     * @return string
     */
    public function getHash() {
        return $this->hash;
    }

}
