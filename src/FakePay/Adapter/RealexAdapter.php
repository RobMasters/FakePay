<?php

namespace FakePay\Adapter;

use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Form;

class RealexAdapter extends BaseAdapter
{
	/**
	 * @return mixed
	 */
	protected function configure()
	{
		$this
			->setName('realex')
		;
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @return bool|mixed
	 */
	public function validateRequest()
    {
        $out = true;

        $required = array(
            'MERCHANT_ID',
            'ORDER_ID',
            'AMOUNT',
            'CURRENCY',
            'TIMESTAMP',
            'AUTO_SETTLE_FLAG'
        );

		// Realvault
		if ($this->request->request->has('OFFER_SAVE_CARD')) {
			$required = array_merge($required, array(
				'PAYER_REF',
				'PMT_REF',
				'PAYER_EXIST'
			));
		}

        foreach ($required as $value) {
            if (null === $this->request->request->get($value)) {
				$this->addFlashMessage(sprintf('`%s` must be provided', $value));
                $out = false;
            }
        }

		if ($out) {
			$this->savePersistentParams();
		}

        return $out;
    }

	/**
	 * @return mixed
	 */
	public function process()
	{
		// Check data

		$responseUrl = $this->getResponseUrl();
		$responseCode = $this->request->request->get('custom_status', '00');

		$this->logger->debug("Posting response code `$responseCode` to: $responseUrl");

		// Post response to client
        $client = new Client($responseUrl);
        $request = $client->post(null, null, array(
            'ORDER_ID' => current($this->getFlashBag()->get('ORDER_ID')),
            'RESULT' => $responseCode, // TODO other status codes...
            'SAVED_PAYER_REF' => current($this->getFlashBag()->get('PAYER_REF')),
            'SAVED_PMT_REF' => current($this->getFlashBag()->get('PMT_REF')),

            // TODO - post back everything else in the spec...
        ));
        $response = $request->send();

        return new Response($response->getBody(true));
	}

	/**
	 *
	 */
	private function savePersistentParams()
	{
		$params = array(
			'ORDER_ID'
		);

		// RealVault
		if ($this->request->request->get('OFFER_SAVE_CARD')) {
			$params = array_merge($params, array(
				'PAYER_REF',
				'PMT_REF'
			));
		}

		$flashBag = $this->getFlashBag();;
		foreach ($params as $param) {
			$flashBag->set($param, $this->request->request->get($param));
		}
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface
	 */
	private function getFlashBag()
	{
		return $this->request->getSession()->getFlashBag();
	}
}