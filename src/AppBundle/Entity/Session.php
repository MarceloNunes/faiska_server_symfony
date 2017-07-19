<?php

namespace AppBundle\Entity;

use AppBundle\Controller\Helper;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="session")
 */
class Session
{
    const CLASS_NAME    = 'AppBundle:Session';
    const CLASS_ALIAS   = 'session';
    const ORDER_COLUMNS = array('id', 'opened_at', 'closed_at');

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;
    /**
     * @ORM\Column(type="string", length=256)
     * @var string
     */
    private $hash;
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="sessions")
     * @var User
     */
    protected $user;
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $remoteAddress;
    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $openedAt;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $modifiedAt;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $closedAt;
    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $timeout = 36000;

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
     * @return Session
     */
    public function setHash()
    {
        $this->hash = hash ('ripemd160', $this->openedAt->getTimestamp().random_int(0, 99999999999));

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Session
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getRemoteAddress()
    {
        return $this->remoteAddress;
    }

    /**
     * @param string $remoteAddress
     * @return Session
     */
    public function setRemoteAddress($remoteAddress)
    {
        $this->remoteAddress = $remoteAddress;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getOpenedAt()
    {
        return $this->openedAt;
    }

    /**
     * @return \DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @return \DateTime
     */
    public function getClosedAt()
    {
        return $this->closedAt;
    }

    /**
     * @return Session
     */
    public function open()
    {
        $this->openedAt = new \DateTime('now');
        return $this;
    }

    /**
     * @return Session
     */
    public function modify()
    {
        $this->modifiedAt = new \DateTime('now');
        return $this;
    }

    /**
     * @return Session
     */
    public function close()
    {
        $this->closedAt = new \DateTime('now');
        return $this;
    }

    /**
     * @return bool
     */
    public function isOpen()
    {
        if (empty($this->closedAt) && $this->isExpired()) {
            $this->close();
        }

        return empty($this->closedAt);
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return Session
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpirationTime()
    {
        $expirationTime = new \DateTime();
        $expirationTime->setTimestamp($this->modifiedAt->getTimestamp() + $this->timeout);
        return $expirationTime;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        $expirationTimestamp = $this->modifiedAt->getTimestamp() + $this->timeout;
        $nowTime             = new \DateTime('now');
        $nowTimestamp        = $nowTime->getTimestamp();
        return $nowTimestamp > $expirationTimestamp;
    }

    /**
     * @param $options
     * @return array
     */
    public function toArray($options = null)
    {
        $session =  get_object_vars($this);
        $result  = array();
        $this->isOpen();

        if (!is_array($options)) {
            $options = array();
        }

        if (!array_key_exists('getLink', $options)) {
            $options['getLink'] = true;
        }

        if (!array_key_exists('getUser', $options)) {
            $options['getUser'] = false;
        }

        foreach (array_keys($session) as $key) {
            switch ($key) {
                case 'user':
                    if ($options['getUser']) {
                        $result['user'] = !empty($this->user)
                            ? $this->user->toArray($options['getLink'])
                            : null;
                    }
                    break;
                case 'openedAt':
                    $result['openedAt'] = !empty($this->openedAt)
                        ? $this->openedAt->format('Y-m-d H:m:s')
                        : null;
                    break;
                case 'closedAt':
                    $result['closedAt'] = !empty($this->closedAt)
                        ? $this->closedAt->format('Y-m-d H:m:s')
                        : null;
                    break;
                case 'modifiedAt':
                    $result['modifiedAt'] = !empty($this->modifiedAt)
                        ? $this->modifiedAt->format('Y-m-d H:m:s')
                        : null;
                    break;
                default:
                    $result[$key] = $session[$key];
            }
        }

        $result['open']    = $this->isOpen();
        $result['expired'] = $this->isExpired();

        if ($this->isOpen()) {
            $expirationTime = $this->getExpirationTime();
            $now = new \DateTime('now');

            $result['expiresAt']     = $expirationTime->format('Y-m-d H:m:s');
            $result['remainingTime'] = $expirationTime->getTimestamp() - $now->getTimestamp();
        } else {
            $result['expiresAt']     = null;
            $result['remainingTime'] = null;
        }

        $link = Helper\HttpServerVars::getHttpHost() . '/session/'. $this->getHash();

        if ($options['getLink']) {
            $result['link'] = $link;
        }

        return $result;
    }
}
