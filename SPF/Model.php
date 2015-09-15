<?php

namespace SPF;

use SPF\Annotations\Engine as AnnotationEngine;
use \JsonSerializable;
use \IteratorAggregate;
use \ArrayIterator;
use \Traversable;

class Model implements JsonSerializable {

    public function __construct($data = array())
    {
        $this->loadData($data);
    }

    public function loadData($data)
    {
        if (is_array($data) || $data instanceof Traversable) {
            foreach ($data as $key => $value) {
                $method = 'set' . ucfirst($key);
                if (method_exists($this, $method)) {
                    $this->{$method}($value);
                    continue;
                }

                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                    continue;
                }
            }
        }
    }

    public function __toString()
    {
        return json_encode($this);
    }

    public function jsonSerialize()
    {
        $out = array();
        foreach ($this as $key => $value) {
            $annotation = AnnotationEngine::get($this, 'property', $key);
            if ($annotation->has('JsonIgnore')) {
                continue;
            }

            $out[$key] = $value;
        }

        return $out;
    }
}