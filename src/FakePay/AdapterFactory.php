<?php

namespace FakePay;

use FakePay\Adapter\AdapterInterface;

class AdapterFactory
{
    const KEY_PATTERN = 'fakepay.adapter.%s';

    /**
     * @var \Silex\Application
     */
    protected $container;

    function __construct(\Silex\Application $container)
    {
        $this->container = $container;
    }

    /**
     * @param $name
     * @throws \RuntimeException
     * @return AdapterInterface
     */
    public function create($name)
    {
        $key = sprintf(self::KEY_PATTERN, $name);
        if (!isset($this->container[$key])) {
            throw new \RuntimeException("Adapter `$name` does not exist. It must be added to the application as `$key`");
        }

        return $this->container[$key];
    }
}