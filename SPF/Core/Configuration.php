<?php

namespace SPF\Core;

use Symfony\Component\Yaml\Yaml;

class Configuration
{
    protected $config;

    public function __construct()
    {
        $this->config = Yaml::parse(__BASE__ . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'config.yaml');
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        } else {
            return null;
        }
    }
}