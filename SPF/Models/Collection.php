<?php

namespace SPF\Models;

use SPF\Model;
use \ArrayIterator;

class Collection extends Model
{
    protected $collection = array();

    public function __construct($data, $collectionClass = null)
    {
        foreach ($data as $key => $value) {
            if ($collectionClass && class_exists($collectionClass)) {
                $this->collection[$key] = new $collectionClass($value);
            } else {
                $this->collection[$key] = $value;
            }
        }
    }

    public function getIterator() {
        return new ArrayIterator($this->collection);
    }

    public function jsonSerialize()
    {
        return $this->collection;
    }
}