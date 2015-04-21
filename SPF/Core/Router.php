<?php

namespace SPF\Core;

use SPF\HTTP\Request;
use SPF\Dependency\DependencyManager;

class Router
{
    protected $routes;

    protected $currentRoute;

    protected $routeOptions;

    protected $routeParams;

    /**
     * Constructor
     *
     * @method __construct
     *
     * @dmManaged
     * @dmProvider SPF\Dependency\Providers\Core\RouterProvider
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
            if ($pattern === $this->request->uri) {
                if ($this->routeIsValid($route)) {
                    $this->currentRoute = $route;
                    return true;
                }
            }
        }

        return false;
    }

    public function getController()
    {
        return ($this->currentRoute) ? DependencyManager::get($this->currentRoute['controller']) : null;
    }

    public function getMethod()
    {
        return ($this->currentRoute['method']) ? $this->currentRoute['method'] : null;
    }

    private function routeIsValid($route) {
        return is_array($route) && array_key_exists('controller', $route) && array_key_exists('method', $route);
    }

}