<?php

namespace SPF;

use SPF\Model;
use SPF\View;
use SPF\HTTP\Response;
use SPF\Dependency\DependencyManager;
use SPF\Dependency\Constants;

abstract class Controller {

    protected $view;

    protected $model;

    /**
     * Constructor
     *
     * @method __construct
     * @dmManaged
     */
    public final function __construct()
    {}

    public function setView(View $view)
    {
        $this->view = $view;
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    public function validate()
    {
        return $this->view instanceof View && $this->model instanceof Model;
    }

    public function generateResponse()
    {
        if (!$this->validate()) {
            throw new ControllerException("This controller isn't valid.  Either the view or model haven't been set correctly");
        }

        $response = DependencyManager::get(Constants::RESPONSE);

        return $response->setBody(
            $this->view->render($this->model)
        );
    }

}