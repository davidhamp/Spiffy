<?php

namespace SPF\Core;

use SPF\Exceptions\ReflectionPoolException;
use \reflectionClass;

class ReflectionPool
{
    static protected $pool = array();

    static public function get($className)
    {
        if (!array_key_exists($className, self::$pool)) {
            if (class_exists($className)) {
                self::$pool[$className] = new reflectionClass($className);
            } else {
                throw new ReflectionPoolException("Requested className " . $classname . " doesn't exist");
            }
        }

        return self::$pool[$className];
    }
}