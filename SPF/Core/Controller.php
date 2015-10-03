<?php
/**
 * SPF/Core/Controller.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Core;

use SPF\Core\View;
use SPF\Dependency\DependencyManager;
use SPF\Dependency\Registry;

/**
 * Core Base Controller
 *
 * All controllers in your project should extend from this base controller as it's detected by the Application
 * before proceeding with calling the view method defined in your route.
 *
 * In your Controller's view method, you are in charge of assigning model data as well as a view.  The view can
 * simply be a path string to the template you intend to use for the view, but you can also use an instance of
 * {@link SPF\Core\View} as well, which is recommended if you require additional rendering or display logic
 * outside of the {@link SPF\Core\View::render()} method.
 *
 * @uses SPF\Dependency\DependencyManager
 */
abstract class Controller
{

    /**
     * View storage which should contain either a path to a template file or an instance of {@link SPF\Core\View}
     *
     * @var string|{@link SPF\Core\View}
     */
    protected $view;

    /**
     * Mustache renderable data object.  Typically this should be an instance of {@link SPF\Core\Model}, however
     * any Traversable or Serializable data structure should do (Such as an associative array)
     *
     * @var array|{@link SPF\Core\Model}
     */
    protected $model;

    /**
     * Parameters defined in the route path and stored here
     *
     * @var array
     */
    protected $params;

    /**
     * Constructor
     *
     * @param array $options Currently not implemented, but this would be an array of options defined in the routes
     *                       file.
     *
     * @return void
     */
    public function __construct($options = array())
    {}

    /**
     * Sets parameters
     *
     * This gets set during {@link SPF\Application::run} and is set with path parameters from the matched route.
     *
     * @param array $params Array of named parameters from the matched route.
     *
     * @return SPF\Core\Controller Returns self to enable chaining
     */
    public function setParams($params = array())
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Returns the named parameter.
     *
     * @param string $name The name of the parameter to get.
     *
     * @return string|null returns the parameter value or null if it doesn't exist.
     */
    public function getParam($name)
    {
        return array_key_exists($name, $this->params) ? $this->params[$name] : null;
    }

    /**
     * Returns the currently set View
     *
     * @return string|SPF\Core\View View can be either an instance of {@link SPF\Core\View} or a string representing a
     *                              template file path.
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Set View to either an instance of {@link SPF\Core\View} or a string representing a template file path.
     *
     * @param string|SPF\Core\View $view Either an instance of {@link SPF\Core\View} or a template file path.
     *
     * @return SPF\Core\Controller Returns self to enable chaining
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Returns the currently set model data which can be any parsable PHP data type, or an instance of
     * {@link SPF\Core\Model}
     *
     * @return Mixed[] Any Mustache renderable data type.
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Sets the data to model.  This can be any Mustache parsable data type, preferably an instance of
     * {@link SPF\Core\Model}
     *
     * @param Mixed[] $data Any Mustache renderable data type, typically an array or preferably an instance of
     *                      {@link SPF\Core\Model}
     *
     * @return SPF\Core\Controller Returns self to enable chaining
     */
    public function setModel($data)
    {
        $this->model = $data;

        return $this;
    }

    /**
     * Get rendered view content or model data as JSON.
     *
     * This will first check if the {@link SFP\HTTP\Response} is set to render JSON.  If it is, this returns just the
     * model.  If Response is not JSON, will then inspect the {@link SPF\Core\Controller:view} property.  If View is
     * a string, it will create a new View instance using that string as the template source.  If View is already an
     * instance of {@link SPF\Core\View} it will call {@link SPF\Core\View::render()} using the current model data.
     *
     * @return Mixed[] Either a string, or the current model data (which will be serialized by PHP)
     */
    public function getContent()
    {
        if (DependencyManager::get(Registry::RESPONSE)->isJson()) {
            return $this->model;
        }

        if (!is_null($this->view)) {
            if (is_string($this->view)) {
                $this->view = new View($this->view);
            }

            if ($this->view instanceof View) {
                return $this->view->render($this->model);
            }
        }

        return $this->model;
    }
}
