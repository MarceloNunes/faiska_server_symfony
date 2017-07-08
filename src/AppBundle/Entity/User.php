<?php

namespace AppBundle\Entity;

use AppBundle\Controller\Helper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;

/**
* @ORM\Entity
* @ORM\Table(name="user")
*/
class User
{
    const CLASS_NAME    = 'AppBundle:User';
    const CLASS_ALIAS   = 'user';
    const ORDER_COLUMNS = array('id', 'name', 'email', 'created_at');

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=256, nullable=true)
     * @var string
     */
    private $hash;
    /**
     * @ORM\Column(type="string", length=256)
     * @var string
     */
    private $email;
    /**
     * @Constraints\Email(checkMX=true)
     * @Constraints\NotBlank
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
     * @var \DateTime
     */
    private $birthDate = null;
    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $admin = false;
    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $createdAt;
    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $active = true;

    /**
     * @ORM\OneToMany(targetEntity="Session", mappedBy="user")
     * @var Session[]
     */
    private $sessions;

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
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return User
     */
    public function setHash()
    {
        $this->hash = hash ('ripemd160', $this->email);
        return $this;
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
        $this->password = hash ('ripemd160', $password);
        return $this;
    }

    /**
     * @param string $secret
     * @return boolean
     */
    public function samePassword($secret)
    {
        return $this->password === hash ('ripemd160', $secret);
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
     * @return \DateTime
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param \DateTime $birthDate
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
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

    /**
     * @return Session[]
     */
    public function getSessions()
    {
        return $this->sessions;
    }

    /**
     * @param Session $session
     * @return User
     */
    public function addSessions($session)
    {
        $this->sessions[] = $session;
        return $this;
    }

    /**
     * @return User
     */
    public function resetSessions()
    {
        $this->sessions[] = array();
        return $this;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sessions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get admin
     *
     * @return boolean
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return User
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Add session
     *
     * @param Session $session
     *
     * @return User
     */
    public function addSession(Session $session)
    {
        $this->sessions[] = $session;

        return $this;
    }

    /**
     * Remove session
     *
     * @param Session $session
     */
    public function removeSession(Session $session)
    {
        $this->sessions->removeElement($session);
    }

    public function toArray($getLink = true)
    {
        $user =  get_object_vars($this);

        $result = array();

        foreach (array_keys($user) as $key) {
            switch ($key) {
                case 'secret':
                    break;
                case 'sessions':
                    break;
                case 'createdAt':
                    $result['createdAt'] = $this->getCreatedAt()->format('Y-m-d H:m:s');
                    break;
                case 'birthDate':
                    if ($this->getBirthDate()) {
                        $result['birthDate'] = $this->getBirthDate()->format('Y-m-d');
                    }

                    break;
                default:
                    $result[$key] = $user[$key];
            }
        }

        $link               = Helper\HttpServerVars::getHttpHost() . '/user/'.$user['hash'];
        $result['sessions'] = $link.'/sessions';

        if ($getLink) {
            $result['link'] = $link;
        }

        return $result;
    }
}
