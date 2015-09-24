<?php

namespace SPF\Core;

use SPF\Core\Model;
use SPF\Core\View;
use SPF\Dependency\DependencyManager;
use SPF\Dependency\Registry;
use \Exception;

abstract class Controller {

    protected $view;

    protected $model;

    protected $params;

    /**
     * @SPF:DmManaged
     */
    public function __construct($options = array())
    {}

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function getParam($name)
    {
        return array_key_exists($name, $this->params) ? $this->params[$name] : null;
    }

    public function getView()
    {
        return $this->view;
    }

    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setModel($data)
    {
        $this->model = $data;

        return $this;
    }

    /**
     * Will return either a rendered view or just the model data
     *
     * If the view is set to a string, it will treat it as a template path and create a new View
     *
     * It will then attempt to call the view render on the view if it exists.
     *
     * If not, it will return the model data (which can be null)
     *
     * @method getContent
     *
     * @return mixed
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