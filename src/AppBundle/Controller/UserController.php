<?php

namespace AppBundle\Controller;

use AppBundle\Controller\Helper\BrowseParameters;
use AppBundle\Controller\Helper\Metadata;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity;
use AppBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserController extends Controller
{
    /**
     * @param EntityManagerInterface $em
     * @return integer
     */
    private function countUsers (EntityManagerInterface $em) {
        $qb = $em
            ->createQueryBuilder()
            ->select('count(user.id)')
            ->from('AppBundle:User','user');
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @Route("/users")
     * @Method({"GET"})
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function listUsersAction(EntityManagerInterface $entityManager)
    {
        $response   = new JsonResponse();
        $repository = new UserRepository($entityManager);

        $parameters = new BrowseParameters(
            Entity\User::CLASS_ALIAS,
            Entity\User::ORDER_COLUMNS
        );

        $parameters->setCount($repository->countByKeyword($parameters));

        $users      = $repository->browseByKeyword($parameters);
        $data       = array();

        foreach ($users as $user) {
            $data[] = $user->toArray();
        }

        $content = array(
            'metadata' => $parameters->getMetadata(),
            'data' => $data
        );

        return $response->setContent($this->json($content));
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
        $user = $em->getRepository('AppBundle:UserRepository')->find($userId);

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