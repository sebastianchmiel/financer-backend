<?php

namespace App\Entity\User;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\User\UserRepository")
 */
class User extends BaseUser {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="firstname", type="string", length=32, nullable=true)
     */
    private $firstname;

    /**
     * @ORM\Column(name="lastname", type="string", length=64, nullable=true)
     */
    private $lastname;
    
    /**
     * @ORM\Column(name="password_changed_at", type="datetime", nullable=true)
     */
    private $passwordChangedAt;

    public function __construct() {
        parent::__construct();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPasswordChangedAt(): ?\DateTime
    {
        return $this->passwordChangedAt;
    }

    public function setPasswordChangedAt(\DateTime $passwordChangedAt): self
    {
        $this->passwordChangedAt = $passwordChangedAt;

        return $this;
    }
}
