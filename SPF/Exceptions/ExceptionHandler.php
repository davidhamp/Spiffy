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
        $response = DependencyManager::get(Constants::RESPONSE)->setStatusCode(500);

        if (DependencyManager::get(Constants::ENVIRONMENT)->getCurrentEnvironment() === Environment::DEVELOPMENT) {
            $message = 'Caught exception: \n' . $e->getMessage() .  "\n";
        } else {
            $message = 'Interal Server Error';
        }

        if ($response->getResponseType() === 'json') {
            $message = json_encode(array('message' => $message));
        }

        $response->setBody($message);
    }

}