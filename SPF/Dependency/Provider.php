<?php
/**
 * SPF/Dependency/Provider.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Dependency;

/**
 * Class Provider Interface
 */
abstract class Provider
{
    /**
     * Should return an instance of the provided class.
     *
     * Load is called by the DependencyManager and should be responsible for setting up all required dependencies of
     * the class the Provider is meant to handle.  Load should return an instance of the class it's associated with.
     *
     * @return Mixed[] Provided class instance.
     */
    public function load()
    {}
}