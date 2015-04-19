<?php

namespace SPF\Core;

class Router
{
    protected $routes;

    protected $currentRoute;

    protected $routeOptions;

    protected $routeParams;

    public function __construct($routes, Request $request)
    {
        $this->routes = $routes;
        $this->request = $request;
    }

    public function matchRoute()
    {
        //iterate over all routes and find a match based on the request
        foreach ($this->routes as $pattern => $route) {
            if (preg_match($pattern, $this->request->uri)) {
                // pull out
            }
        }
    }

    private function routeIsValid($routeInformation) {
        return true;
    }

}