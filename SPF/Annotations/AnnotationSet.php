<?php
/**
 * SPF/Annotations/AnnotationSet.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Annotations;

/**
 * Annotation container always returned by {@link SPF\Annotations\Engine::get()}
 * 
 * @todo Update to use Iterator for traversing over annotations in the set
 */
class AnnotationSet
{
    /**
     * Collection of annotation information keyed by Annotation name
     * 
     * @var array
     */
    protected $annotations = array();

    /**
     * The annotation set reflects a group of annotations present in a given element of an object
     * 
     * For convenience and for validation against things like parameters of method or visibility we provide
     * the reflection object here.  Note this isn't the full reflectionClass, but either an instance of 
     * ReflectionMethod or ReflectionProperty depending on the type of element requested through
     * {@link SPF\Annotations\Engine::get()}
     * 
     * @var ReflectionMethod|ReflectionProperty
     */
    public $reflection;

    /**
     * Recieves the annotation set as an array and the reflection object
     *
     * @param array                               $annotations Array of matched annotaions
     * @param ReflectionMethod|ReflectionProperty $reflection Reflection element instance stored for reference
     *
     * @return void
     */
    public function __construct($annotations = array(), $reflection = null)
    {
        $this->reflection = $reflection;

        foreach ($annotations as $annotation) {
            $annotationInfo = explode(' ', $annotation);
            $annotationType = array_shift($annotationInfo);

            if (!array_key_exists($annotationType, $this->annotations)) {
                $this->annotations[$annotationType] = array();
            }

            array_push($this->annotations[$annotationType], $annotationInfo);
        }
    }

    /**
     * Helper function to determine existence of Annotations in the set
     * 
     * @return boolean
     */
    public function has($annotation)
    {
        return array_key_exists($annotation, $this->annotations);
    }

    /**
     * Returns all annotations or a subset of annotations based on annotation name
     * 
     * Annotations are grouped by annotation name as an array such as
     * 
     * When inspecting annotation parameters they are indexed in the order they are defined in the annotation.
     * 
     * Example:
     * ```
     * @SPF:AnnotationTest Param1 Param2
     * @SPF:AnnotationTest Param3 Param4
     * ```
     * ```
     * $annotationSet->get('AnnotationTest')
     * ```
     * would result in
     * ```
     * array(
     *     array(Param1, Param2),
     *     array(Param3, Param4)
     * )
     * ```
     * 
     * @param string $annotation Name of annotation to retrieve from the set.  Will return all annotations if set to null
     * 
     * @return array
     */
    public function get($annotation = null)
    {
        if (is_null($annotation)) {
            return $this->annotations;
        }

        return array_key_exists($annotation, $this->annotations) ? $this->annotations[$annotation] : array();
    }
}
