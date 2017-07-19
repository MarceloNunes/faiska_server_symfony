<?php

namespace AppBundle\Controller;

use AppBundle\Controller\Helper\ControlledResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath(
                $this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR
        ]);
    }

    /**
     * @Route("/test")
     * @Route("/test/")
     * @Method({"GET", "PUT", "PATCH", "POST", "DELETE", "OPTIONS"})
     * @return JsonResponse
     */
    public function testAction()
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
            ->addAllowedMethods(array('GET', 'POST', 'PUT', 'PATCH', 'DELETE'))
            ->setJsonContent($data);

        return $response->getResult();
    }
}
