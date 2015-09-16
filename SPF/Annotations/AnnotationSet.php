<?php

namespace SPF\Annotations;

/**
 * @todo Update to use Iterator for traversing over annotations
 */
class AnnotationSet {

    protected $annotations = array();

    public $reflection;

    public function __construct($annotations = array(), $reflection = null) {
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

    public function has($annotation) {
        return array_key_exists($annotation, $this->annotations);
    }

    public function get($annotation = null) {
        if (is_null($annotation)) {
            return $this->annotations;
        }

        return array_key_exists($annotation, $this->annotations) ? $this->annotations[$annotation] : array();
    }
}