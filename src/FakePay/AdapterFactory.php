<?php

namespace FakePay;

use FakePay\Adapter\AdapterInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

class AdapterFactory
{
    /**
     * @var \Silex\Application
     */
    protected $container;

    /**
     * @var array|AdapterInterface[]
     */
    protected $adapters;

	/**
	 * @param Request $request
	 * @param FormFactory $formFactory
	 * @param array $config
	 */
    function __construct(Request $request, FormFactory $formFactory, array $config)
    {
        $this->request = $request;
		$this->formFactory = $formFactory;
		$this->config = $config;

		$this->adapters = array();
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
        if (array_key_exists($name, $this->adapters) && $this->adapters[$name] instanceof AdapterInterface) {
            return $this->adapters[$name];
        }

		if (!array_key_exists($name, $this->config)) {
			throw new \RuntimeException("Adapter `$name` does not exist.");
		}

		if (!array_key_exists('class', $this->config[$name])) {
			throw new \RuntimeException("Adapter `$name` has no class configured.");
		}

		$class = $this->config[$name]['class'];

        return $this->adapters[$name] = new $class(
            $this->formFactory,
            $this->request,
            $this->config[$name]
        );
    }

    /**
     * Get the service ids of all payment adapters in the application
     *
     * @return array
     */
    public function getAllNames()
    {
        return array_keys($this->config);
    }
}