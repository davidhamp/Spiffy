<?php

namespace SPF\Core;

use SPF\HTTP\Request;
use SPF\Dependency\DependencyManager;

class Router
{
    protected $routes;

    protected $currentRoute;

    protected $routeOptions;

    /**
     * Constructor
     *
     * @method __construct
     *
     * @SPF\DmManaged
     * @SPF\DmProvider SPF\Dependency\Providers\Core\RouterProvider
     *
     * @param array   $routes  Compiled yaml routes file
     * @param Request $request Request object instnace
     */
    public function __construct($routes, Request $request)
    {
        $this->routes = $routes;
        $this->request = $request;
    }

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

    public function getMatchedRoute()
    {
        return $this->currentRoute;
    }

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