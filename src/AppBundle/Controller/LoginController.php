<?php

namespace AppBundle\Controller;

use AppBundle\Exception\Http\BadRequestException;
use AppBundle\Repository\LoginRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    /**
     * @Route("/login")
     * @Method({"POST"})
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function loginAction(EntityManagerInterface $entityManager)
    {
        $response = new JsonResponse();

        try {
            $loginRepository = new LoginRepository($entityManager);
            $authData = $loginRepository->login(Helper\UnifiedRequest::createFromGlobals());
            return $response->setContent($this->json($authData));

        } catch (EntityNotFoundException $e) {
            return $response->setStatusCode(Response::HTTP_NOT_FOUND);

        } catch (BadRequestException $badRequest) {
            return $response
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setContent($this->json($badRequest->getErrors()));
        }
    }

}