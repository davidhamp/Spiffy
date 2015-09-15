<?php

namespace SPF\Annotations;

use SPF\Annotations\AnnotationSet;
use SPF\Exceptions\AnnotationEngineException;
use SPF\Core\ReflectionPool;
use \reflectionClass;

/**
 * Static because this gets used by the Dependency Manager
 */
class Engine {

    // This class should not only be responsible for creating the reflection class instances, but it should also cache
    // results of parsed annotations so that multiples of the same object don't have to be re-parsed.
    static protected $annotationCache = array();

    /**
     * Kind of like a namespace for annotations
     *
     * @var string
     *
     * @todo  Make this configurable
     */
    static protected $annotationTagspace = 'SPF';

    static public function get($subject, $type = 'constructor', $element = null)
    {
        $className = (is_string($subject) && class_exists($subject))
            ? $subject
            : (is_object($subject) ? get_class($subject) : false);

        /**
         * Error handling
         */
        if (!$className) {
            throw new AnnotationEngineException("$subject provided to getAnnotations wasn't a valid class name or class instance");
        }
        if (!in_array($type, array('constructor', 'method', 'property'))) {
            throw new AnnotationEngineException('$type parameter should be "constructor", "method", or "property".  ' . $type . ' given');
        }
        if (($type === 'method' || $type === 'property') && !$element) {
            throw new AnnotationEngineException('If type is "property" or "method", an $element parameter must be provided');
        }

        /**
         * Cache key
         *
         * @var string
         */
        $cacheKey = $className . '::' . $type . ($type !== 'constructor' ? '::' . $element : null);

        if (!array_key_exists($cacheKey, self::$annotationCache)) {
            $reflectionElement = null;
            try {
                switch ($type) {
                    case 'constructor':
                        $reflectionElement = ReflectionPool::get($className)->getConstructor();
                        break;
                    case 'method':
                        $reflectionElement = ReflectionPool::get($className)->getMethod($element);
                        break;
                    case 'property':
                        $reflectionElement = ReflectionPool::get($className)->getProperty($element);
                        break;
                }
            } catch (\ReflectionException  $e) {
                return new AnnotationSet();
                // throw new AnnotationEngineException("Attempting to inspect non-existent object member: " . $type . " - " . $element);
            }

            $docblock = $reflectionElement ? $reflectionElement->getDocComment() : '';

            preg_match_all('/@' . self::$annotationTagspace .'\\\\(.+)$/m', $docblock, $annotations, PREG_PATTERN_ORDER);
            if (count($annotations) > 1) {
                self::$annotationCache[$cacheKey] = $annotations[1] ? new AnnotationSet($annotations[1], $reflectionElement) : new AnnotationSet();
            }
        }

        return self::$annotationCache[$cacheKey];
    }

}