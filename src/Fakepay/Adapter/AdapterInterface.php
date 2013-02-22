<?php

namespace FakePay\Adapter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;

interface AdapterInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return mixed
     */
    public function validateRequest(Request $request);

    /**
     * @return Form
     */
    public function buildForm();
}