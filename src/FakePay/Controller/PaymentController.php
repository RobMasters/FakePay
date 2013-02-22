<?php

namespace FakePay\Controller;

use FakePay\AdapterFactory;
use Symfony\Component\HttpFoundation\Response;
use FakePay\Adapter\AdapterInterface;

class PaymentController extends BaseController
{
	/**
	 * @param $adapter
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
    public function displayAction(AdapterInterface $adapter)
    {
        $errors = [];
        if (!$adapter->validateRequest($this->request)) {
            $errors = $this->request->getSession()->getFlashBag()->get("{$adapter->getName()}_error");
        }

        return $this->templating->render("Adapter/{$adapter->getName()}.html.twig", [
			'adapter' => $adapter,
            'form' => $adapter->buildForm()->createView(),
            'errors' => $errors
        ]);
    }

	/**
	 * @param \FakePay\Adapter\AdapterInterface $adapter
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function processAction(AdapterInterface $adapter)
	{
		try {
			$response = $adapter->process();
		} catch (\Exception $e) {
			return new Response($e->getMessage(), 400);
		}

		return $response ?: $this->templating->render("base_response.html.twig");
	}
}