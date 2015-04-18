<?php

namespace SPF;

use SPF\DependencyInjection as DI;

class Application {

    protected $di;

    protected $controller;

    protected $method;

    public function __construct($options)
    {
        $this->di = new DI();
    }

    public function run()
    {
        // Build request
        // Match route
        // Instatiate controller
        // Run controller

        //Return response

        $this->controller->{$this->method}();

    }

    public function get($name)
    {
        die('we tried to get something, yay!');
        //return $this->di($name);
    }

    public function setController(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

}