<?php

namespace AppBundle\Repository;

use AppBundle\Entity;
use AppBundle\Controller\Helper;
use AppBundle\Exception\Http\BadRequestException;
use AppBundle\Repository\Helper\Validator\UserValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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
     * @param Helper\UnifiedRequest $request
     * @return array
     * @throws EntityNotFoundException
     * @throws BadRequestException
     */
    public function login(Helper\UnifiedRequest $request)
    {
        $userValidator = new UserValidator($request);
        $userValidator->validateLoginData();

        /** @var Entity\User $user */
        $user = $this
            ->entityManager
            ->getRepository(Entity\User::CLASS_NAME)
            ->findOneBy(array(
               'email' => $request->get('email')
            ));

        if (empty($user)) {
            throw new EntityNotFoundException($request->get('email'));
        }

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
            'sessionHash' => $session->getHash()
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
                throw new UnauthorizedHttpException('Wrong Password');
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