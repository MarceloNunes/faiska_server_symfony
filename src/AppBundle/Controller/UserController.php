<?php

namespace AppBundle\Controller;

use AppBundle\Controller\Helper;
use AppBundle\Entity;
use AppBundle\Exception;
use AppBundle\Repository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UserController extends Controller
{
    /**
     * Returns a browsable list of users according to a list of parameters.
     * This method has no expectex exceptions. All invalid parameters are
     * converted to their default value.
     *
     * @Route("/users")
     * @Method({"GET"})
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function listAction(EntityManagerInterface $entityManager)
    {
        $response   = new JsonResponse();
        $repository = new Repository\UserRepository($entityManager);
        $auth       = new Helper\Authorizator($entityManager);

        try {
            $auth->restrict($auth->isAdmin());
            $auth->validate();

            $parameters = new Helper\BrowseParameters(
                Entity\User::CLASS_ALIAS,
                Entity\User::ORDER_COLUMNS
            );

            $parameters->setCount($repository->countByKeyword($parameters));

            $users = $repository->browseByKeyword($parameters);
            $data  = array();

            /** @var Entity\User $user */
            foreach ($users as $user) {
                $data[] = $user->toArray();
            }

            $content = array(
                'metadata' => $parameters->getMetadata(),
                'data' => $data
            );

            return $response->setContent($this->json($content));

        } catch (UnauthorizedHttpException $e) {
            return $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @Route("/user/{userHash}")
     * @Method({"GET"})
     * @param Integer $userHash
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function getAction($userHash, EntityManagerInterface $entityManager)
    {
        $response   = new JsonResponse();
        $repository = new Repository\UserRepository($entityManager);
        $auth       = new Helper\Authorizator($entityManager);

        try {
            $user = $repository->getByHash($userHash);

            $auth->restrict($auth->isAdmin() || $auth->isSameUser($user));

            $response->setContent($this->json($user->toArray(false)));
        } catch (EntityNotFoundException $e) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        return $response;
    }

    /**
     * Users can be inserted both by an unauthenticated user (Sign up)
     * or by an admin user.
     *
     * @Route("/user")
     * @Method({"POST"})
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function insertAction(EntityManagerInterface $entityManager)
    {
        $response       = new JsonResponse();
        $userRepository = new Repository\UserRepository($entityManager);

        try {
            $user = $userRepository->insert(
                Helper\UnifiedRequest::createFromGlobals(),
                $this->get('validator')
            );

            return $response->setContent($this->json($user->toArray()));

        } catch (Exception\Http\BadRequestException $badRequest) {
            return $response
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setContent($this->json($badRequest->getErrors()));
        }
    }

    /**
     * This method manages both UpdateColumn (PATCH) and UpdateAll (PUT)
     * operations. The validator is responsible to filter different rules
     * for each operations.
     *
     * @Route("/user/{userHash}")
     * @Method({"PATCH", "PUT"})
     * @param string $userHash
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function updateAction($userHash, EntityManagerInterface $entityManager)
    {
        $response = new JsonResponse();

        try {
            $userRepository = new Repository\UserRepository($entityManager);

            $user = $userRepository->update(
                $userHash,
                Helper\UnifiedRequest::createFromGlobals(),
                $this->get('validator')
            );

            return $response->setContent($this->json($user->toArray()));

        } catch (EntityNotFoundException $e) {
            return $response->setStatusCode(Response::HTTP_NOT_FOUND);

        } catch (Exception\Http\BadRequestException $badRequest) {
            return $response
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setContent($this->json($badRequest->getErrors()));
        }
    }

    /**
     * @Route("/user/{userHash}")
     * @Method({"DELETE"})
     * @param string $userHash
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function deleteAction($userHash, EntityManagerInterface $entityManager)
    {
        $response = new JsonResponse();

        try {
            $userRepository = new Repository\UserRepository($entityManager);
            $userRepository->delete($userHash);
            return $response;

        } catch (EntityNotFoundException $e) {
            return $response->setStatusCode(Response::HTTP_NOT_FOUND);

        } catch (Exception\Http\BadRequestException $badRequest) {
            return $response
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setContent($this->json($badRequest->getErrors()));
        }
    }
}
