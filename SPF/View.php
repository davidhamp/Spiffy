<?php

namespace SPF;

use Mustache;
use SPF\Dependency\DependencyManager;
use SPF\Dependency\Constants;

class View {

    public $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function render($data)
    {
        return DependencyManager::get(Constants::MUSTACHE_ENGINE)->render($this->template, $data);
    }

}
