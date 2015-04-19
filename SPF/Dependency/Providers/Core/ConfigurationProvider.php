<?php

namespace SPF\Dependency\Providers\Core;

use SPF\Dependency\Provider;
use SPF\Core\Configuration;

class ConfigurationProvider extends Provider
{
    public function load()
    {
        return new Configuration();
    }

}