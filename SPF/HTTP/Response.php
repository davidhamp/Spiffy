<?php
/**
 * SPF/HTTP/Response.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\HTTP;

/**
 * Response object
 *
 * This class is in charge of constructing the proper http response.  Http status codes and headers can be set prior
 *     to script completion in ordr to affect the proper response.  The {@link SPF\HTTP\Response::send()} method will
 *     fire off all headers, before echoing out the body contents.  This happens on {SPF\Application::__destruct()}
 */
class Response {

    protected $statusCode = 200;

    protected $body;

    protected $headers = array('Content-Type' => 'text/html');

    /**
     * Helper method to simpify checing content-type headers against 'application/json'
     *
     * @return bool
     */
    public function isJson()
    {
        return ($this->getContentType() === 'application/json');
    }

    /**
     * Returns the currently set content body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets the response body
     *
     * @param string $body Response body as string or json serializable object
     *
     * @return SPF\HTTP\Response Returns self for chainability
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Gets currently set http headers
     *
     * Will return the specified header, unless $key is not provided, in which case it will return all curretnly set
     *     headers.
     *
     * @param string $key Header key such as "Content-Type"
     *
     * @return string
     */
    public function getHeaders($key = null)
    {
        if ($key && isset($this->headers[$key])) {
            return $this->headers[$key];
        }

        if (is_null($key)) {
            return $this->headers;
        }

        return null;
    }

    /**
     * Sets an HTTP Header
     *
     * @param string $type Header type name
     * @param string $string header content string
     *
     * @return SPF\HTTP\Response Returns self for chainability
     */
    public function setHeader($type, $string)
    {
        $this->headers[$type] = $string;

        return $this;
    }

    /**
     * Helper function to simplify getting 'Content-Type' header
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->headers['Content-Type'];
    }

    /**
     * Helper method to simplify setting 'Content-Type' header
     *
     * @param string $typeString Content-Type string
     *
     * @return SPF\HTTP\Response Returns self for chainability
     */
    public function setContentType($typeString)
    {
        $this->setheader('Content-Type', $typeString);

        return $this;
    }

    /**
     * Sets HTTP response code
     *
     * @param int $code Response code, such as 200 or 404.
     *
     * @return SPF\HTTP\Response Returns self for chainability
     */
    public function setStatusCode($code)
    {
        $this->statusCode = $code;

        return $this;
    }

    /**
     * Constructs HTTP response
     *
     * Called by {@link SPF\Application::_destruct()}.  This sets http_response_code as well as sends all http headers
     *     through PHP's header function.  If the response should be JSON this will json_encode the body before echoing
     *     out the body.
     *
     * @return void This should be the very last thing that happens in the script, and it will echo out content to the
     *              end-user.
     */
    public function send()
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $key => $value) {
            header($key . ': ' . $value);
        }

        if ($this->isJson()) {
            $this->body = json_encode($this->body, JSON_NUMERIC_CHECK);
        }

        echo $this->body;
    }

}