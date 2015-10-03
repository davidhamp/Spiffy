<?php
/**
 * SPF/Core/Configuration.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Core;

use Symfony\Component\Yaml\Yaml;

/**
 * Configuration setting storage class
 *
 * Provides storage for your project's configuration settings.  Expects a 'config.yaml' file in your project's path
 * (<PROJECT_PATH>/configs/config.yaml)
 *
 * @uses Symfony\Component\Yaml\Yaml
 */
class Configuration
{
    protected $config;

    /**
     * Constructor
     *
     * Parses a yaml config file located in your project path (<PROJECT_PATH>/configs/config.yaml)
     *
     * @uses SPF\Dependency\DependencyManager
     *
     * @return void
     *
     * @SPF:DmManaged
     */
    public function __construct()
    {
        $this->config = Yaml::parse(__BASE__ . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'config.yaml');
    }

    /**
     * Fetches a keyed configuration value
     *
     * @param string $key Configuration value to fetch
     *
     * @return string|array|null Parsed yaml config value for corresponding key, or null
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        } else {
            return null;
        }
    }
}