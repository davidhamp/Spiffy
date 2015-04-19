<?php

namespace SPF\Dependency;

use SPF\Dependency\Providers\ProviderBase;
use SPF\Exceptions\DependencyResolutionException;
use \Closure;

class DependencyManager {

    static protected $objects = array();

    static protected $providersDirectories = array('SPF\\Dependency\\Providers');

    static public function set($name, $object)
    {
        if (array_key_exists($name, self::$objects)) {
            throw new DependencyResolutionException("Attempting to set an object with a key that already exists");
            exit();
        }

        self::$objects[$name] = $object;
    }

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

        $className = __PROJECT_NAMESPACE__ . '\\Providers\\' . $name . 'Provider';

        if (class_exists($className)) {
            $class = new $className();
            self::$objects[$name] = $class->load();
        } else {
            $className = 'SPF\\Dependency\\Providers\\' . $name . 'Provider';

            if (class_exists($className)) {
                $class = new $className();
                self::$objects[$name] = $class->load();
            } else {
                throw new DependencyResolutionException("Attempting to retrieve the desired object failed.  It doesn't exist nor is there a provider defined for it");
            }
        }

        return self::$objects[$name];
    }

}