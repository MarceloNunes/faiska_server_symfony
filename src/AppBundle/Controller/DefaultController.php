<?php

namespace AppBundle\Controller;

use AppBundle\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * @Method({"GET", "PUT", "PATCH", "POST"})
     * @return JsonResponse
     */
    public function testAction(EntityManagerInterface $entityManager)
    {
        $response = new JsonResponse();
        $request  = Helper\UnifiedRequest::createFromGlobals();

        $authKey = $request->getRequest()->headers->all()['auth-key'];

        $session = $entityManager
            ->getRepository(Entity\Session::CLASS_NAME)
            ->findOneBy(array(
                'hash' => $authKey
            ));

        $date = new \DateTime('now');

        return $response->setContent($this->json(array(
            'request' => $request->all(),
            'timestamp' => $date->getTimestamp().random_int(0, 999999999999),
            'server' => $request->getRequest()->server->all()['REMOTE_ADDR'],
            'session' => $session->toArray(),
            'header' => $request->getRequest()->headers->all(),
        )));
    }
}
