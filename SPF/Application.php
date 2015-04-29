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
    }

    public function run()
    {
        $router = DependencyManager::get(Constants::ROUTER);
        $response = DependencyManager::get(Constants::RESPONSE);

        if ($router->matchRoute()) {
            $matchedRoute = $router->getMatchedRoute();

            $controller = DependencyManager::get($matchedRoute['controller']);
            $controller->setParams($matchedRoute['params']);

            if (!($controller instanceof Controller)) {
                throw new ControllerException(
                    "The specified controller class doesn't exist or isn't a valid Controller class"
                );
            }

            if (!method_exists($controller, $matchedRoute['method'])) {
                throw new ControllerException("The defined method does not exist in the controller class");
            }

            $controller->{$matchedRoute['method']}();
            $response->setBody($controller->getContent());

            if (isset($matchedRoute['contentType'])) {
                $response->setContentType($matchedRoute['contentType']);
            }

        } else {
            $response->setStatusCode(404);
        }
    }

    public function __destruct()
    {
        $response = DependencyManager::get(Constants::RESPONSE);
        $response->send();
    }
}