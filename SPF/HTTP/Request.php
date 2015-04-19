<?php

namespace SPF\HTTP;

class Request {

    public $uri = '';
    public $query = '';

    public $method = '';

    public $get = '';
    public $post = '';
    public $put = '';

    public function __construct()
    {
        $this->uri = $_SERVER['SCRIPT_URL'];
        $this->query = $_SERVER['QUERY_STRING'];
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->get = $_GET;
        $this->post = $_POST;
        $this->put = file_get_contents("php://input");
    }

}