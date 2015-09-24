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

/**
 * Basic Exception Handler
 * 
 * @todo decouple from DependencyManager
 */
class Handler
{
    /**
     * Allows rudimentary control over visibility of displayed exceptions
     * 
     * 'development' will allow detailed exception messages.  Anything else will reslt in a simple "Internal Server Error" message
     * 
     * @var string
     */
    protected $environment;

    /**
     * Registers itself with set_exception_handler.
     * 
     * @param string $environment When set to 'development' will allow for detailed exception messages.
     * 
     * @return void
     */
    public function __construct($environment = 'production')
    {
        $this->environment = $environment;
        set_exception_handler(array($this, 'handle'));
    }
    
    /**
     * Exception handler
     * 
     * Sets the status code of the response to 500.
     * When $environment is set to 'development' will set the response body to the Exception message.  Otherwise the
     * message gets set to 'Internal Server Error'.
     * 
     * If response should be json, will send message as a json object with the message field set to the exception message
     * 
     * @param Exception $e Exception object
     *
     * @return void
     */
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
