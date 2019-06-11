<?php

namespace App\Entity\Bookkeeping\Tag;

use App\Entity\Bookkeeping\Billing\BillingItem;
use App\Entity\Bookkeeping\Billing\BillingPlannedItem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
use Swagger\Annotations as SWG;
use App\Entity\Balance\BalanceItem;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Bookkeeping\Tag\TagRepository")
 * @ORM\Table(name="tag")
 * @UniqueEntity("name")
 * @JMSSerializer\ExclusionPolicy("all")
 */
class Tag
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
     * @ORM\Column(type="integer", nullable=false)
     * 
     * @Assert\NotBlank()
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="settlement type")
     */
    protected $settlementType;
    
    /**
     * @ORM\Column(type="string", length=32, nullable=false)
     * 
     * @Assert\NotBlank()
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="background color")
     */
    protected $backgroundColor;
    
    /**
     * @ORM\Column(type="string", length=32, nullable=false)
     * 
     * @Assert\NotBlank()
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="font color")
     */
    protected $fontColor;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Bookkeeping\Billing\BillingItem", inversedBy="tags", cascade={"persist"})
     * @ORM\JoinTable(name="tags_billing_item",
     *      joinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="billing_item_id", referencedColumnName="id")}
     * )
     *
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all"})
     * 
     * @SWG\Property(type="BillingItem", description="billing item")
     */
    protected $billingItems;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Bookkeeping\Billing\BillingPlannedItem", inversedBy="tags", cascade={"persist"})
     * @ORM\JoinTable(name="tags_billing_planned_item",
     *      joinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="billing_planned_item_id", referencedColumnName="id")}
     * )
     *
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all"})
     * 
     * @SWG\Property(type="BillingPlannedItem", description="billing planned item")
     */
    protected $billingPlannedItems;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Balance\BalanceItem", mappedBy="tag", cascade={"persist", "remove"}, orphanRemoval=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all"})
     */
    private $balanceItems;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":true})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="boolean", description="include in balance")
     */
    protected $includeInBalance;
    
    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":true})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="boolean", description="include in balance chart")
     */
    protected $includeInBalanceChart;
    
    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":true})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="boolean", description="include in real cost")
     */
    protected $includeInRealCost;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(
     *      property="bankStatementPhrases",
     *      description="phrases in bank statement",
     *      type="array",
     *      @SWG\Items(
     *          type="object",
     *          @SWG\Property(property="name", type="string")
     *      ),
     * )
     */
    protected $bankStatementPhrases;
    
    
    public function __construct()
    {
        $this->billingItems = new ArrayCollection();
        $this->billingPlannedItems = new ArrayCollection();
        $this->includeInBalance = true;
        $this->includeInBalanceChart = true;
        $this->includeInRealCost = true;
        $this->balanceItems = new ArrayCollection();
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

    public function getSettlementType(): ?int
    {
        return $this->settlementType;
    }

    public function setSettlementType(int $settlementType): self
    {
        $this->settlementType = $settlementType;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(string $backgroundColor): self
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    public function getFontColor(): ?string
    {
        return $this->fontColor;
    }

    public function setFontColor(string $fontColor): self
    {
        $this->fontColor = $fontColor;

        return $this;
    }

    /**
     * @return Collection|BillingItem[]
     */
    public function getBillingItems(): Collection
    {
        return $this->billingItems;
    }

    public function addBillingItem(BillingItem $billingItem): self
    {
        if (!$this->billingItems->contains($billingItem)) {
            $this->billingItems[] = $billingItem;
        }

        return $this;
    }

    public function removeBillingItem(BillingItem $billingItem): self
    {
        if ($this->billingItems->contains($billingItem)) {
            $this->billingItems->removeElement($billingItem);
        }

        return $this;
    }

    /**
     * @return Collection|BillingPlannedItem[]
     */
    public function getBillingPlannedItems(): Collection
    {
        return $this->billingPlannedItems;
    }

    public function addBillingPlannedItem(BillingPlannedItem $billingPlannedItem): self
    {
        if (!$this->billingPlannedItems->contains($billingPlannedItem)) {
            $this->billingPlannedItems[] = $billingPlannedItem;
            $billingPlannedItem->addTag($this);
        }

        return $this;
    }

    public function removeBillingPlannedItem(BillingPlannedItem $billingPlannedItem): self
    {
        if ($this->billingPlannedItems->contains($billingPlannedItem)) {
            $this->billingPlannedItems->removeElement($billingPlannedItem);
        }

        return $this;
    }
    
    public function getIncludeInBalance(): ?bool
    {
        return $this->includeInBalance;
    }

    public function setIncludeInBalance(bool $includeInBalance): self
    {
        $this->includeInBalance = $includeInBalance;

        return $this;
    }  
    
    public function getIncludeInBalanceChart(): ?bool
    {
        return $this->includeInBalanceChart;
    }

    public function setIncludeInBalanceChart(bool $includeInBalanceChart): self
    {
        $this->includeInBalanceChart = $includeInBalanceChart;

        return $this;
    }
    
    public function getIncludeInRealCost(): ?bool
    {
        return $this->includeInRealCost;
    }

    public function setIncludeInRealCost(bool $includeInRealCost): self
    {
        $this->includeInRealCost = $includeInRealCost;

        return $this;
    }

    /**
     * @return Collection|BalanceItem[]
     */
    public function getBalanceItems(): Collection
    {
        return $this->balanceItems;
    }

    public function addBalanceItem(BalanceItem $balanceItem): self
    {
        if (!$this->balanceItems->contains($balanceItem)) {
            $this->balanceItems[] = $balanceItem;
            $balanceItem->setTag($this);
        }

        return $this;
    }

    public function removeBalanceItem(BalanceItem $balanceItem): self
    {
        if ($this->balanceItems->contains($balanceItem)) {
            $this->balanceItems->removeElement($balanceItem);
            // set the owning side to null (unless already changed)
            if ($balanceItem->getTag() === $this) {
                $balanceItem->setTag(null);
            }
        }

        return $this;
    }

    public function getBankStatementPhrases(): ?array
    {
        return $this->bankStatementPhrases;
    }

    public function setBankStatementPhrases(?array $bankStatementPhrases): self
    {
        $this->bankStatementPhrases = $bankStatementPhrases;

        return $this;
    }
}
