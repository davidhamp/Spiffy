<?php

namespace SPF\Dependency;

use SPF\Dependency\Providers\ProviderBase;
use SPF\Exceptions\DependencyResolutionException;
use \Closure;
use \reflectionClass;

class DependencyManager {

    static protected $objects = array();

    static protected $providersDirectories = array('SPF\\Dependency\\Providers');

    static public function set($name, $object)
    {
        if (array_key_exists($name, self::$objects) && !is_null(self::$objects[$name])) {
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

        self::set($name, self::process($name));
        return self::get($name);
    }

    static protected function process($name)
    {
        if ($object = self::isManaged($name)) {
            return $object;
        }

        if ($object = self::hasProvider($name)) {
            return $object;
        }

        throw new DependencyResolutionException("Couldn't figure out what to do with " . $name);
    }

    static protected function isManaged($name)
    {

        if (class_exists($name)) {
            $reflectionClass = new reflectionClass($name);
            $constructor = $reflectionClass->getConstructor();
            $comments = $constructor ? $constructor->getDocComment() : '';

            if (preg_match('/@dmManaged\n/', $comments)) {
                if (preg_match('/@dmProvider\s+([\w\\\\]+)/s', $comments, $provider)) {
                    if (class_exists($provider[1])) {
                        $provider = new $provider[1]();
                        return $provider->load();
                    } else {
                        throw new DependencyResolutionException('The provider defined does not exist: ' . $provider[1]);
                    }
                }

                if(preg_match_all('/@dmRequires\s+([\w\\\\]+)\s+(@\w+)/s', $comments, $tags, PREG_SET_ORDER)) {
                    if (count($tags) < $constructor->getNumberOfRequiredParameters()) {
                        throw new DependencyResolutionException('You must declare all requried dependencies for this class');
                    }

                    $dependencies = array();
                    foreach ($constructor->getParameters() as $parameter) {
                        if (!$parameter->isOptional()) {
                            foreach ($tags as $tag) {
                                if (substr($tag[2], 1) === $parameter->name) {
                                    array_push($dependencies, self::get($tag[1]));
                                }
                            }
                        }
                    }

                    return $reflectionClass->newInstanceArgs($dependencies);
                }
            }

            return $reflectionClass->newInstance();
        }

        return false;
    }

    static protected function hasProvider($name)
    {
        $classSearchList = array(
            preg_replace('/^' . __PROJECT_NAMESPACE__ . '/', __PROJECT_NAMESPACE__ . '\\Providers', $name) . 'Provider',
            preg_replace('/^SPF/', 'SPF\\Dependency\\Providers', $name) . 'Provider'
        );

        foreach ($classSearchList as $className) {
            if (class_exists($className)) {
                $class = new $className();
                return $class->load();
            }
        }

        return false;
    }

}