<?php

namespace FakePay;

use FakePay\Adapter\AdapterInterface;

class AdapterFactory
{
    /**
     * @var \Silex\Application
     */
    protected $container;

    /**
     * @var array
     */
    protected $adapters;

    /**
     * @param \Silex\Application $container
     */
    function __construct(\Silex\Application $container)
    {
        $this->container = $container;
        $this->loadAdapters();
    }

    /**
     * Fetch/create an instance of the requested payment adapter
     *
     * @param $name
     * @throws \RuntimeException
     * @return AdapterInterface
     */
    public function create($name)
    {
        if (!array_key_exists($name, $this->adapters)) {
            throw new \RuntimeException("Adapter `$name` does not exist.");
        }

        if ($this->adapters[$name] instanceof AdapterInterface) {
            return $this->adapters[$name];
        }

        return $this->adapters[$name] = new $this->container[$this->adapters[$name]](
            $this->container['form.factory'],
            $this->container['request'],
            $this->container['fakepay']['adapter'][$name],
            $this->container['sandbox']
        );
    }

    /**
     * Get the service ids of all payment adapters in the application
     *
     * @return array
     */
    public function getAllNames()
    {
        return array_keys($this->adapters);
    }

    /**
     * Ensure all adapters are (lazily) loaded. They will only be instantiated when asked for
     */
    private function loadAdapters()
    {
        $pattern = '/^fakepay\.adapter\.([a-z_-]+)\.class$/';
        foreach ($this->container->keys() as $id) {
            if (preg_match($pattern, $id, $matches)) {
                list($classKey, $name) = $matches;
                $this->adapters[$name] = $classKey;
            }
        }
    }
}