<?php

namespace SPF;

require_once('SPF/Autoloader.php');

$appOptions = array();

if (is_array($options)) {

    if (array_key_exists('namespace', $options)) {
       foreach ($options['namespace'] as $key => $val) {
            Autoloader::addNamespace($key, $val);
        }
    }

    if (array_key_exists('application', $options)) {
        $appOptions = $options['application'];
    }

}

$application = new Application($appOptions);