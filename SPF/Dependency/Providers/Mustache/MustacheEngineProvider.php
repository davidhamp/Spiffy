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
        $options = array(
            'loader' => new Mustache_Loader_FilesystemLoader(__BASE__ . '/views'),
            'partials_loader' => new Mustache_Loader_FilesystemLoader(__BASE__ . '/views/partials')
        );

        return new Mustache_Engine($options);
    }

}