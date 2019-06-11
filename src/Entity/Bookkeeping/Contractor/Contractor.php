<?php

namespace App\Entity\Bookkeeping\Contractor;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
use Swagger\Annotations as SWG;
use App\Entity\Bookkeeping\Billing\BillingItem;
use App\Entity\Bookkeeping\Billing\BillingPlannedItem;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Bookkeeping\Contractor\ContractorRepository")
 * @ORM\Table(name="contractor")
 * @UniqueEntity("name")
 * @JMSSerializer\ExclusionPolicy("all")
 */
class Contractor
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
     * @ORM\Column(type="string", name="name", unique=true)
     * 
     * @Assert\NotBlank()
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="contractor name")
     */
    protected $name;
    
    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all"})
     * 
     * @SWG\Property(type="string", description="contractor full name")
     */
    protected $fullName;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all"})
     * 
     * @SWG\Property(type="string", description="address street")
     */
    protected $addressStreet;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all"})
     * 
     * @SWG\Property(type="string", description="address city")
     */
    protected $addressCity;
    
    /**
     * @ORM\Column(type="string", length=8, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all"})
     * 
     * @SWG\Property(type="string", description="address post code")
     */
    protected $addressPostCode;
    
    /**
     * @ORM\Column(type="string", length=16, unique=true, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all"})
     * 
     * @SWG\Property(type="string", description="address post code")
     */
    protected $nip;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Bookkeeping\Billing\BillingItem", mappedBy="contractor")
     */
    private $billingItems;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Bookkeeping\Billing\BillingPlannedItem", mappedBy="contractor")
     */
    private $billingPlannedItems;

    public function __construct()
    {
        $this->billingItems = new ArrayCollection();
        $this->billingPlannedItems = new ArrayCollection();
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
            $billingItem->setContractor($this);
        }

        return $this;
    }

    public function removeBillingItem(BillingItem $billingItem): self
    {
        if ($this->billingItems->contains($billingItem)) {
            $this->billingItems->removeElement($billingItem);
            // set the owning side to null (unless already changed)
            if ($billingItem->getContractor() === $this) {
                $billingItem->setContractor(null);
            }
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
            $billingPlannedItem->setContractor($this);
        }

        return $this;
    }

    public function removeBillingPlannedItem(BillingPlannedItem $billingPlannedItem): self
    {
        if ($this->billingPlannedItems->contains($billingPlannedItem)) {
            $this->billingPlannedItems->removeElement($billingPlannedItem);
            // set the owning side to null (unless already changed)
            if ($billingPlannedItem->getContractor() === $this) {
                $billingPlannedItem->setContractor(null);
            }
        }

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getAddressStreet(): ?string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet(?string $addressStreet): self
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }

    public function setAddressCity(?string $addressCity): self
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressPostCode(): ?string
    {
        return $this->addressPostCode;
    }

    public function setAddressPostCode(?string $addressPostCode): self
    {
        $this->addressPostCode = $addressPostCode;

        return $this;
    }

    public function getNip(): ?string
    {
        return $this->nip;
    }

    public function setNip(?string $nip): self
    {
        $this->nip = $nip;

        return $this;
    }

    
    
}
