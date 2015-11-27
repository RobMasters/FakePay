<?php

namespace FakePay\Controller;

use FakePay\Adapter\RealvaultAdapter;
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
        $errors = array();

//        $this->getLogger()->debug('Session: %s', print_r($_SESSION, true));

        if ($adapter instanceof RealvaultAdapter) {
            return $this->processAction($adapter);
        }

        $adapterName = $adapter->getName();
        $this->getLogger()->debug('In display action for adapter: ' . $adapterName);

        if (!$adapter->validateRequest($this->getRequest())) {
            $errors = $this->getRequest()->getSession()->getFlashBag()->get("{$adapterName}_error");
        }

        return new Response($this->getTemplating()->render("Adapter/{$adapterName}.html.twig", array(
			'adapter' => $adapter,
            'form' => $adapter->buildForm()->createView(),
            'errors' => $errors
        )));
    }

	/**
	 * @param \FakePay\Adapter\AdapterInterface $adapter
     * @return \Symfony\Component\HttpFoundation\Response
     */
	public function processAction(AdapterInterface $adapter)
	{
        $adapterName = $adapter->getName();
        $this->getLogger()->debug('In process action for adapter: ' . $adapterName);

		try {
			$this->getLogger()->addDebug("Processing payment using adapter: " . $adapterName);
			$response = $adapter->process();
		} catch (\Exception $e) {
			return new Response($e->getMessage(), 400);
		}

		return $response ?: new Response($this->getTemplating()->render("base_response.html.twig"));
	}
}