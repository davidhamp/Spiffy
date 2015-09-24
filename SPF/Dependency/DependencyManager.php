<?php
/**
 * SPF/Dependency/DependencyManager.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Dependency;

use SPF\Dependency\Providers\Provider;
use SPF\Exceptions\DependencyResolutionException;
use SPF\Annotations\Engine as AnnotationEngine;
use SPF\Core\ReflectionPool;
use \Closure;

/**
 * DependencyManager
 *
 * This class was designed static so that it could be ubiquitous throughout the framework.
 *
 * It stores objects by a given keyname and retrieves the objects when using the
 *     {@link SPF\Dependency\DependencyManager::get()} method.
 *
 * When using fully qualified class names as keys, this class will attempt to find the corresponding class, resolve it's
 *     dependencies, and store a singleton instance of the class for later retrieval.  All dependencies resolved this
 *     way are also stored as singelton instances in the object storage container.
 *
 * If you wish to have the class's dependencies managed, or if your class is a dependency for another class,
 *     you need to add a @SPF:DmManaged tag.
 *
 * There are two ways in which dependencies can be managed.
 *
 * The first method is to define a Provider for the class.  The provider must extend
 *     {@link SPF\Dependency\Providers\Provider} which only has a
 *     {@link SPF\Dependency\Providers\Provider::load()} method.
 *     You can use this to manually resolve dependencies for your class, such as parsed yaml configs, or
 *     non-singleton class instances.  The return value of this load method should be a new instance of your class
 *     with all of it's required dependencies injected into it's constructor.
 *
 * To use this mthod, you'll need to have a Provider with the same name as your class appended with 'Provider'.  This
 *     Provider should be in your project's ProviderLocation path, which you can define in your bootstrap using the
 *     {@link SPF\Dependency\DependencyManager::addProviderLocation()} method.  New ProviderLocations are searched first
 *     so you can override SPF level class Providers with Providers in your project.
 *
 * Alternatively you can define your provider using the @SPF:DmProvider <namespace\path\to\provider> Annotation.
 *
 * The second method of dependency management is to define the class paths for all of the dependencies of your class's
 *     constructor.  Each requirement needs a @SPF:DmRequires <namespace\path\to\requirement> $<paramName> tag.
 *     If the number of requirements doesn't match the number of actual required parameters for the class, this will
 *     throw an exeption.
 *
 * In all instances, the instance of the object created through any of these means is stored in the object storage for
 *     later retrieval.  Therefore, you should only use the DM for singelton use cases for now.
 *
 * @see SPF\Dependency\Providers\Provider
 *
 * @uses SPF\Annotations\Engine
 */
class DependencyManager
{

    /**
     * Object storage
     *
     * @var array
     */
    static protected $objects = array();

    /**
     * Defines where to search for providers as a fallback.
     *
     * @var array
     */
    static protected $providerLocations = array(
        array('SPF', 'SPF\\Dependency\\Providers')
    );

    /**
     * Sets an item into the object storage
     *
     * While this method is public, it's mainly used to store singleton instances of classes into the $object pool.
     *     However it can be handy to store arbitrary data into the DM for carrying data between scopes.
     *
     * @param string $name   Keyname to under which to store the object.
     * @param mixed  $object Object to store.
     *
     * @throws SPF\Exceptions\DependencyResolutionException
     *
     * @return void
     */
    static public function set($name, $object)
    {
        if (array_key_exists($name, self::$objects) && !is_null(self::$objects[$name])) {
            throw new DependencyResolutionException("Attempting to set an object with a key that already exists");
        }

        self::$objects[$name] = $object;
    }

    /**
     * Checks for an existing object in the storage.
     *
     * If the stored object is a provider, it will overwrite itself with the return value from it's load method.
     * If the stored object is a Closure, it will overwrite itself with it's own return value after being called.
     *
     * If nothing is currently stored, this will then attempt to find a valid object using the process method.
     *
     * @param string $name This is the keyname to search for.  This is usually a fully qualified class name.
     *
     * @return mixed This will return whatever is stored in the object storage container under the requested $name
     */
    static public function get($name)
    {
        if (array_key_exists($name, self::$objects)) {

            if (self::$objects[$name] instanceof Provider) {
                self::$objects[$name] = self::$objects[$name]->load();
            } else if (self::$objects[$name] instanceof Closure) {
                self::$objects[$name] = call_user_func(self::$objects[$name]);
            }

            return self::$objects[$name];
        }

        self::set($name, self::process($name));
        return self::get($name);
    }

    /**
     * Adds a new provider location for provider lookup fallbacks.
     *
     * With this method you can define project-level provider locations which will be included when searching for
     *     providers for any requested objects.
     *
     * @param string $namespace Namespace search string
     * @param string $location  Namespace replacement string provider location.
     *
     * @return void
     */
    static public function addProviderLocation($namespace, $location)
    {
        if (is_string($location)) {
            array_unshift(
                self::$providerLocations,
                array($namespace, $location)
            );
        }
    }

    /**
     * Processes the class and attempt to resolve all dependencies or call an associated provider
     *
     * This will prioritize Providers in the defined providerLocations first.
     *
     * @internal
     *
     * @param string $className Fully qualified class name to resolve.
     *
     * @see SPF\Dependency\DependencyManager::getProvider()
     * @see SPF\Dependency\DependencyManager::getManaged()
     *
     * @throws SPF\Exceptions\DependencyResolutionException
     *
     * @return Object
     */
    static protected function process($className)
    {
        if ($object = self::getProvider($className)) {
            return $object;
        }

        if ($object = self::getManaged($className)) {
            return $object;
        }

        throw new DependencyResolutionException("Couldn't figure out what to do with " . $className);
    }

    /**
     * Attempts to fetch an instance of a managed class while resolving all of it's dependencies.
     *
     * If the $className is found by the autoloader, this will then utilize {@link SPF\Annotations\Engine} to inspect
     *     the class' constructor for an @SPF:DmManaged annotation.  If one exists, it will then look for
     *     an @SPF:DmProvider annotation.  This allows users to manually define a Provider outside of the regular
     *     Provider search path.  If a provider is specified in this way, bu the provider doesn't exist, this will throw
     *     an exception.  If no @SPF:DmProvider tag is present, it will then inspect the required arguments for the
     *     constructor and compare those with the @SPF:DmRequires annotations.  The @SPF:DmRequires annotations take
     *     two parameters.  The first is the fully qualified classname of the dependency, and the second is the
     *     parameter name in the contructor (including the dollar sign).  If not all of the required class parameters
     *     have a corresponding DmRequires annotation this will throw an exception.
     *
     * @param string $className Fully qualified classname to resolve.
     *
     * @throws SPF\Exceptions\DependencyResolutionException
     *
     * @return Object|null Returns an instance of the managed class if found.  Otherwise returns null.
     */
    static protected function getManaged($className)
    {
        if (class_exists($className)) {

            $annotations = AnnotationEngine::get($className);

            if ($annotations->has('DmManaged')) {
                if ($provider = $annotations->get('DmProvider')) {
                    if (class_exists($provider[0][0])) {
                        $provider = new $provider[0][0]();
                        return $provider->load();
                    } else {
                        throw new DependencyResolutionException('The provider defined does not exist: ' . $provider[1]);
                    }
                }

                $reqs = $annotations->get('DmRequires');

                $dependencies = array();
                foreach ($annotations->reflection->getParameters() as $parameter) {
                    if (!$parameter->isOptional()) {
                        foreach ($reqs as $req) {
                            if (substr($req[1], 1) === $parameter->name) {
                                array_push($dependencies, self::get($req[0]));
                            }
                        }
                    }
                }

                if (count($dependencies) < $annotations->reflection->getNumberOfRequiredParameters()) {
                    throw new DependencyResolutionException('You must declare all required dependencies for this class');
                }

                return ReflectionPool::get($className)->newInstanceArgs($dependencies);
            }

            if (!ReflectionPool::get($className)->getConstructor()) {
                return ReflectionPool::get($className)->newInstance();
            }
        }

        return null;
    }

    /**
     * Searches for a matching provider based on the providerLocations defined in this class.  If one is found
     *     this will instantiate it, and return the output of the provider's load method.
     *
     * @param string $className Class to resolve.
     *
     * @see SPF\Dependency\Provider
     *
     * @return mixed Returns the return value of a matched provider's load method.  Otherwise returns null.
     */
    static protected function getProvider($className)
    {
        foreach (self::$providerLocations as $providerLocation) {
            $className = preg_replace('/^' . $providerLocation[0] . '/', $providerLocation[1], $className) . 'Provider';
            if (class_exists($className)) {
                $class = new $className();
                return $class->load();
            }
        }

        return null;
    }

}