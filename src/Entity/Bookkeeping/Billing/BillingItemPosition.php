<?php

namespace App\Entity\Bookkeeping\Billing;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
use Swagger\Annotations as SWG;
use App\Entity\Bookkeeping\Billing\BillingItem;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Bookkeeping\Billing\BillingItemPositionRepository")
 * @ORM\Table(name="billing_item_position")
 * @JMSSerializer\ExclusionPolicy("all")
 */
class BillingItemPosition
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
     * Billing item
     * @ORM\ManyToOne(targetEntity="BillingItem", inversedBy="billingItemPositions")
     * @ORM\JoinColumn(name="billing_item_id", referencedColumnName="id")
     * 
     */
    protected $billingItem;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=false)
     * 
     * @Assert\NotBlank()
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="name")
     */
    protected $name;
    
    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":1})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="quantity")
     */
    protected $quantity;
    
    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="unit")
     */
    protected $unit;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     * 
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value=0)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="amount net single")
     */
    protected $amountNetSingle;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     * 
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value=0)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="amount net")
     */
    protected $amountNet;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     * 
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="amount gross")
     */
    protected $amountGross;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     * 
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual(0)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="tax value")
     */
    protected $taxValue;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(100)
     * @Assert\GreaterThanOrEqual(0)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="tax percent")
     */
    protected $taxPercent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getAmountNetSingle(): ?int
    {
        return $this->amountNetSingle;
    }

    public function setAmountNetSingle(int $amountNetSingle): self
    {
        $this->amountNetSingle = $amountNetSingle;

        return $this;
    }
    
    public function getAmountNet(): ?int
    {
        return $this->amountNet;
    }

    public function setAmountNet(int $amountNet): self
    {
        $this->amountNet = $amountNet;

        return $this;
    }

    public function getAmountGross(): ?int
    {
        return $this->amountGross;
    }

    public function setAmountGross(int $amountGross): self
    {
        $this->amountGross = $amountGross;

        return $this;
    }

    public function getTaxValue(): ?int
    {
        return $this->taxValue;
    }

    public function setTaxValue(int $taxValue): self
    {
        $this->taxValue = $taxValue;

        return $this;
    }

    public function getTaxPercent(): ?int
    {
        return $this->taxPercent;
    }

    public function setTaxPercent(?int $taxPercent): self
    {
        $this->taxPercent = $taxPercent;

        return $this;
    }

    public function getBillingItem(): ?BillingItem
    {
        return $this->billingItem;
    }

    public function setBillingItem(?BillingItem $billingItem): self
    {
        $this->billingItem = $billingItem;

        return $this;
    }
    
        
}
