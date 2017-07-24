<?php

namespace AppBundle\Controller;

use AppBundle\Controller\Helper;
use AppBundle\Exception\Http\BadRequestException;
use AppBundle\Repository\LoginRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class LoginController extends Controller
{
    /**
     * Takes an email and a password as input parameters, matches the corresponding user,
     * starts a new session and returns an authorization key for that session to be used
     * on future calls.
     *
     * @Route("/login")
     * @Method({"POST"})
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function loginAction(EntityManagerInterface $entityManager)
    {
        $response = new Helper\ControlledResponse();
        $response->addAllowedMethod('POST');

        try {
            $loginRepository = new LoginRepository($entityManager);

            $authData = $loginRepository->login(
                Helper\UnifiedRequest::createFromGlobals()
            );

            return $response->setJsonContent($authData)->getResult();

        } catch (EntityNotFoundException $e) {
            return $response->setStatusCode(Response::HTTP_NOT_FOUND)->getResult();

        } catch (BadRequestException $badRequest) {
            return $response
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setJsonContent($badRequest->getErrors())
                ->getResult();

        } catch (UnauthorizedHttpException $e) {
            return $response->setStatusCode(Response::HTTP_UNAUTHORIZED)->getResult();
        }
    }

    /**
     * @Route("/logout")
     * @Method({"GET", "POST"})
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function logoutAction(EntityManagerInterface $entityManager)
    {
        $response = new Helper\ControlledResponse();
        $response->addAllowedMethods(array('GET', 'POST'));

        $loginRepository = new LoginRepository($entityManager);
        $auth            = new Helper\Authorizer($entityManager);

        try {
            $auth->restrict($auth->isLoggedIn());
            $auth->validate();

            $loginRepository->logout($auth->getSession());

            return $response->getResult();

        } catch (UnauthorizedHttpException $e) {
            return $response->setStatusCode(Response::HTTP_UNAUTHORIZED)->getResult();
        }
    }
}