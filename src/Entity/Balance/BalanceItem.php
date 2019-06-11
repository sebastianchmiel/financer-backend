<?php

namespace App\Entity\Balance;

use App\Entity\Bookkeeping\Billing\BillingItem;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
use Swagger\Annotations as SWG;
use App\Entity\Bookkeeping\Tag\Tag;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Balance\BalanceItemRepository")
 * @ORM\Table(name="balance_item")
 * @UniqueEntity("name")
 * @JMSSerializer\ExclusionPolicy("all")
 */
class BalanceItem
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
     * @ORM\Column(type="datetime", nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="operation date")
     */
    protected $dateOperation;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="posting date")
     */
    protected $datePosting;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all"})
     * 
     * @SWG\Property(type="string", description="description")
     */
    protected $description;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all"})
     * 
     * @SWG\Property(type="string", description="title")
     */
    protected $title;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all"})
     * 
     * @SWG\Property(type="string", description="sender or receiver")
     */
    protected $senderReceiver;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all"})
     * 
     * @SWG\Property(type="string", description="account number")
     */
    protected $accountNumber;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all"})
     * 
     * @SWG\Property(type="string", description="amount")
     */
    protected $amount;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all"})
     * 
     * @SWG\Property(type="string", description="balance")
     */
    protected $balance;
    
    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all"})
     * 
     * @SWG\Property(type="string", description="hash")
     */
    protected $hash;
    
    

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Bookkeeping\Billing\BillingItem", inversedBy="balanceItem")
     * @ORM\JoinColumn(name="billing_item_id", referencedColumnName="id")
     */
    private $billingItem;
    
    /**
     * Tag
     * @ORM\ManyToOne(targetEntity="App\Entity\Bookkeeping\Tag\Tag", inversedBy="balanceItems")
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     */
    protected $tag;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateOperation(): ?\DateTimeInterface
    {
        return $this->dateOperation;
    }

    public function setDateOperation(?\DateTimeInterface $dateOperation): self
    {
        $this->dateOperation = $dateOperation;

        return $this;
    }

    public function getDatePosting(): ?\DateTimeInterface
    {
        return $this->datePosting;
    }

    public function setDatePosting(?\DateTimeInterface $datePosting): self
    {
        $this->datePosting = $datePosting;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSenderReceiver(): ?string
    {
        return $this->senderReceiver;
    }

    public function setSenderReceiver(?string $senderReceiver): self
    {
        $this->senderReceiver = $senderReceiver;

        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): self
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(?int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getBalance(): ?int
    {
        return $this->balance;
    }

    public function setBalance(?int $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(?string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getBillingItem(): ?BillingItem
    {
        return $this->billingItem;
    }

    public function setBillingItem(?BillingItem $billingItem): self
    {
        $this->billingItem = $billingItem;

        // set (or unset) the owning side of the relation if necessary
        $newBalanceItem = $billingItem === null ? null : $this;
        if ($newBalanceItem !== $billingItem->getBalanceItem()) {
            $billingItem->setBalanceItem($newBalanceItem);
        }

        return $this;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): self
    {
        $this->tag = $tag;

        return $this;
    }
    
}
