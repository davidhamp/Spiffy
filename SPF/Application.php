<?php
/**
 * SPF/Application.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF;

use SPF\Dependency\DependencyManager;
use SPF\Dependency\Registry;
use SPF\Exceptions\Handler;
use SPF\Exceptions\SetupException;
use SPF\Exceptions\ControllerException;
use SPF\Core\Controller;

/**
 * SPF Main Application
 *
 * Main Application class.  This should be instantiated in your project bootstrap file, and is kicked off by calling the
 * {@link SPF\Application::run()} method.
 *
 * @uses SPF\Dependency\DependencyManager
 */
class Application
{
    /**
     * Application Constructor
     *
     * When Application is created, it sets up the {@link SPF\Exceptions\Handler} and checks for required application
     * constants __BASE__ and __PROJECT_NAMESPACE__
     *
     * @param  string $environment Used to make output decisions in code depending on your intended environement.  This
     *                            gets set to the {@link SPF\Dependency\DependencyManager} under 'Environment'
     *
     * @throws SPF\Exceptions\SetupException
     *
     * @return void
     */
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

    /**
     * Kicks off the SPF Application
     *
     * Attempts to match the current router with {@link SPF\Core\Router}.  If a route is found, this will attempt to
     * instantiate the specified controller and ultimately call the specified method on the controller. Finally, the
     * Application will attempt to set the controller's content on the {@link SPF\HTTP\Response}.  Otherwise this will
     * set the status code to 404.
     *
     * @uses SPF\Core\Router
     * @uses SPF\HTTP\Response
     *
     * @return void
     */
    public function run()
    {
        $router = DependencyManager::get(Registry::ROUTER);
        $response = DependencyManager::get(Registry::RESPONSE);

        if ($router->matchRoute()) {
            $matchedRoute = $router->getMatchedRoute();

            if (isset($matchedRoute['contentType'])) {
                $response->setContentType($matchedRoute['contentType']);
            }

            $controller = DependencyManager::get($matchedRoute['controller']);

            if (!($controller instanceof Controller)) {
                throw new ControllerException(
                    "The specified controller class "
                        . $matchedRoute['controller']
                        . " doesn't exist or isn't a valid Controller class"
                );
            }

            if (!method_exists($controller, $matchedRoute['method'])) {
                throw new ControllerException("The defined method does not exist in the controller class");
            }

            $controller->setParams($matchedRoute['params']);

            $controller->{$matchedRoute['method']}();
            $response->setBody($controller->getContent());

        } else {
            $response->setStatusCode(404);
            $response->setBody('SPF: The requested route was not found.');
        }

        $response = DependencyManager::get(Registry::RESPONSE);
        $response->send();
    }
}