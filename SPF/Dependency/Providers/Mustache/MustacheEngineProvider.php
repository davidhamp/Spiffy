<?php
/**
 * SPF/Dependency/Providers/Mustache/MustacheEngineProvider.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Dependency\Providers\Mustache;

use SPF\Dependency\Provider;
use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;
use SPF\Dependency\DependencyManager;
use SPF\Dependency\Registry;

/**
 * Creates an instance of Mustache_Engine
 *
 * Assumes your project has a /views direction in __BASE__ and a partials directory in /views.
 *
 * @todo Remove /partials directory requirement, and do directory detection on that instead.
 *
 * @see SPF\Dependency\Provider
 *
 * @return Mustache_Engine
 */
class MustacheEngineProvider extends Provider
{
    public function load()
    {
        $options = array();

        if ($templatePath = DependencyManager::get(Registry::CONFIGURATION)->get('templatePath')) {
            $options['loader'] = new Mustache_Loader_FilesystemLoader($templatePath);
        }

        if ($templatePartialsPath = DependencyManager::get(Registry::CONFIGURATION)->get('templatePartialsPath')) {
            $options['partials_loader'] = new Mustache_Loader_FilesystemLoader($templatePartialsPath);
        }

        return new Mustache_Engine($options);
    }

}