<?php

namespace AppBundle\Controller;


use AppBundle\Controller\Helper;
use AppBundle\Entity;
use AppBundle\Repository\SessionRepository;
use AppBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class SessionController extends Controller
{
    /**
     * @Route("/user/{userHash}/sessions")
     * @Method({"GET"})
     * @param EntityManagerInterface $entityManager
     * @param $userHash
     * @return JsonResponse
     */
    public function listByUserAction (EntityManagerInterface $entityManager, $userHash)
    {
        $response          = new Helper\ControlledResponse();
        $userRepository    = new UserRepository($entityManager);
        $sessionRepository = new SessionRepository($entityManager);
        $auth              = new Helper\Authorizator($entityManager);

        try {
            $auth->restrict($auth->isLoggedIn());
            $auth->validate();

            $user = $userRepository->getByHash($userHash);

            $auth->restrict($auth->isAdmin() || $auth->isSameUser($user));
            $auth->validate();

            $parameters = new Helper\BrowseParameters(
                Entity\User::CLASS_ALIAS,
                Entity\User::ORDER_COLUMNS
            );

            $parameters->setCount($sessionRepository->countByUser($user));

            $sessions = $sessionRepository->browseByUser($user, $parameters);
            $data     = array();

            /** @var Entity\Session $session */
            foreach ($sessions as $session) {
                $sessionArray = $session->toArray();
                unset($sessionArray ['user']);
                $data[] = $sessionArray;
            }

            $metadata = array_merge(array('user' => $user->toArray()), $parameters->getMetadata());

            $content = array(
                'metadata' => $metadata,
                'data'     => $data
            );

            return $response->setJsonContent($content)->getResult();

        } catch (EntityNotFoundException $e) {
            return $response->setStatusCode(Response::HTTP_NOT_FOUND)->getResult();

        } catch (UnauthorizedHttpException $e) {
            return $response->setStatusCode(Response::HTTP_UNAUTHORIZED)->getResult();
        }
    }

    /**
     * @Route("/session/{sessionHash}")
     * @Method({"GET"})
     * @param EntityManagerInterface $entityManager
     * @param string $sessionHash
     * @return JsonResponse
     */
    public function getByHash(EntityManagerInterface $entityManager, $sessionHash)
    {
        $response          = new Helper\ControlledResponse();
        $sessionRepository = new SessionRepository($entityManager);
        $auth              = new Helper\Authorizator($entityManager);

        try {
            $auth->restrict($auth->isLoggedIn());
            $auth->validate();

            $session = $sessionRepository->getByHash($sessionHash);

            return $response
                ->setJsonContent($session->toArray(array(
                    'getUser' => true
                )))
                ->getResult();

        } catch (EntityNotFoundException $e) {
            return $response->setStatusCode(Response::HTTP_NOT_FOUND)->getResult();

        } catch (UnauthorizedHttpException $e) {
            return $response->setStatusCode(Response::HTTP_UNAUTHORIZED)->getResult();
        }
    }
}
