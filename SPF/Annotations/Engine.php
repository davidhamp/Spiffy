<?php
/**
 * SPF/Annotations/Engine.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Annotations;

use SPF\Annotations\AnnotationSet;
use SPF\Exceptions\AnnotationEngineException;
use SPF\Core\ReflectionPool;
use \reflectionClass;

/**
 * Annotation processing engine.
 * 
 * Uses PHP Reflection to inspect given class properties and methods and returns a set of Annotations as 
 * {@link SPF\Annotations\AnnotationSet}.
 * 
 * Annotations are tags placed within a property or method's docComment block.  These take the form of
 * @SPF:AnnotationName.  Annotations are "namespaced" to @SPF: to avoid conflict with other annotation implementations
 * such as PHPDocs or IDE tagging.
 * 
 * The annotation can also have one or more parameters separated by spaces.  When returned in an Annotation set, these 
 * parameters are indexes in the array you get back when requesting annotations.
 * 
 * @see SPF\Annotations\AnnotationSet
 * @uses reflectionClass
 */
class Engine
{

    /**
     * Parse annotations are stored here, so parsing annotations on a collection of the same object type won't
     * result in unecessary work.
     * 
     * @var array
     */
    static protected $annotationCache = array();

    /**
     * Kind of like a namespace for annotations
     *
     * @var string
     *
     * @todo  Make this configurable
     */
    static protected $annotationTagspace = 'SPF';

    /**
     * Gets all SPF Annotations given a subject
     * 
     * Subject can be either a fully qualified class name string, or an instance of the object in question.
     * This will then parse the docblock associated with the given element within the class reflection and
     * look for @SPF: annotations (from the {@link SPF\Annotations\Engine:$annotationTagSpace}).  
     * This then returns an AnnotationSet containing all found annotations (or an empty AnnotationSet if none
     * are found).
     * 
     * @param string|object $subject                  Class name string or object instance to inspect
     * @param 'constructor'|'method'|'property' $type String value that should only be 'constructor', 'method', or 'property', and an Exception will 
     *                                                be thrown if the value is anything but those three.  
     *                                                Determines what type of elementon the ojbect you wish to inspect for annotations.  
     *                                                Defaults to 'constructor'.
     * @param string        $element                  Element name of property or method.  Unecessary if $type is 'constructor', but will 
     *                                                throw an Exception if omitted when $type is 'method' or 'property'
     * 
     * @throws SPF\Exceptions\AnnotationEngineException
     * 
     * @return SPF\Annotations\AnnotationSet
     */
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
            }

            $docblock = $reflectionElement ? $reflectionElement->getDocComment() : '';

            preg_match_all('/@' . self::$annotationTagspace .':(.+)$/m', $docblock, $annotations, PREG_PATTERN_ORDER);

            if (count($annotations) > 1) {
                self::$annotationCache[$cacheKey] = $annotations[1] ? new AnnotationSet($annotations[1], $reflectionElement) : new AnnotationSet();
            }
        }

        return self::$annotationCache[$cacheKey];
    }

}
