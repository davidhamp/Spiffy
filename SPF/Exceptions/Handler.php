<?php
/**
 * SPF/Exceptions/Handler.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Exceptions;

use SPF\Dependency\Registry;
use SPF\Dependency\DependencyManager;
use SPF\Core\Environment;
use \Exception;

class Handler
{
    protected $environment;

    public function __construct($environment = 'production')
    {
        $this->environment = $environment;
        set_exception_handler(array($this, 'handle'));
    }

    public function handle(Exception $e)
    {
        $response = DependencyManager::get(Registry::RESPONSE)->setStatusCode(500);

        if ($this->environment === 'development') {
            $message = 'Caught Exception ' . $e->getCode() . ': ' . $e->getMessage();
        } else {
            $message = 'Interal Server Error';
        }

        if ($response->isJson()) {
            $message = json_encode(array('message' => $message));
        }

        $response->setBody($message);
        $response->send();
    }

}