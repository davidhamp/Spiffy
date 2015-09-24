<?php

namespace SPF\Dependency;

use SPF\Dependency\Providers\ProviderBase;
use SPF\Exceptions\DependencyResolutionException;
use SPF\Annotations\Engine as AnnotationEngine;
use SPF\Core\ReflectionPool;
use \Closure;

/**
 * DependencyManager
 *
 * This class was designed static so that it could be ubiquitous throughout the framework.
 *
 * It stores objects by a given keyname and retrieves the objects when using the get method.
 *
 * When using fully qualified class names as keys, this class will attempt to find the corresponding class, resolve it's
 *     dependencies, and store a singleton instance of the class for later retrieval.  All dependencies resolved this
 *     way are also stored as singelton instances in the object storage container.
 *
 * Use of the set method directly should be limited, as it muddies the intent of this class.
 *
 * The get method will analyze the doc comments present in your class's constructor.
 *
 * If you wish to have the class's dependencies managed, or if this class is a dependency for another class,
 *     you need to add a @SPF:DmManaged tag.
 *
 * There are two ways in which dependencies can be managed.
 *
 * The first method is to define a Provider for the class.  The provider is a wrapper for your class and has only a load
 *     method.  You can use this to manually resolve dependencies for your class, such as parsed yaml configs, or
 *     non-singleton class instances.  The return value of this load method should be a new instance of your class
 *     with all of it's required dependencies injected into your constructor.
 *
 * To use this first method you need to supply a @SPF:DmProvider <namespace\path\to\provider> tag.   This provider
 *     needs to have a load method that returns an instance of your class.
 *
 * The second method of dependency management is to define the class paths for all of the dependencies of your class's
 *     constructor.  Each requirement needs a @SPF:DmRequires <namespace\path\to\requirement> $<param name> tag.
 *     If the number of requirements doesn't match the number of actual required parameters for the class, this will
 *     throw an exeption.
 *
 * Lastly, you can define providers for your class and put them in predictable locations.  The default location within
 *     the framwork is in SPF\Dependency\Providers, but you can define a new location for your project's providers using
 *     the addProviderLocation method.  When managed tags aren't found in the class, or even if the class itself isn't
 *     found, the DM will then look for a matching class in the defined provider's directories.  If one is found, it
 *     is instantiated, and the load method is again called.
 *
 * This means that you can create providers for third party classes that you do not wish to alter.
 *
 * In all instances, the instance of the object created through any of these means is stored in the object storage for
 *     later retrieval.  Therefore, you should only use the DM for singelton use cases for now.
 */
class DependencyManager {

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
    static protected $providerLocations = array('SPF' => 'SPF\\Dependency\\Providers');

    /**
     * Sets an object into the object storage
     *
     * @method set
     *
     * @param  string $name   Keyname to under which to store the object.
     * @param  mixed $object Object to store.
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
     * @method get
     *
     * @param  string $name This is the keyname to search for.  This is usually a fully qualified class name.
     *
     * @return mixed
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
     * @method addProviderLocation
     *
     * @param string $namespace Namespace search string
     * @param string $location  Namespace replacement string provider location.
     */
    static public function addProviderLocation($namespace, $location)
    {
        if (is_string($location)) {
            array_push(self::$providerLocations, $location);
        }
    }

    /**
     * Processes the class and attempt to resolve all dependencies or call an associated provider
     *
     * @method process
     *
     * @param string $className Fully qualified class name to resolve.
     *
     * @return Object This will throw a DependencyResolutionException if nothing valid is found.
     */
    static protected function process($className)
    {
        if ($object = self::getManaged($className)) {
            return $object;
        }

        if ($object = self::getProvider($className)) {
            return $object;
        }

        throw new DependencyResolutionException("Couldn't figure out what to do with " . $className);
    }

    /**
     * Attempts to fetch an instance of a managed class while resolving all of it's dependencies.
     *
     * @method getManaged
     *
     * @param string $className Class to resolve
     *
     * @return Object Returns an instance of the managed class if found.  Otherwise returns null.
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
     * @method getProvider
     *
     * @param string $className Class to resolve.
     *
     * @return mixed Returns the return value of a matched provider's load method.  Otherwise returns null.
     */
    static protected function getProvider($className)
    {
        foreach (self::$providerLocations as $namespace => $providerLocation) {
            $className = preg_replace('/^' . $namespace . '/', $providerLocation, $className) . 'Provider';
            if (class_exists($className)) {
                $class = new $className();
                return $class->load();
            }
        }

        return null;
    }

}