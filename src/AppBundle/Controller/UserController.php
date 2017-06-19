<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    function listUsersAction(EntityManagerInterface $em)
    {
        $response = new JsonResponse();
        $paging  = new Helper\PagingParameters(array('id', 'name', 'email', 'created_at'));

        $users = $em->getRepository('AppBundle:User')->findAll(
            array(),
            array(
                $paging->getOrderBy() => $paging->getDirection()
            ),
            $paging->getLimit()
        );

        // TODO: Include Metadata
        
        $result = array();

        /** @var Entity\User $user */
        foreach ($users as $user) {
            $result[] = $user->toArray();
        }

        return $response->setContent($this->json($result));
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
            return $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        return $response->setContent($this->json($user->toArray()));
    }

    /**
     * @Route("/users")
     */
    function notAllowedAction()
    {
        $response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);
        return $response;
    }
}