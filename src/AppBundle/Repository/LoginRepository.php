<?php

namespace AppBundle\Repository;

use AppBundle\Entity;
use AppBundle\Controller\Helper;
use AppBundle\Exception\Http\BadRequestException;
use AppBundle\Repository\Helper\Validator\UserValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class LoginRepository extends BaseRepository
{

    /**
     * UserRepository constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->setEntityManager($entityManager);
    }

    /**
     * @return array
     * @throws EntityNotFoundException
     * @throws BadRequestException
     */
    public function login()
    {
        $request = Helper\UnifiedRequest::createFromGlobals();

        $userValidator = new UserValidator($request);
        $userValidator->validateLoginData();

        /** @var Entity\User $user */
        $user = $this
            ->entityManager
            ->getRepository(Entity\User::CLASS_NAME)
            ->findOneBy(array(
               'email' => $request->get('email')
            ));

        $this->verifyLogin($request, $user);

        $session = new Entity\Session();
        $session->setUser($user)
            ->setRemoteAddress($request->getRequest()->server->all()['REMOTE_ADDR'])
            ->open()
            ->setHash()
            ->modify();

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return array(
            'authKey' => $session->getHash()
        );
    }

    /**
     * @param Helper\UnifiedRequest $request
     * @param Entity\User $user
     * @throws BadRequestException
     * @throws EntityNotFoundException
     */
    private function verifyLogin(Helper\UnifiedRequest $request, Entity\User $user)
    {
        if (!$user) {
            throw new EntityNotFoundException();
        } else {
            $badRequest = new BadRequestException();

            if (!$user->samePassword($request->get('password'))) {
                $badRequest->addError('password', BadRequestException::INVALID);
            } elseif (!$user->isActive()) {
                $badRequest->addError('email', BadRequestException::INACTIVE);
            }

            if (!empty($badRequest->getErrors())) {
                throw $badRequest;
            }
        }
    }

    /**
     * @param Entity\Session $session
     */
    public function logout (Entity\Session $session)
    {
        $session->close();
        $this->entityManager->persist($session);
        $this->entityManager->flush();
    }
}