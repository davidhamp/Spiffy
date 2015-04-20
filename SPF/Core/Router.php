<?php

namespace SPF\Core;

use SPF\HTTP\Request;

class Router
{
    protected $routes;

    protected $currentRoute;

    protected $routeOptions;

    protected $routeParams;

    /**
     * @dmManaged
     * @dmProvider SPF\Dependency\Providers\Core\RouterProvider
     */
    public function __construct($routes, Request $request)
    {
        $this->routes = $routes;
        $this->request = $request;
    }

    public function matchRoute()
    {
        //iterate over all routes and find a match based on the request
        foreach ($this->routes as $pattern => $route) {
            if ($pattern === $this->request->uri) {
                $this->currentRoute = $route;
            }
        }
    }

    public function getController()
    {
        return $this->currentRoute['controller'];
    }

    public function getMethod()
    {
        return $this->currentRoute['method'];
    }

    private function routeIsValid($routeInformation) {
        return true;
    }

}