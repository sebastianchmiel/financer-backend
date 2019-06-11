<?php

namespace App\Entity\Saving\Item;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
use Swagger\Annotations as SWG;
use App\Entity\Saving\Item\SavingItem;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Saving\Item\SavingItemHistoryRepository")
 * @ORM\Table(name="saving_item_history")
 * @JMSSerializer\ExclusionPolicy("all")
 */
class SavingItemHistory
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
     * Saving item
     * @ORM\ManyToOne(targetEntity="SavingItem", inversedBy="savingItemHistories")
     * @ORM\JoinColumn(name="saving_item_id", referencedColumnName="id")
     * 
     */
    protected $savingItem;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=false)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="name")
     */
    protected $name;
    
    /**
     * @ORM\Column(type="date", nullable=false)
     * 
     * @Assert\NotBlank()
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="date", description="date")
     */
    protected $date;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     * 
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value=0)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="integer", description="amount")
     */
    protected $amount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getSavingItem(): ?SavingItem
    {
        return $this->savingItem;
    }

    public function setSavingItem(?SavingItem $savingItem): self
    {
        $this->savingItem = $savingItem;

        return $this;
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
}
