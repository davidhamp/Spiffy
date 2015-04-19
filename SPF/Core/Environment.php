<?php

namespace SPF\Core;

use SPF\Core\Configuration;
use SPF\Exceptions\EnvironmentException;

class Environment
{
    const PRODUCTION = 'production';
    const DEVELOPMENT = 'development';

    protected $currentEnv = self::PRODUCTION;

    public function __construct(Configuration $config)
    {
        $env = $config->get('environment');
        if ($env !== self::PRODUCTION && $env !== self::DEVELOPMENT) {
            throw new EnvironmentException('The environment property is not properly set in the config.  Ensure it is either set to "production" or "development"');
            exit();
        }

        $this->currentEnv = $env;
    }

    public function getCurrentEnvironment()
    {
        return $this->currentEnv;
    }

}