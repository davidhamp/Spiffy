<?php

namespace SPF;

use SPF\Dependency\DependencyManager;
use SPF\Exceptions\ExceptionHandler;
use SPF\Exceptions\ControllerException;
use \Exception;

class Application {

    protected $controller;

    protected $method;

    public function __construct()
    {
        DependencyManager::set('ExceptionHandler', new ExceptionHandler());

        if (!defined('__BASE__')) {
            throw new SetupException('Required global constant __BASE__ undefined');
        }

        if (!defined('__PROJECT_NAMESPACE__')) {
            throw new SetupException('Required global constant __PROJECT_NAMESPACE__ undefined');
        }

        DependencyManager::set('Application', $this);
    }

    public function run()
    {
        // Inspect route
        // Set controller/method

        if ($this->controller instanceof Controller && !empty($this->method)) {
            $this->controller->{$this->method}();
        } else {
            throw new Exceptions\ControllerException("Either a controller or a method hasn't been set yet.");
        }
    }

    public function get($name)
    {
        die('we tried to get something, yay!  ' . $name);
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