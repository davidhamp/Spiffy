<?php

namespace SPF;

use SPF\Dependency\DependencyManager;
use SPF\Dependency\Registry;
use SPF\Exceptions\Handler;
use SPF\Exceptions\SetupException;
use SPF\Exceptions\ControllerException;
use SPF\Core\Controller;
use \Exception;

class Application {

    public function __construct($environment = 'production')
    {
        $exceptionHandler = new Handler($environment);
        DependencyManager::set('ExceptionHandler', $exceptionHandler);

        DependencyManager::set('Environment', $environment);

        if (!defined('__BASE__')) {
            throw new SetupException('Required global constant __BASE__ undefined');
        }

        if (!defined('__PROJECT_NAMESPACE__')) {
            throw new SetupException('Required global constant __PROJECT_NAMESPACE__ undefined');
        }
    }

    public function run()
    {
        $router = DependencyManager::get(Registry::ROUTER);
        $response = DependencyManager::get(Registry::RESPONSE);

        if ($router->matchRoute()) {
            $matchedRoute = $router->getMatchedRoute();

            $controller = DependencyManager::get($matchedRoute['controller']);
            $controller->setParams($matchedRoute['params']);

            if (!($controller instanceof Controller)) {
                throw new ControllerException(
                    "The specified controller class " . $matchedRoute['controller'] . " doesn't exist or isn't a valid Controller class"
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
        $response = DependencyManager::get(Registry::RESPONSE);
        $response->send();
    }
}