<?php

namespace AppBundle\Controller\Helper;

use AppBundle\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Authorizer
{
    /** @var  EntityManagerInterface */
    private $entityManager;
    /** @var bool  */
    private $authError = false;
    /** @var Entity\Session */
    private $session;

    /**
     * Authorizer constructor.
     * @param EntityManagerInterface $entityManager
     */
    function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param bool $rule
     */
    public function restrict($rule)
    {
        if (!$rule) {
            $this->authError = true;
        }
    }

    /**
     * @throws UnauthorizedHttpException
     */
    public function validate()
    {
        if ($this->authError) {
            if (!empty($this->session)) {
                $this->updateLastModifiedTime();
            }

            throw new UnauthorizedHttpException('Unauthorized');
        }

        $this->updateLastModifiedTime();
    }

    private function openSession() {
        if (empty($this->session)) {
            $request = Request::createFromGlobals();
            $sessionHash = $request->headers->get('session-hash');

            if ($sessionHash) {
                $this->session = $this->entityManager
                    ->getRepository(Entity\Session::CLASS_NAME)
                    ->findOneBy(array(
                        'hash' => $sessionHash
                    ));
            }
        }
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        $this->openSession();
        return !empty($this->session) && $this->session->isOpen();
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        $this->openSession();
        return $this->isLoggedIn() && $this->session->getUser()->isAdmin();
    }

    /**
     * @param Entity\User $user
     * @return bool
     */
    public function isSameUser(Entity\User $user)
    {
        $this->openSession();
        return $this->isLoggedIn() && $this->session->getUser()->getHash() === $user->getHash();
    }

    /**
     *
     */
    private function updateLastModifiedTime()
    {
        if (!empty($this->session)) {
            $this->session->modify();
            $this->entityManager->persist($this->session);
            $this->entityManager->flush();
        }
    }

    /**
     * @return Entity\Session
     */
    public function getSession()
    {
        return $this->session;
    }
}
