<?php

namespace SPF\Exceptions;

use SPF\Dependency\Constants;
use SPF\Dependency\DependencyManager;
use SPF\Core\Environment;
use \Exception;

class ExceptionHandler
{
    public function __construct()
    {
        set_exception_handler(array($this, 'handle'));
    }

    public function handle(Exception $e)
    {
        if (DependencyManager::get(Constants::ENVIRONMENT)->getCurrentEnvironment() === Environment::DEVELOPMENT) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            exit();
        } else {
            exit();
        }
    }

}