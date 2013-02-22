<?php

namespace FakePay\Adapter;

use Symfony\Component\HttpFoundation\Request;
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
	 * @var array
	 */
	protected $config;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @param \Symfony\Component\Form\FormFactory $formFactory
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param array $config
	 */
    function __construct(FormFactory $formFactory, Request $request, $config = array())
    {
        $this->formFactory = $formFactory;
		$this->request = $request;
		$this->config = $config;

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
     * @return Form
     */
    public function buildForm()
    {
        return $this->formFactory->createBuilder()
            ->add('name')
            ->add('card_type', 'choice', [
                'choices' => ['visa' => 'Visa', 'mastercard' => 'Mastercard']
            ])
            ->add('card_number')
            ->add('security_code')
            ->add('expiry_date', 'date')
            ->getForm()
        ;
    }
}