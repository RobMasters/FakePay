<?php

namespace FakePay\Adapter;

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
		$responseUrl = $this->getResponseUrl();
		$responseCode = $this->request->request->get('custom_status', '00');

		$this->logger->debug("Posting response code `$responseCode` to: $responseUrl");

		$params = $this->getFlashBag()->get('params');

		// Post response to client
		$ch = curl_init($responseUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			'ORDER_ID' => $params['ORDER_ID'],
			'RESULT' => $responseCode,
			'SAVED_PAYER_REF' => $params['PAYER_REF'],
			'SAVED_PMT_REF' => $params['PMT_REF'],
			'MERCHANT_ID' => $params['MERCHANT_ID'],
			'AMOUNT' => $params['AMOUNT'],
			'TIMESTAMP' => $params['TIMESTAMP'],
			'MD5HASH' => '',
			'REALWALLET_CHOSEN' => array_key_exists('PAYER_REF', $params),
			'PMT_SETUP' => '00',
			'PMT_SETUP_MSG' => 'Successful',
			'SAVED_PMT_TYPE' => 'VISA',
			'SAVED_PMT_DIGITS' => '426397xxxx1307',
			'SAVED_PMT_EXPDATE' => '0214',
			'SAVED_PMT_NAME' => 'Mr P Yi',
			'ACCOUNT' => 'internet',
			'AUTHCODE' => 12345,
			'MESSAGE' => '[ test system ] Authorised',
			'PASREF' => 1364910737394424,
			'AVSPOSTCODERESULT' => 'U',
			'AVSADDRESSRESULT' => 'U',
			'CVNRESULT' => 'U',
			'BATCHID' => 111891
		));

		$data = curl_exec($ch);

		return new Response($data);
	}

	/**
	 *
	 */
	private function savePersistentParams()
	{
		$params = array(
			'MERCHANT_ID',
			'ORDER_ID',
			'AMOUNT',
			'TIMESTAMP'
		);

		// RealVault
		if ($this->request->request->get('OFFER_SAVE_CARD')) {
			$params = array_merge($params, array(
				'PAYER_REF',
				'PMT_REF'
			));
		}

		$flashBag = $this->getFlashBag();
		$param_values = array();
		foreach ($params as $param) {
			$param_values[$param] = $this->request->request->get($param);
		}

		$flashBag->set('params', $param_values);
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface
	 */
	private function getFlashBag()
	{
		return $this->request->getSession()->getFlashBag();
	}
}