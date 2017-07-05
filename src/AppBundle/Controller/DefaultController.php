<?php

namespace AppBundle\Controller;

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
     * @Route("/user")
     * @Method({"GET", "PUT", "PATCH", "POST"})
     * @return JsonResponse
     */
    public function updateAction()
    {
        $response = new JsonResponse();
        $request  = Helper\UnifiedRequest::createFromGlobals();

        $date = new \DateTime('now');
        return $response->setContent($this->json(array(
            'request' => $request->all(),
            'timestamp' => $date->getTimestamp().random_int(0, 999999999999)
        )));

//        try {
//            $userRepository = new Repository\User($entityManager);
//
//            $user = $userRepository->insert(
//                Request::createFromGlobals(),
//                $this->get('validator')
//            );
//
//            return $response->setContent($this->json($user->toArray()));
//        }
//        catch (Exception\Http\BadRequest $badRequest) {
//            return $response
//                ->setStatusCode(Response::HTTP_BAD_REQUEST)
//                ->setContent($this->json($badRequest->getErrors()));
//        }
    }

}
