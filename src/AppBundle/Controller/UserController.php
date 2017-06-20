<?php

namespace AppBundle\Controller;

use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
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
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function listUsersAction(EntityManagerInterface $em)
    {
        $response = new JsonResponse();
        $params   = new Helper\BrowseParameters(
            array('id', 'name', 'email', 'created_at'),
            $this->countUsers($em)
        );

        $queryBuilder = $em->getRepository('AppBundle:User')
            ->createQueryBuilder('user')
            ->orderBy('user.'.$params->getOrderBy(), $params->getDirection())
            ->setFirstResult($params->getOffset())
            ->setMaxResults($params->getLimit());

        // TODO: Query by keywords

        $users = $queryBuilder->getQuery()->getResult();
        $data  = array();

        foreach ($users as $user) {
            $data[] = $user->toArray();
        }

        $result = array(
            'metadata' => $params->getMetadata(),
            'data' => $data
        );

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