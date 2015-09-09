<?php

namespace SPF\HTTP;

class Response {

    protected $statusCode = 200;

    protected $body;

    protected $headers = array('Content-Type' => 'text/html');

    /**
     * Constructor
     *
     * @method __construct
     * @dmManaged
     */
    public function __construct()
    {}

    public function isJson()
    {
        return ($this->getContentType() === 'application/json');
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function getHeaders($key = null)
    {
        if ($key && isset($this->headers[$key])) {
            return $this->headers[$key];
        }

        if (is_null($key)) {
            return $this->headers;
        }

        return null;
    }

    public function setHeader($type, $string)
    {
        $this->headers[$type] = $string;

        return $this;
    }

    public function getContentType()
    {
        return $this->headers['Content-Type'];
    }

    public function setContentType($typeString)
    {
        $this->setheader('Content-Type', $typeString);

        return $this;
    }

    public function setStatusCode($code)
    {
        $this->statusCode = $code;

        return $this;
    }

    public function send()
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $key => $value) {
            header($key . ': ' . $value);
        }

        if ($this->isJson()) {
            $this->body = json_encode($this->body, JSON_NUMERIC_CHECK);
        }

        echo $this->body;
    }

}