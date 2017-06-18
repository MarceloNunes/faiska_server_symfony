<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

class UserController extends Controller
{
    /**
     * @Route("/users")
     * @Method({"GET"})
     */
    function listUsersAction()
    {
        return new JsonResponse('Hey you!');
    }

    /**
     * @Route("/user/{userId}")
     * @Method({"GET"})
     * @param Integer $userId
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    function getUserAction($userId, EntityManagerInterface $em)
    {
        $response = new JsonResponse();

        /** @var Entity\User $user */
        $user = $em->getRepository('AppBundle:User')->find($userId);

        if (!$user) {
            return $response->setStatusCode(404); // User not found
        }

        return $response->setContent(json_encode($user->toArray()));
    }

    /**
     * @Route("/users")
     */
    function notAllowedAction()
    {
        $response = new JsonResponse();
        $response->setStatusCode(405); // Method not allowed
        return $response;
    }
}