<?php
/**
 * SPF/Dependency/Providers/Core/RouterProvider.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Dependency\Providers\Core;

use SPF\Dependency\Provider;
use Symfony\Component\Yaml\Yaml;
use SPF\Core\Router;
use SPF\Dependency\DependencyManager;
use SPF\Dependency\Registry;

/**
 * Router Provider
 *
 * Parses the routes.yaml file which is expected to be in your project's __BASE__ path under /configs.  Then creates a
 * {@link SPF\Core\Router} instance.
 *
 * @uses SPF\HTTP\Request
 * @uses Symfony\Component\Yaml\Yaml
 *
 * @see SPF\Dependency\Provider
 */
class RouterProvider extends Provider
{
    public function load()
    {
        return new Router(
            Yaml::parse(__BASE__ . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'routes.yaml'),
            DependencyManager::get(Registry::REQUEST)
        );
    }

}