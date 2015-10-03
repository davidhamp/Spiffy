<?php
/**
 * SPF/Dependency/Registry.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Dependency;

/**
 * This constants file is used for maintanability of DM managed classes.
 *
 * If any of said classes need to be refactored, you would only have to change the ClassName in this file, rathr than
 * hunt down all instances of {@link SPF\Dependency\DependencyManager::get()} using the class' name.  It is recommended
 * you extend this constants file with one in your own project fand use only the project-level constants file
 * throughout, even for SPF classes.
 */
class Registry
{

    const CONFIGURATION = 'SPF\\Core\\Configuration';

    const ENVIRONMENT = 'SPF\\Core\\Environment';

    const MUSTACHE_ENGINE = 'SPF\\Mustache\\MustacheEngine';

    const REQUEST = 'SPF\\HTTP\\Request';

    const RESPONSE = 'SPF\\HTTP\\Response';

    const ROUTER = 'SPF\\Core\\Router';

    const DATABASE = 'SPF\\Core\\Database';

}