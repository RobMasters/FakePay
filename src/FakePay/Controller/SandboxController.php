<?php

namespace FakePay\Controller;

use Symfony\Component\HttpFoundation\Response;

class SandboxController extends BaseController
{
	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
    public function indexAction()
    {
        $ids = $this->app['fakepay.adapter_factory']->getAllNames();

        return $this->getTemplating()->render("Sandbox/index.html.twig", [
            'ids' => $ids
        ]);
    }
}