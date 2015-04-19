<?php

namespace SPF\Dependency\Providers\Mustache;

use SPF\Dependency\Provider;
use Mustache\Mustache_Engine;
use Mustache\Mustache_Loader_FilesystemLoader;

class MustacheEngineProvider extends Provider
{
    public function load()
    {
        $options = array(
            'loader' => new Mustache_Loader_FilesystemLoader(__BASE__ . '/views'),
            'partials_loader' => new Mustache_Loader_FilesystemLoader(__BASE__ . '/views/partials')
        );

        return new Mustache_Engine($options);
    }

}