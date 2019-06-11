<?php

namespace App\Entity\Setting;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Setting\SettingRepository")
 * @ORM\Table(name="setting")
 * @UniqueEntity("name")
 * @JMSSerializer\ExclusionPolicy("all")
 */
class Setting
{
    /**
     * @ORM\Id
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
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @JMSSerializer\Expose
     * @JMSSerializer\Groups({"all", "summary"})
     * 
     * @SWG\Property(type="string", description="value")
     */
    protected $value;
    
    
    public function __construct()
    {
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

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
