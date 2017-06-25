<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;

/**
* @ORM\Entity
* @ORM\Table(name="user")
*/
class User
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=256)
     * @var string
     */
    private $email;
    /**
     * @ORM\Column(type="string", length=50)
     * @var string
     */
    private $password;
    /**
     * @ORM\Column(type="string", length=256)
     * @var string
     */
    private $name;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var Date
     */
    private $birthDate = null;
    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $admin = false;
    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    private $createdAt;
    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $active = true;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param mixed $birthDate
     * @return User
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->admin;
    }

    /**
     * @return User
     */
    public function setAdmin()
    {
        $this->admin = true;
        return $this;
    }

    /**
     * @return User
     */
    public function unsetAdmin()
    {
        $this->admin = false;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return User
     */
    public function activate()
    {
        $this->active = true;
        return $this;
    }

    /**
     * @return User
     */
    public function deactivate()
    {
        $this->active = false;
        return $this;
    }

    public function toArray()
    {
        $result =  get_object_vars($this);
        unset($result['password']);
        return $result;
    }
}
