<?php

namespace AppBundle\Controller\Helper;

use Symfony\Component\HttpFoundation\JsonResponse;

class ControlledResponse
{
    const ALLOWED_METHODS = array(
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
        'HEAD',
        'COPY',
        'LINK',
        'UNLINK',
        'PURGE',
        'LOCK',
        'UNLOCK',
        'PROPFIND',
        'VIEW'
    );

    const DEFAULT_ALLOWED_HEADERS = array(
        'accept',
        'accept-encoding',
        'accept-language',
        'authorization',
        'host',
        'connection',
        'content-type',
        'content-length',
        'origin',
        'user-agent'
    );

    const DEFAULT_ALLOWED_ORIGINS = array(
        '*'
    );

    const DEFAULT_ALLOWED_METHODS = array(
        'GET'
    );

    /** @var  JsonResponse */
    private $response;
    /** @var  string[] */
    private $allowedMethods = array();
    /** @var  string[] */
    private $allowedOrigin = array();
    /** @var string[]  */
    private $allowedHeaders = array();

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
     * @param String[] $methods
     * @return ControlledResponse
     */
    public function addAllowedMethods($methods)
    {
        foreach ($methods as $method) {
            $this->addAllowedMethod($method);
        }
        return $this;
    }

    /**
     * @param String $method
     * @return ControlledResponse
     */
    public function addAllowedMethod($method)
    {
        if (in_array($method, self::ALLOWED_METHODS) &&
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
        if (empty($this->allowedMethods)) {
            $this->allowedMethods = self::DEFAULT_ALLOWED_METHODS;
        }

        return join(', ', $this->allowedMethods);
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
        if (empty($this->allowedOrigin)) {
            foreach (self::DEFAULT_ALLOWED_ORIGINS as $origin)
                $this->addAllowedOrigin($origin);
        }

        return join(', ', $this->allowedOrigin);
    }

    /**
     * @param $header
     * @return ControlledResponse
     */
    public function addAllowedHeader($header)
    {
        if (!in_array($header, $this->allowedHeaders)) {
            $this->allowedHeaders[] = $header;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getAllowedHeaders() {
        if (empty($this->allowedHeaders)) {
            foreach (self::DEFAULT_ALLOWED_HEADERS as $header)
                $this->addAllowedHeader($header);
        }

        return join(', ', $this->allowedHeaders);
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

        $this->response->headers->set(
            'Access-Control-Allow-Headers',
            $this->getAllowedHeaders()
        );

        return $this->response;
    }

}