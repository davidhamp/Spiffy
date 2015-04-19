<?php

namespace SPF;

class Controller {

    protected $view;

    protected $model;

    public function __construct()
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
            throw new ControllerException("This controller isn't valid.  Either the view or model havne't been set correctly");
        }

        $response = new Response();

        return $response->setBody(
            $this->view->render($this->model)
        );
    }

}