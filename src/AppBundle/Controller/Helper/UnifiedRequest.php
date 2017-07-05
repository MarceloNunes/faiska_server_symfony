<?php

namespace AppBundle\Controller\Helper;

use Symfony\Component\HttpFoundation\Request;

class UnifiedRequest
{
    /** array */
    private $requestData;
    /** @var  Request */
    private $request;

    /**
     * UnifiedRequest constructor.
     * @param array $requestData
     */
    function __construct($requestData = array())
    {
        $this->setRequestData($requestData);
    }

    /**
     * @param array $requestData
     * @return UnifiedRequest
     */
    public function setRequestData($requestData)
    {
        $this->requestData = $requestData;
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     * @return UnifiedRequest
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isProvided($key)
    {
        return array_key_exists($key, $this->requestData);
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->requestData)) {
            return $this->requestData[$key];
        }

        return $default;
    }

    public function all()
    {
        return $this->requestData;
    }

    /**
     * Adapted from:
     * https://stackoverflow.com/questions/5483851/manually-parse-raw-http-data-with-php
     *
     * @return UnifiedRequest
     */
    public static function createFromGlobals()
    {
        $request     = Request::createFromGlobals();
        $requestData = $request->request->all();

        if (empty($requestData)) {
            $requestData = array();
            $input = file_get_contents('php://input');

            if (!empty($input)) {
                preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);

                $boundary = $matches[1];
                $a_blocks = preg_split("/-+$boundary/", $input);
                array_pop($a_blocks);

                foreach ($a_blocks as $id => $block) {
                    if (empty($block)) {
                        continue;
                    }

                    if (strpos($block, 'application/octet-stream') !== FALSE) {
                        preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                    } else {
                        preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                    }

                    $requestData[$matches[1]] = $matches[2];
                }
            }
        }

        $unifiedRequest = new UnifiedRequest($requestData);
        $unifiedRequest->setRequest($request);

        return $unifiedRequest;
    }
}
