<?php

namespace FakePay\Adapter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;

interface AdapterInterface
{
    /**
     * @return bool
     */
    public function validateRequest();

    /**
     * @return Form
     */
    public function buildForm();

	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return mixed
	 */
	public function process();
}