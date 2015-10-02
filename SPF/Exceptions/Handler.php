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
use SPF\HTTP\Response;
use SPF\Core\Environment;
use SPF\Dependency\DependencyManager;
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
        try {
            $response = DependencyManager::get(Registry::RESPONSE);
        } catch (\Exception $e) {
            $response = new Response();
        }

        if ($response->getStatusCode() === 200) {
            $response->setStatusCode(500);
        }

        $message = '';
        if ($this->environment === 'development') {
            if (!$response->isJson()) {
                $message =
                    '<h2>SPF Caught Exception: ' . $e->getMessage() . '</h2>'
                    . '<div>[' . $e->getCode() . '] - ' . get_class($e) . '</div>'
                    . '<div>Exception thrown in <strong>' . $e->getFile() . '</strong> '
                    . 'on line <strong>' . $e->getLine() . '</strong></div>'
                    . '<pre>' . $e->getTraceAsString() . '</pre>';
            } else {
                $message = array(
                    'message' => 'SPF Caught Exception [' . $e->getCode() . '] '
                        . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine()
                );
            }
        } else {
            $message = 'SPF Caught Exception: Interal Server Error';
        }

        $response->setBody($message);
        $response->send();
    }

}
