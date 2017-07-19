<?php

namespace AppBundle\Controller\Helper;

use Symfony\Component\HttpFoundation\JsonResponse;

class ControlledResponse
{
    /** @var  JsonResponse */
    private $response;
    /** @var  String[] */
    private $allowedMethods = array();
    /** @var  String[] */
    private $allowedOrigin = array();

    /**
     * ControlledResponse constructor.
     */
    function __construct()
    {
        $this->response = new JsonResponse();
    }

    /**
     * @return JsonResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param JsonResponse $response
     * @return ControlledResponse
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @param $data
     * @return ControlledResponse
     */
    public function setJsonContent($data)
    {
        $this->response->setContent(json_encode($data));
        return $this;
    }

    /**
     * @param String $method
     * @return ControlledResponse
     */
    public function addAlowedMethod($method)
    {
        if (in_array($method, array('GET', 'POST', 'PUT', 'PATCH', 'DELETE')) &&
            !in_array($method, $this->allowedMethods)
        ) {
            $this->allowedMethods[] = $method;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getAllowedMethods()
    {
        if (!empty($this->allowedMethods)) {
            return join(', ', $this->allowedMethods);
        } else {
            return 'GET';
        }
    }

    /**
     * @param $origin
     * @return ControlledResponse
     */
    public function addAllowedOrigin($origin)
    {
        if (!in_array($origin, $this->allowedOrigin)) {
            $this->allowedOrigin[] = $origin;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getAllowedOrigins() {
        if (!empty($this->allowedOrigin)) {
            return join(', ', $this->allowedOrigin);
        } else {
            return '*';
        }
    }

    /**
     * @param $code
     * @return ControlledResponse
     */
    public function setStatusCode($code)
    {
        $this->response->setStatusCode($code);
        return $this;
    }

    /**
     * @return JsonResponse
     */
    public function getResult() {
        $this->response->headers->set(
            'Access-Control-Allow-Origin',
            $this->getAllowedOrigins()
        );

        $this->response->headers->set(
            'Access-Control-Allow-Methods',
            $this->getAllowedMethods()
        );

        return $this->response;
    }

}