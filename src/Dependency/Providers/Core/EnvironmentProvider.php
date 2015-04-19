<?php

namespace SPF\Dependency\Providers\Core;

use SPF\Dependency\Constants;
use SPF\Dependency\DependencyManager;
use SPF\Dependency\Provider;
use SPF\Core\Environment;

class EnvironmentProvider extends Provider
{
    public function load()
    {
        $config = DependencyManager::get(Constants::CONFIGURATION);
        return new Environment($config);
    }

}