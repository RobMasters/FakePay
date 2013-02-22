<?php

namespace FakePay\Controller;

use Symfony\Component\HttpFoundation\Request;

abstract class BaseController
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Twig_Environment
     */
    protected $templating;

    /**
     * @param \Twig_Environment $templating
     */
    function __construct(Request $request, \Twig_Environment $templating)
    {
        $this->request = $request;
        $this->templating = $templating;
    }
}