<?php

namespace App\Entity\Bookkeeping\Billing;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Bookkeeping\Billing\BillingYearConstRepository")
 * @ORM\Table(name="billing_year_const")
 * @UniqueEntity("year")
 * @JMSSerializer\ExclusionPolicy("all")
 */
class BillingYearConst
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
    protected $id;

    /**
     * @ORM\Column(type="smallint", unique=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="year")
     */
    protected $year;
    
    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="tax free allowance amount")
     */
    protected $taxFreeAllowanceAmount;
    
    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="deduction from income tax amount")
     */
    protected $deductionFromIncomeTaxAmount;
    
    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="income tax percent")
     */
    protected $incomeTaxPercent;
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getTaxFreeAllowanceAmount(): ?int
    {
        return $this->taxFreeAllowanceAmount;
    }

    public function setTaxFreeAllowanceAmount(int $taxFreeAllowanceAmount): self
    {
        $this->taxFreeAllowanceAmount = $taxFreeAllowanceAmount;

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

    public function getIncomeTaxPercent(): ?int
    {
        return $this->incomeTaxPercent;
    }

    public function setIncomeTaxPercent(int $incomeTaxPercent): self
    {
        $this->incomeTaxPercent = $incomeTaxPercent;

        return $this;
    }
    
}
