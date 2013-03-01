<?php

namespace FakePay\Adapter;

use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Form;

abstract class BaseAdapter implements AdapterInterface
{
    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected $request;

	/**
	 * @var bool
	 */
	protected $sandbox;

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @param \Symfony\Component\Form\FormFactory $formFactory
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param $sandbox
	 * @param $config
	 * @param \Psr\Log\LoggerInterface $logger
	 */
    function __construct(FormFactory $formFactory, Request $request, $sandbox, $config, LoggerInterface $logger)
    {
        $this->formFactory = $formFactory;
		$this->request = $request;
		$this->sandbox = $sandbox;
		$this->config = $config;
		$this->logger = $logger;

		$this->configure();
    }

	/**
	 * @return mixed
	 */
	abstract protected function configure();

	/**
	 * @param $name
	 * @return \FakePay\Adapter\BaseAdapter
	 */
	protected function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 * @throws \RuntimeException
	 */
	public function getName()
	{
		if (empty($this->name)) {
			throw new \RuntimeException(sprintf("Adapter %s has no name", __CLASS__));
		}

		return $this->name;
	}

	/**
	 * @param $message
	 * @param string $key
	 */
	protected function addFlashMessage($message, $key = '')
	{
		$key = (!empty($key)) ? $key : sprintf('%s_error', $this->getName());
		$this->request->getSession()->getFlashBag()->add($key, $message);
	}

    /**
     * @return string
     */
    protected function getResponseUrl()
    {
        if ($this->sandbox) {
            return $this->getSandboxResponseUrl();
        }

        return $this->config['response_url'];
    }

    /**
     * @return string
     */
    protected function getSandboxResponseUrl()
    {
        return sprintf('http://%s/sandbox/%s/response', trim($this->request->server->get('HTTP_HOST'), '/'), $this->getName());
    }

    /**
     * @return Form
     */
    public function buildForm()
    {
        return $this->formFactory->createBuilder()
            ->add('name')
            ->add('card_type', 'choice', array(
                'choices' => array('visa' => 'Visa', 'mastercard' => 'Mastercard')
            ))
            ->add('card_number')
            ->add('security_code')
            ->add('expiry_date', 'date')
            ->getForm()
        ;
    }
}