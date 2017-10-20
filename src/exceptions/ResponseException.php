<?php
namespace linkprofit\trackerApiClient\exceptions;
use Exception;

class ResponseException extends Exception
{
    public $response;
    public $route;
    public $params;

    public function __construct($message, $code = 0, $response, $route, $params, \Exception $previous = null)
    {
        $this->response = $response;
        $this->route    = $route;
        $this->params   = $params;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Response Exception';
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getParams()
    {
        return json_encode($this->params, 1);
    }

    public function getRoute()
    {
        return $this->route;
    }
}