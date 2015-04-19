<?php

namespace SPF\Dependency\Providers\Core;

use SPF\Dependency\Provider;
use Symfony\Component\Yaml\Yaml;

class RouterProvider extends Provider
{
    public function load()
    {
        $routes = Yaml::parse(__BASE__ . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'routes.yaml');
        return new Router($routes);
    }

}