<?php

namespace App\Entity\Bookkeeping\Billing;

use App\Entity\Balance\BalanceItem;
use App\Entity\Bookkeeping\Contractor\Contractor;
use App\Entity\Bookkeeping\Tag\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
use Swagger\Annotations as SWG;
use App\Entity\Bookkeeping\Billing\BillingItemPosition;
use App\Form\Constraints as CustomAssert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Bookkeeping\Billing\BillingItemRepository")
 * @ORM\Table(name="billing_item")
 * @JMSSerializer\ExclusionPolicy("all")
 */
class BillingItem
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
     * @ORM\Column(type="integer")
     * 
     * @Assert\NotBlank(groups={"typeCost", "typeUs", "typeZus", "typeIncome"})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="item type")
     */
    protected $type;
    
    /**
     * @ORM\Column(type="date")
     * 
     * @Assert\NotBlank(groups={"typeCost", "typeUs", "typeZus", "typeIncome"})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="date", description="date")
     */
    protected $date;
    
    /**
     * @ORM\Column(type="date", nullable=true)
     * 
     * @Assert\NotBlank(groups={"typeIncome"})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="date", description="date")
     */
    protected $dateOfService;
    
    /**
     * @ORM\Column(type="date", nullable=true)
     * 
     * @Assert\NotBlank(groups={"typeIncome"})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="date", description="payment date")
     */
    protected $dateOfPayment;
    
    /**
     * @ORM\Column(type="date", nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="date", description="paid date")
     */
    protected $dateOfPaid;
    
    /**
     * Contractor
     * @ORM\ManyToOne(targetEntity="App\Entity\Bookkeeping\Contractor\Contractor", inversedBy="billingItems")
     * @ORM\JoinColumn(name="contractor_id", referencedColumnName="id")
     * 
     * @Assert\NotNull(groups={"typeCost", "typeUs", "typeZus", "typeIncome"})
     * @CustomAssert\ContractorAllData(groups={"typeIncome"})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="Contractor", description="contractor")
     */
    protected $contractor;

    /**
     * Billing month
     * @ORM\ManyToOne(targetEntity="BillingMonth", inversedBy="billingItems")
     * @ORM\JoinColumn(name="billing_month_id", referencedColumnName="id")
     * 
     */
    protected $billingMonth;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=false)
     * 
     * @Assert\NotBlank(groups={"typeCost", "typeUs", "typeZus", "typeIncome"})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="invoice number")
     */
    protected $invoiceNumber;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="description")
     */
    protected $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     * @Assert\NotBlank(groups={"typeCost", "typeIncome"})
     * @Assert\GreaterThan(value=0, groups={"typeIncome"})
     * @Assert\LessThan(value=0, groups={"typeCost"})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="float", description="amount net")
     */
    protected $amountNet;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     * @Assert\NotBlank(groups={"typeCost", "typeUs", "typeZus", "typeIncome"})
     * @Assert\LessThan(value=0, groups={"typeCost", "typeUs", "typeZus"})
     * @Assert\GreaterThan(value=0, groups={"typeIncome"})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="float", description="amount gross")
     */
    protected $amountGross;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     * @Assert\NotBlank(groups={"typeCost", "typeIncome"})
     * @Assert\LessThan(0, groups={"typeCost"})
     * @Assert\GreaterThanOrEqual(0, groups={"typeIncome"})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="float", description="tax value")
     */
    protected $taxValue;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     * @Assert\NotBlank(groups={"typeCost", "typeIncome"})
     * @Assert\LessThanOrEqual(100, groups={"typeCost"})
     * @Assert\GreaterThanOrEqual(0, groups={"typeCost", "typeIncome"})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="float", description="tax percent")
     */
    protected $taxPercent;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * 
     * @Assert\NotBlank(groups={"typeIncome"})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="date")
     */
    protected $paymentMethod;
    
    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="boolean", description="paid")
     */
    protected $paid;
    
    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="boolean", description="copied")
     */
    protected $copied;   
    
    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="boolean", description="confirmation")
     */
    protected $confirmation;

    /**
     * @ORM\OneToMany(targetEntity="BillingItemPosition", mappedBy="billingItem", cascade={"persist", "remove"}, orphanRemoval=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     */
    private $billingItemPositions;
    
    
    /**
     * Planned item
     * @ORM\ManyToOne(targetEntity="BillingPlannedItem", inversedBy="billingItems")
     * @ORM\JoinColumn(name="billing_planned_item_id", referencedColumnName="id")
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     */
    protected $plannedItem;
    
    
    /**
     * Tags
     * @ORM\ManyToMany(targetEntity="App\Entity\Bookkeeping\Tag\Tag", mappedBy="billingItems", cascade={"persist"})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     */
    private $tags;
    
    /**
     * Balance item
     * @ORM\OneToOne(targetEntity="App\Entity\Balance\BalanceItem", mappedBy="billingItem", cascade={"persist"})
     */
    private $balanceItem;
    
    public function __construct() {
        $this->paid = false;
        $this->copied = false;
        $this->confirmation = false;
        $this->billingItemPositions = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAmountNet(): ?int
    {
        return $this->amountNet;
    }

    public function setAmountNet(?int $amountNet): self
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

    public function setTaxValue(?int $taxValue): self
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

    public function getPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): self
    {
        $this->paid = $paid;

        return $this;
    }

    public function getCopied(): ?bool
    {
        return $this->copied;
    }

    public function setCopied(bool $copied): self
    {
        $this->copied = $copied;

        return $this;
    }

    public function getConfirmation(): ?bool
    {
        return $this->confirmation;
    }

    public function setConfirmation(bool $confirmation): self
    {
        $this->confirmation = $confirmation;

        return $this;
    }

    public function getContractor(): ?Contractor
    {
        return $this->contractor;
    }

    public function setContractor(?Contractor $contractor): self
    {
        $this->contractor = $contractor;

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

    /**
     * @return Collection|BillingItemPosition[]
     */
    public function getBillingItemPositions(): Collection
    {
        return $this->billingItemPositions;
    }

    public function addBillingItemPosition(BillingItemPosition $billingItemPosition): self
    {
        if (!$this->billingItemPositions->contains($billingItemPosition)) {
            $this->billingItemPositions[] = $billingItemPosition;
            $billingItemPosition->setBillingItem($this);
        }

        return $this;
    }
    
    public function clearBillingItemPositions(): self
    {
        $this->billingItemPositions = new ArrayCollection();

        return $this;
    }

    public function removeBillingItemPosition(BillingItemPosition $billingItemPosition): self
    {
        if ($this->billingItemPositions->contains($billingItemPosition)) {
            $this->billingItemPositions->removeElement($billingItemPosition);
            // set the owning side to null (unless already changed)
            if ($billingItemPosition->getBillingItem() === $this) {
                $billingItemPosition->setBillingItem(null);
            }
        }

        return $this;
    }

    public function getDateOfService(): ?\DateTimeInterface
    {
        return $this->dateOfService;
    }

    public function setDateOfService(?\DateTimeInterface $dateOfService): self
    {
        $this->dateOfService = $dateOfService;

        return $this;
    }

    public function getDateOfPayment(): ?\DateTimeInterface
    {
        return $this->dateOfPayment;
    }

    public function setDateOfPayment(?\DateTimeInterface $dateOfPayment): self
    {
        $this->dateOfPayment = $dateOfPayment;

        return $this;
    }
    
    public function getDateOfPaid(): ?\DateTimeInterface
    {
        return $this->dateOfPaid;
    }

    public function setDateOfPaid(?\DateTimeInterface $dateOfPaid): self
    {
        $this->dateOfPaid = $dateOfPaid;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getPlannedItem(): ?BillingPlannedItem
    {
        return $this->plannedItem;
    }

    public function setPlannedItem(?BillingPlannedItem $plannedItem): self
    {
        $this->plannedItem = $plannedItem;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->addBillingItem($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
            $tag->removeBillingItem($this);
        }

        return $this;
    }

    public function getBalanceItem(): ?BalanceItem
    {
        return $this->balanceItem;
    }

    public function setBalanceItem(?BalanceItem $balanceItem): self
    {
        $this->balanceItem = $balanceItem;

        // set (or unset) the owning side of the relation if necessary
        $newBillingItem = $balanceItem === null ? null : $this;
        if ($newBillingItem !== $balanceItem->getBillingItem()) {
            $balanceItem->setBillingItem($newBillingItem);
        }

        return $this;
    }  
    

    
}
