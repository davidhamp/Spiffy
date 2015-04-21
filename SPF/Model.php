<?php

namespace SPF;

class Model {

    public function __construct($data = array())
    {
        $this->loadData($data);
    }

    public function loadData($data)
    {
        if (is_array($data) || $data instanceof Traversable) {
            foreach ($data as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

}