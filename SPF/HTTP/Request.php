<?php
/**
 * SPF/HTTP/Request.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\HTTP;

/**
 * Request abstraction class
 *
 * Centralizes various PHP request information such as $_SERVER, $_GET, $_POST, and $_COOKIE super globals.
 */
class Request
{
    /**
     * Request URI from $_SERVER['REQUEST_URI']
     *
     * @var string
     */
    public $uri = '';

    /**
     * The raw query string from $_SERVER['QUERY_STRING']
     *
     * @var string
     */
    public $query = '';

    /**
     * Request method (GET, POST, PUT, etc) from $_SERVER['REQUEST_METHOD']
     *
     * @var string
     */
    public $method = '';

    /**
     * GET variable data from $_GET
     *
     * @var string
     */
    public $get = '';

    /**
     * POST variable data from $_POST
     *
     * @var string
     */
    public $post = '';

    /**
     * Cookie data from $_COOKIE
     *
     * @var string
     */
    public $cookie = '';

    /**
     * PUT request contents from file_get_contents("php://input")
     *
     * @var string
     */
    public $put = '';

    /**
     * Gathers up key $_SERVER info as well as stores $_GET, $_POST, $_COOKIE, and PUT data (through php://input).
     *
     * @return void
     *
     * @SPF:DmManaged
     */
    public function __construct()
    {
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->query = $_SERVER['QUERY_STRING'];
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->get = $_GET;
        $this->post = $_POST;
        $this->cookie = $_COOKIE;
        $this->put = file_get_contents("php://input");
    }

}