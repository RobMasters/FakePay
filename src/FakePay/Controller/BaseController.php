<?php

namespace FakePay\Controller;

use Symfony\Component\HttpFoundation\Request;

abstract class BaseController
{
    /**
     * @var \Silex\Application
     */
    protected $app;

    /**
     * @param \Silex\Application $app
     */
    function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return \Twig_Environment
     */
    public function getTemplating()
    {
        return $this->app['twig'];
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->app['request'];
    }

	/**
	 * @return \Monolog\Logger
	 */
	public function getLogger()
	{
		return $this->app['monolog'];
	}
}