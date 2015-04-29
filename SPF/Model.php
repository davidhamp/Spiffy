<?php

namespace SPF;

use \JsonSerializable;
use \IteratorAggregate;
use \ArrayIterator;
use \Traversable;

class Model implements JsonSerializable, IteratorAggregate  {

    protected $strict = true;

    public function __construct($data = array())
    {
        $this->loadData($data);
    }

    public function loadData($data)
    {
        if (is_array($data) || $data instanceof Traversable) {
            foreach ($data as $key => $value) {
                if ($this->strict) {
                    $method = 'set' . ucfirst($key);
                    if (method_exists($this, $method)) {
                        $this->{$method}($value);
                        continue;
                    }

                    if (property_exists($this, $key)) {
                        $this->{$key} = $value;
                    }
                } else {
                    $this->{$key} = $value;
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
        return $this->getIterator();
    }

    public function getIterator() {
        return new ArrayIterator($this);
    }

}