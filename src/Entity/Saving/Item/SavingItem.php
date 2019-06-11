<?php

namespace App\Entity\Saving\Item;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
use JMS\Serializer\Annotation\VirtualProperty;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Saving\Item\SavingItemRepository")
 * @ORM\Table(name="saving_item")
 * @UniqueEntity("name")
 * @JMSSerializer\ExclusionPolicy("all")
 */
class SavingItem {
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
     * @ORM\Column(type="string", length=128, nullable=false, unique=true)
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
     * @ORM\Column(type="integer", nullable=false)
     * 
     * @Assert\GreaterThan(value=0)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="amount")
     */
    protected $amount;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     * 
     * @Assert\GreaterThanOrEqual(value=0)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="amount collected")
     */
    protected $amountCollected;
    
    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="boolean", description="dynamic amount")
     */
    protected $dynamicAmount;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     * 
     * @Assert\GreaterThan(value=0)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="planned installment")
     */
    protected $plannedInstallment;
    
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
     * @ORM\Column(type="date")
     * 
     * @Assert\NotBlank()
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="date", description="date to")
     */
    protected $dateTo;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     * @Assert\GreaterThan(value=0)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="frequency of payment in months")
     */
    protected $frequencyOfPaymentInMonths;
    
    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="boolean", description="finished")
     */
    protected $finished;
    
    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="boolean", description="used")
     */
    protected $used;

    /**
     * @ORM\OneToMany(targetEntity="SavingItemHistory", mappedBy="savingItem", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"date" = "ASC"})
     */
    private $savingItemHistories;

    
    public function __construct()
    {
        $this->savingItemHistories = new ArrayCollection();
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

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmountCollected(): ?int
    {
        return $this->amountCollected;
    }

    public function setAmountCollected(int $amountCollected): self
    {
        $this->amountCollected = $amountCollected;

        return $this;
    }
    
    public function getDynamicAmount(): ?bool
    {
        return $this->dynamicAmount;
    }

    public function setDynamicAmount(bool $dynamicAmount): self
    {
        $this->dynamicAmount = $dynamicAmount;

        return $this;
    }

    public function getPlannedInstallment(): ?int
    {
        return $this->plannedInstallment;
    }

    public function setPlannedInstallment(int $plannedInstallment): self
    {
        $this->plannedInstallment = $plannedInstallment;

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

    public function getFrequencyOfPaymentInMonths(): ?int
    {
        return $this->frequencyOfPaymentInMonths;
    }

    public function setFrequencyOfPaymentInMonths(?int $frequencyOfPaymentInMonths): self
    {
        $this->frequencyOfPaymentInMonths = $frequencyOfPaymentInMonths;

        return $this;
    }

    public function getFinished(): ?bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): self
    {
        $this->finished = $finished;

        return $this;
    }
    
    public function getUsed(): ?bool
    {
        return $this->used;
    }

    public function setUsed(bool $used): self
    {
        $this->used = $used;

        return $this;
    }

    /**
     * @return Collection|SavingItemHistory[]
     */
    public function getSavingItemHistories(): Collection
    {
        return $this->savingItemHistories;
    }

    public function addSavingItemHistory(SavingItemHistory $savingItemHistory): self
    {
        if (!$this->savingItemHistories->contains($savingItemHistory)) {
            $this->savingItemHistories[] = $savingItemHistory;
            $savingItemHistory->setSavingItem($this);
        }

        return $this;
    }

    public function removeSavingItemHistory(SavingItemHistory $savingItemHistory): self
    {
        if ($this->savingItemHistories->contains($savingItemHistory)) {
            $this->savingItemHistories->removeElement($savingItemHistory);
            // set the owning side to null (unless already changed)
            if ($savingItemHistory->getSavingItem() === $this) {
                $savingItemHistory->setSavingItem(null);
            }
        }

        return $this;
    }
    
    /**
     * @JMSSerializer\VirtualProperty
     * @JMSSerializer\SerializedName("histories")
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     */
    public function getLastHistories() : ArrayCollection {
        return $this->savingItemHistories->filter(function(SavingItemHistory $history) {
            $minDate = (new \DateTime('now'))->modify('-6months');
            return $history->getDate() > $minDate;
        });
    }
}
