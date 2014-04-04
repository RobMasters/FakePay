<?php

namespace FakePay;

use FakePay\Adapter\AdapterInterface;
use FakePay\Adapter\XmlResponseInterface;
use Psr\Log\LoggerInterface;
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
	 * @var bool
	 */
	protected $sandbox;

    /**
     * @var \Twig_Environment
     */
    protected $templating;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

    /**
     * @param Request $request
     * @param FormFactory $formFactory
     * @param array $config
     * @param bool $sandbox
     * @param \Twig_Environment $templating
     * @param \Psr\Log\LoggerInterface $logger
     */
    function __construct(Request $request, FormFactory $formFactory, array $config, $sandbox, \Twig_Environment $templating, LoggerInterface $logger)
    {
        $this->request = $request;
		$this->formFactory = $formFactory;
		$this->config = $config;
		$this->sandbox = $sandbox;
		$this->templating = $templating;
		$this->logger = $logger;

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
        $adapter = $this->adapters[$name] = new $class(
            $this->formFactory,
            $this->request,
			$this->sandbox,
            $this->config[$name],
			$this->logger
        );

        if ($adapter instanceof XmlResponseInterface) {
            $adapter->setTemplating($this->templating);
        }

        return $adapter;
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