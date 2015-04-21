<?php

namespace SPF\HTTP;

class Response {

    public $status = 200;

    public $body;

    public $headers = array();

    /**
     * Constructor
     *
     * @method __construct
     * @dmManaged
     */
    public function __construct()
    {}

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function setHeader($type, $string)
    {
        $this->headers[$type] = $string;
    }

    public function setContentType($typeString)
    {
        $this->setheader('Content-Type', $typeString);
    }

    public function send()
    {
        http_response_code($this->status);

        foreach ($this->headers as $key => $value) {
            header($key . ': ' . $value);
        }

        echo $this->body;
    }

}