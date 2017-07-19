<?php

namespace AppBundle\Controller;

use AppBundle\Controller\Helper\ControlledResponse;
use AppBundle\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @Route("/test")
     * @Method({"GET", "PUT", "PATCH", "POST", "DELETE", "OPTIONS"})
     * @return JsonResponse
     */
    public function testAction(EntityManagerInterface $entityManager)
    {
        $request  = Helper\UnifiedRequest::createFromGlobals();


        $date = new \DateTime('now');

        $data = array(
            'timestamp' => $date->getTimestamp().random_int(0, 999999999999),
            'server' => $request->getRequest()->server->all()['REMOTE_ADDR'],
            'header' => $request->getRequest()->headers->all(),
        );

        $response = new ControlledResponse();

        $response
            ->setStatusCode(200)
            ->addAlowedMethod('GET')
            ->addAlowedMethod('POST')
            ->addAlowedMethod('PUT')
            ->addAlowedMethod('PATCH')
            ->addAlowedMethod('DELETE')
            ->setJsonContent($data);

        return $response->getResult();
    }
}
