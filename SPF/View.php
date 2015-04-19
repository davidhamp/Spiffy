<?php

namespace SPF;

use Mustache;

class View {

    public $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function render($data)
    {
        DependencyManager::get(Constants::MUSTACHE_ENGINE)->render($this->template, $data);
    }

}
