<?php

namespace App\Entity\Bookkeeping\Billing;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Bookkeeping\Billing\BillingMonthRepository")
 * @ORM\Table(name="billing_month")
 * @UniqueEntity("date")
 * @JMSSerializer\ExclusionPolicy("all")
 */
class BillingMonth
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
     * @ORM\Column(type="date", unique=true)
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
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="boolean", description="finished")
     */
    protected $finished;
    
    /**
     * @ORM\OneToMany(targetEntity="BillingItem", mappedBy="billingMonth")
     * @ORM\OrderBy({"date" = "ASC"})
     */
    private $billingItems;
    
    /**
     * @ORM\OneToOne(targetEntity="BillingMonthSettlement", mappedBy="billingMonth")
     */
    private $billingMonthSettlement;

    public function __construct()
    {
        $this->billingItems = new ArrayCollection();
        $this->finished = false;
    }

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

    public function getFinished(): ?bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): self
    {
        $this->finished = $finished;

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
            $billingItem->setBillingMonth($this);
        }

        return $this;
    }

    public function removeBillingItem(BillingItem $billingItem): self
    {
        if ($this->billingItems->contains($billingItem)) {
            $this->billingItems->removeElement($billingItem);
            // set the owning side to null (unless already changed)
            if ($billingItem->getBillingMonth() === $this) {
                $billingItem->setBillingMonth(null);
            }
        }

        return $this;
    }

    public function getBillingMonthSettlement(): ?BillingMonthSettlement
    {
        return $this->billingMonthSettlement;
    }

    public function setBillingMonthSettlement(?BillingMonthSettlement $billingMonthSettlement): self
    {
        $this->billingMonthSettlement = $billingMonthSettlement;

        // set (or unset) the owning side of the relation if necessary
        $newBillingMonth = $billingMonthSettlement === null ? null : $this;
        if ($newBillingMonth !== $billingMonthSettlement->getBillingMonth()) {
            $billingMonthSettlement->setBillingMonth($newBillingMonth);
        }

        return $this;
    }

}
