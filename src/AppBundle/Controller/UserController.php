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
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UserController extends Controller
{
    /**
     * Returns a browsable list of users according to a list of parameters.
     * This method has no expected exceptions. All invalid parameters are
     * converted to their default value.
     *
     * @Route("/users")
     * @Method({"GET"})
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function listAction(EntityManagerInterface $entityManager)
    {
        $response = new Helper\ControlledResponse();
        $response->addAllowedMethod('GET');

        $repository = new Repository\UserRepository($entityManager);
        $auth       = new Helper\Authorizer($entityManager);

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

            return $response->setJsonContent($content)->getResult();

        } catch (UnauthorizedHttpException $e) {
            return $response->setStatusCode(Response::HTTP_UNAUTHORIZED)->getResult();
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
        $response = new Helper\ControlledResponse();
        $response->addAllowedMethod('GET');

        $repository = new Repository\UserRepository($entityManager);
        $auth       = new Helper\Authorizer($entityManager);

        try {
            // If it is not logged in, then it won't even try to fetch the user.
            $auth->restrict($auth->isLoggedIn());
            $auth->validate();

            $user = $repository->getByHash($userHash);

            // Admin user can get info from any user. Regular user only get info about himself.
            $auth->restrict($auth->isAdmin() || $auth->isSameUser($user));
            $auth->validate();

            return $response->setJsonContent($user->toArray(false))->getResult();

        } catch (EntityNotFoundException $e) {
            return $response->setStatusCode(Response::HTTP_NOT_FOUND)->getResult();

        } catch (UnauthorizedHttpException $e) {
            return $response->setStatusCode(Response::HTTP_UNAUTHORIZED)->getResult();
        }
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
        $response  = new Helper\ControlledResponse();
        $response->addAllowedMethod('POST');

        $userRepository = new Repository\UserRepository($entityManager);
        $auth           = new Helper\Authorizer($entityManager);

        try {
            // That is a tricky one: To create an new user the caller must be either
            // a guest user (not logged in) or and admin user.Regular users can not
            // add new users.
            $auth->restrict(!$auth->isLoggedIn() || $auth->isAdmin());
            $auth->validate();

            $user = $userRepository->insert(
                Helper\UnifiedRequest::createFromGlobals()
            );

            return $response->setJsonContent($user->toArray())->getResult();

        } catch (Exception\Http\BadRequestException $badRequest) {
            return $response
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setJsonContent($badRequest->getErrors())
                ->getResult();

        } catch (UnauthorizedHttpException $e) {
            return $response->setStatusCode(Response::HTTP_UNAUTHORIZED)->getResult();
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
        $response = new Helper\ControlledResponse();
        $response->addAllowedMethods(array('PATCH', 'PUT'));

        $userRepository = new Repository\UserRepository($entityManager);
        $auth           = new Helper\Authorizer($entityManager);

        try {
            $auth->restrict($auth->isLoggedIn());
            $auth->validate();

            $user = $userRepository->getByHash($userHash);

            $auth->restrict($auth->isAdmin() || $auth->isSameUser($user));
            $auth->validate();

            $user = $userRepository->update(
                $user,
                Helper\UnifiedRequest::createFromGlobals()
            );

            return $response->setJsonContent($user->toArray())->getResult();

        } catch (EntityNotFoundException $e) {
            return $response->setStatusCode(Response::HTTP_NOT_FOUND)->getResult();

        } catch (Exception\Http\BadRequestException $badRequest) {
            return $response
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setJsonContent($badRequest->getErrors())
                ->getResult();

        } catch (UnauthorizedHttpException $e) {
            return $response->setStatusCode(Response::HTTP_UNAUTHORIZED)->getResult();
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
        $response = new Helper\ControlledResponse();
        $response->addAllowedMethod('DELETE');

        $userRepository = new Repository\UserRepository($entityManager);
        $auth           = new Helper\Authorizer($entityManager);

        try {
            $auth->restrict($auth->isAdmin());
            $auth->validate();

            $user = $userRepository->getByHash($userHash);

            if ($user->getId() === $auth->getSession()->getUser()->getId()) {
                throw new MethodNotAllowedHttpException(array());
            }

            $userRepository->delete($user);

            return $response->getResult();

        } catch (EntityNotFoundException $e) {
            return $response->setStatusCode(Response::HTTP_NOT_FOUND)->getResult();

        } catch (PreconditionFailedHttpException $precondition) {
            return $response
                ->setStatusCode(Response::HTTP_PRECONDITION_FAILED)
                ->setJsonContent($this->json(array(
                    'restriction' => $precondition->getMessage()
                )))
                ->getResult();

        } catch (MethodNotAllowedHttpException $e) {
            return $response->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED)->getResult();

        } catch (UnauthorizedHttpException $e) {
            return $response->setStatusCode(Response::HTTP_UNAUTHORIZED)->getResult();
        }
    }
}