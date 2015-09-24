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
class Request {

    public $uri = '';
    public $query = '';

    public $method = '';

    public $get = '';
    public $post = '';
    public $cookie = '';
    public $put = '';

    /**
     * Gathers up key $_SERVER info as well as stores $_GET, $_POST, $_COOKIE, and PUT data (through php://input).
     *
     * @SPF:DmManaged
     */
    public function __construct()
    {
        $this->uri = $_SERVER['SCRIPT_URL'];
        $this->query = $_SERVER['QUERY_STRING'];
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->get = $_GET;
        $this->post = $_POST;
        $this->cookie = $_COOKIE;
        $this->put = file_get_contents("php://input");
    }

}