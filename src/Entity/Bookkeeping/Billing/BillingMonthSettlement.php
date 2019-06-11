<?php

namespace App\Entity\Bookkeeping\Billing;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Bookkeeping\Billing\BillingMonthSettlementRepository")
 * @ORM\Table(name="billing_month_settlement")
 * @JMSSerializer\ExclusionPolicy("all")
 */
class BillingMonthSettlement
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Type("integer")
     * @JMSSerializer\Groups({"all", "summary"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="cost sum")
     */
    private $costSum;
    
    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="income sum")
     */
    private $incomeSum;
    
    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="income tax to paid")
     */
    private $incomeTax;
    
    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="vat tax to paid")
     */
    private $vatTax;
    
    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="surplus income tax")
     */
    private $surplusIncomeTax;
    
    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="surplus vat tax")
     */
    private $surplusVatTax;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="used tax free allowance")
     */
    private $usedTaxFreeAllowance;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="rest tax free allowance")
     */
    private $restTaxFreeAllowance;
    
    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="real income in month")
     */
    private $realIncome;
    
    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="deduction from income tax amount")
     */
    private $deductionFromIncomeTaxAmount;
    
    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="additional cost to remove from real income in month")
     */
    private $additionalCostToRemoveFromRealIncome;
    
    
    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="additional cost to remove from real income in month")
     */
    private $amountGetForSavingPositions;
    
    
    
    /**
     * @ORM\OneToOne(targetEntity="BillingMonth", inversedBy="billingMonthSettlement")
     * @ORM\JoinColumn(name="billing_month_id", referencedColumnName="id")
     */
    private $billingMonth;

    public function __construct() {
        $this->amountGetForSavingPositions = 0;
    }
    
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCostSum(): ?int
    {
        return $this->costSum;
    }

    public function setCostSum(int $costSum): self
    {
        $this->costSum = $costSum;

        return $this;
    }

    public function getIncomeSum(): ?int
    {
        return $this->incomeSum;
    }

    public function setIncomeSum(int $incomeSum): self
    {
        $this->incomeSum = $incomeSum;

        return $this;
    }

    public function getIncomeTax(): ?int
    {
        return $this->incomeTax;
    }

    public function setIncomeTax(int $incomeTax): self
    {
        $this->incomeTax = $incomeTax;

        return $this;
    }

    public function getVatTax(): ?int
    {
        return $this->vatTax;
    }

    public function setVatTax(int $vatTax): self
    {
        $this->vatTax = $vatTax;

        return $this;
    }

    public function getSurplusIncomeTax(): ?int
    {
        return $this->surplusIncomeTax;
    }

    public function setSurplusIncomeTax(int $surplusIncomeTax): self
    {
        $this->surplusIncomeTax = $surplusIncomeTax;

        return $this;
    }

    public function getSurplusVatTax(): ?int
    {
        return $this->surplusVatTax;
    }

    public function setSurplusVatTax(int $surplusVatTax): self
    {
        $this->surplusVatTax = $surplusVatTax;

        return $this;
    }

    public function getUsedTaxFreeAllowance(): ?int
    {
        return $this->usedTaxFreeAllowance;
    }

    public function setUsedTaxFreeAllowance(?int $usedTaxFreeAllowance): self
    {
        $this->usedTaxFreeAllowance = $usedTaxFreeAllowance;

        return $this;
    }

    public function getRestTaxFreeAllowance(): ?int
    {
        return $this->restTaxFreeAllowance;
    }

    public function setRestTaxFreeAllowance(?int $restTaxFreeAllowance): self
    {
        $this->restTaxFreeAllowance = $restTaxFreeAllowance;

        return $this;
    }

    public function getRealIncome(): ?int
    {
        return $this->realIncome;
    }

    public function setRealIncome(int $realIncome): self
    {
        $this->realIncome = $realIncome;

        return $this;
    }
    
    public function getDeductionFromIncomeTaxAmount(): ?int
    {
        return $this->deductionFromIncomeTaxAmount;
    }

    public function setDeductionFromIncomeTaxAmount(int $deductionFromIncomeTaxAmount): self
    {
        $this->deductionFromIncomeTaxAmount = $deductionFromIncomeTaxAmount;

        return $this;
    }
    
    public function getBillingMonth(): ?BillingMonth
    {
        return $this->billingMonth;
    }

    public function setBillingMonth(?BillingMonth $billingMonth): self
    {
        $this->billingMonth = $billingMonth;

        return $this;
    }

    public function getAdditionalCostToRemoveFromRealIncome(): ?int
    {
        return $this->additionalCostToRemoveFromRealIncome;
    }

    public function setAdditionalCostToRemoveFromRealIncome(int $additionalCostToRemoveFromRealIncome): self
    {
        $this->additionalCostToRemoveFromRealIncome = $additionalCostToRemoveFromRealIncome;

        return $this;
    }

    public function getAmountGetForSavingPositions(): ?int
    {
        return $this->amountGetForSavingPositions;
    }

    public function setAmountGetForSavingPositions(int $amountGetForSavingPositions): self
    {
        $this->amountGetForSavingPositions = $amountGetForSavingPositions;

        return $this;
    }

        
}
