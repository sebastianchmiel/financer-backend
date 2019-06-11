<?php

namespace App\Entity\Bookkeeping\Billing;

use App\Entity\Bookkeeping\Contractor\Contractor;
use App\Entity\Bookkeeping\Tag\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
use Swagger\Annotations as SWG;
use App\Entity\Bookkeeping\Billing\BillingPlannedItemPosition;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Bookkeeping\Billing\BillingPlannedItemRepository")
 * @ORM\Table(name="billing_planned_item")
 * @JMSSerializer\ExclusionPolicy("all")
 */
class BillingPlannedItem
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
     * @ORM\Column(type="integer")
     * 
     * @Assert\NotBlank()
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="item type")
     */
    protected $type;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="date")
     */
    protected $date;
    
    /**
     * @ORM\Column(type="date")
     * 
     * @Assert\NotBlank()
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="date", description="date from")
     */
    protected $dateFrom;
    
    /**
     * @ORM\Column(type="date", nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="date", description="date to")
     */
    protected $dateTo;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="date of service")
     */
    protected $dateOfService;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="payment date")
     */
    protected $dateOfPayment;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="paid date")
     */
    protected $dateOfPaid;
    
    /**
     * Contractor
     * @ORM\ManyToOne(targetEntity="App\Entity\Bookkeeping\Contractor\Contractor", inversedBy="billingPlannedItems")
     * @ORM\JoinColumn(name="contractor_id", referencedColumnName="id")
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="Contractor", description="contractor")
     */
    protected $contractor;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
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
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="amount net")
     */
    protected $amountNet;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="amount gross")
     */
    protected $amountGross;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
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
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="tax percent")
     */
    protected $taxPercent;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="date")
     */
    protected $paymentMethod;
    
    /**
     * @ORM\OneToMany(targetEntity="BillingPlannedItemPosition", mappedBy="billingPlannedItem", cascade={"persist", "remove"}, orphanRemoval=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     */
    private $billingPlannedItemPositions;
    
    
    /**
     * @ORM\OneToMany(targetEntity="BillingItem", mappedBy="plannedItem")
     */
    private $billingItems;
    
    
    /**
     * Tags
     * @ORM\ManyToMany(targetEntity="App\Entity\Bookkeeping\Tag\Tag", mappedBy="billingPlannedItems", cascade={"persist"})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     */
    private $tags;
    
     /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="boolean", description="only as pattern")
     */
    private $onlyAsPattern;
    
    
    public function __construct() {
        $this->billingPlannedItemPositions = new ArrayCollection();
        $this->billingItems = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }
    
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
    
    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

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


    public function getContractor(): ?Contractor
    {
        return $this->contractor;
    }

    public function setContractor(?Contractor $contractor): self
    {
        $this->contractor = $contractor;

        return $this;
    }

    /**
     * @return Collection|BillingPlannedItemPosition[]
     */
    public function getBillingPlannedItemPositions(): Collection
    {
        return $this->billingPlannedItemPositions;
    }

    public function addBillingPlannedItemPosition(BillingPlannedItemPosition $billingPlannedItemPosition): self
    {
        if (!$this->billingPlannedItemPositions->contains($billingPlannedItemPosition)) {
            $this->billingPlannedItemPositions[] = $billingPlannedItemPosition;
            $billingPlannedItemPosition->setBillingPlannedItem($this);
        }

        return $this;
    }
    
    public function clearBillingPlannedItemPositions(): self
    {
        $this->billingPlannedItemPositions = new ArrayCollection();

        return $this;
    }

    public function removeBillingPlannedItemPosition(BillingPlannedItemPosition $billingPlannedItemPosition): self
    {
        if ($this->billingPlannedItemPositions->contains($billingPlannedItemPosition)) {
            $this->billingPlannedItemPositions->removeElement($billingPlannedItemPosition);
            // set the owning side to null (unless already changed)
            if ($billingPlannedItemPosition->getBillingPlannedItem() === $this) {
                $billingPlannedItemPosition->setBillingPlannedItem(null);
            }
        }

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

    public function getDateFrom(): ?\DateTimeInterface
    {
        return $this->dateFrom;
    }

    public function setDateFrom(?\DateTimeInterface $dateFrom): self
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    public function getDateTo(): ?\DateTimeInterface
    {
        return $this->dateTo;
    }

    public function setDateTo(?\DateTimeInterface $dateTo): self
    {
        $this->dateTo = $dateTo;

        return $this;
    }

    /**
     * @return Collection|BillingItems[]
     */
    public function getBillingItems(): Collection
    {
        return $this->billingItems;
    }

    public function addBillingItem(BillingItem $billingItem): self
    {
        if (!$this->billingItems->contains($billingItem)) {
            $this->billingItems[] = $billingItem;
            $billingItem->setPlannedItems($this);
        }

        return $this;
    }

    public function removeBillingItem(BillingItem $billingItem): self
    {
        if ($this->billingItems->contains($billingItem)) {
            $this->billingItems->removeElement($billingItem);
            // set the owning side to null (unless already changed)
            if ($billingItem->getPlannedItems() === $this) {
                $billingItem->setPlannedItems(null);
            }
        }

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDateOfService(): ?string
    {
        return $this->dateOfService;
    }

    public function setDateOfService(?string $dateOfService): self
    {
        $this->dateOfService = $dateOfService;

        return $this;
    }

    public function getDateOfPayment(): ?string
    {
        return $this->dateOfPayment;
    }

    public function setDateOfPayment(?string $dateOfPayment): self
    {
        $this->dateOfPayment = $dateOfPayment;

        return $this;
    }
    
    public function getDateOfPaid(): ?string
    {
        return $this->dateOfPaid;
    }

    public function setDateOfPaid(?string $dateOfPaid): self
    {
        $this->dateOfPaid = $dateOfPaid;

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
            $tag->addBillingPlannedItem($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
            $tag->removeBillingPlannedItem($this);
        }

        return $this;
    }

    public function getOnlyAsPattern(): ?bool
    {
        return $this->onlyAsPattern;
    }

    public function setOnlyAsPattern(bool $onlyAsPattern): self
    {
        $this->onlyAsPattern = $onlyAsPattern;

        return $this;
    }  
    

    
}
