<?php
/**
 * SPF/Core/Router.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Core;

use SPF\HTTP\Request;
use SPF\Dependency\DependencyManager;

/**
 * Determines which Controllers to use based on the routes.yaml config file
 *
 * This class matches the incoming request with a route stored in it's route configuration.  If a route match is found,
 *     it will store the corresponding information about that route for reference by the {@link SPF\Application}.
 */
class Router
{
    protected $routes;

    protected $currentRoute;

    protected $routeOptions;

    /**
     * Constructor
     *
     * Requires a parsed route config and an instance of {@link SPF\HTTP\Request} to compare with.  This class uses a
     * provider to parse the routes.yaml file.
     *
     * @param array            $routes  Parsed routes.yaml config values
     * @param SPF\HTTP|Request $request Request object instance.
     *
     * @uses SPF\Dependency\Providers\Core\RouterProvider
     *
     * @return void
     *
     * @SPF:DmManaged
     * @SPF:DmProvider SPF\Dependency\Providers\Core\RouterProvider
     */
    public function __construct($routes, Request $request)
    {
        $this->routes = $routes;
        $this->request = $request;
    }

    /**
     * Inspects the current request and attempts to match it to a route.
     *
     * This will take the current uri from the request and attempt to match it to a route defined in your project's
     * routes.yaml file.  Your routes.yaml file should contain an array of elements which describe your routes, as
     * well as the controllers, methods, and request methods required by the route.
     * An example routes.yaml file will look like this:
     *     '/route/one':
     *         controller: 'Namespace\Path\To\ControllerOne\{PATH_PARAM}'
     *         method:     'methodToCall'
     *         requestMethod: 'GET'
     *         contentType: 'application/json'
     *         description: 'Description for the humans'
     *     '/route/two/':
     *         controller: 'Namespace\Path\To\ControllerTwo\'
     *         method: 'methodToCallToo'
     *         requestMethod: 'GET'
     *         contentType: 'text/html'
     *         description: 'Humans require descriptions'
     * When a route is found, it's matched data will be stored in {@link SPF\Core\Router::$currentRoute}
     *
     * @return boolean Returns true or false depending on whether or not the Router found a matched route.
     */
    public function matchRoute()
    {
        foreach ($this->routes as $pattern => $route) {

            $pattern = str_replace('/', '\/', preg_replace('/:(\w+)/', '(?<$1>\w+)', $pattern));

            preg_match('#^' . $pattern . '$#', $this->request->uri, $matches);

            if ($matches && $this->routeIsValid($route)) {

                foreach($matches as $key => $value) {
                    if (is_numeric($key)) {
                        unset($matches[$key]);
                    }
                }

                $route['params'] = $matches;
                $this->currentRoute = $route;

                return true;
            }
        }

        return false;
    }

    /**
     * Returns the current matched route data.
     *
     * @return array Matched route data as an array
     */
    public function getMatchedRoute()
    {
        return $this->currentRoute;
    }

    /**
     * Route validation logic
     *
     * Abstracted out so route validation could be maintained outside of the matched route handling.  Will look at the
     * current request and ensure it meets criteria defined in the route.  Namely, the current requestMethod.
     *
     * @internal
     *
     * @param string $route The route data to validate
     *
     * @return boolean
     */
    protected function routeIsValid($route)
    {
        return (is_array($route) && array_key_exists('controller', $route) && array_key_exists('method', $route))
            && (
                (isset($route['options']['requestMethod']))
                    ? $route['requestMethod'] === $this->request->method
                    : true
                );
    }

}