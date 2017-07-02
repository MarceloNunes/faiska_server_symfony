<?php

namespace AppBundle\Controller;

use AppBundle\Controller\Helper\BrowseParameters;
use AppBundle\Entity;
use AppBundle\Repository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class User extends Controller
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
        $repository = new Repository\User($entityManager);

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
     * @Route("/user/{userHash}")
     * @Method({"GET"})
     * @param Integer $userHash
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    function getAction($userHash, EntityManagerInterface $entityManager)
    {
        $response = new JsonResponse();
        $repository = new Repository\User($entityManager);

        try {
            $user = $repository->getByHash($userHash);
            $response->setContent($this->json($user->toArray(false)));
        } catch (EntityNotFoundException $e) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        return $response;
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