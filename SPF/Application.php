<?php

namespace SPF;

use SPF\Dependency\DependencyManager;
use SPF\Dependency\Constants;
use SPF\Exceptions\ExceptionHandler;
use SPF\Exceptions\ControllerException;
use \Exception;

class Application {

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
        $router = DependencyManager::get(Constants::ROUTER);

        $env = DependencyManager::get(Constants::ENVIRONMENT);

        if ($router->matchRoute()) {
            $controller = $router->getController();
            $method = $router->getMethod();

            if (!($controller instanceof Controller)) {
                throw new ControllerException("The specified controller class isn't an instance of Controller");
            }

            if (empty($method)) {
                throw new ControllerException("Method is not defined");
            }

            if (!method_exists($controller, $method)) {
                throw new ControllerException("The defined method does not exist in the controller class");
            }

            $controller->{$method}();
        }

        $response = DependencyManager::get(Constants::RESPONSE);
        $response->send();
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