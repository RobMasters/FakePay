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
//				'PMT_REF',
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

        if ($this->request->request->has('server_error')) {
            return new Response('<h1>Server error</h1><p>No response is sent to the response URL</p>');
        }

		$this->logger->debug("Posting response code `$responseCode` to: $responseUrl");

		$params = $this->getFlashBag()->get('params');

		// Post response to client
		$client = new Client($responseUrl);
		$postVars = array(
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
			'PASREF' => '1364910737394424',
			'AVSPOSTCODERESULT' => 'U',
			'AVSADDRESSRESULT' => 'U',
			'CVNRESULT' => 'U',
			'BATCHID' => 111891
		);

        // Pass back any additional info that was sent
        $extra = $this->getFlashBag()->get('extra');
        foreach ($extra as $key => $value) {
            $postVars[$key] = $value;
        }

		$hashString = sprintf('%s.%s.%s.%s.%s.%s.%s',
			$params['TIMESTAMP'],
			$this->config['merchant_id'],
			$params['ORDER_ID'],
			$responseCode,
			'[ test system ] Authorised',
			'1364910737394424',
			12345
		);

		if ($this->request->getSession()->get('hash_type') === 'sha1') {
			$postVars['SHA1HASH'] = sha1(sprintf('%s.%s',
				sha1($hashString),
				$this->config['secret']
			));
		} else {
			$postVars['MD5HASH'] = md5(sprintf('%s.%s',
				md5($hashString),
				$this->config['secret']
			));
		}

		$request = $client->post(null, null, $postVars);

		$response = $request->send();

		return new Response($response->getBody(true));
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

        $realexFields = array(
            'MERCHANT_ID',
            'ACCOUNT',
            'ORDER_ID',
            'AMOUNT',
            'CURRENCY',
            'TIMESTAMP',
            'MD5HASH',
            'SHA1HASH',
            'AUTO_SETTLE_FLAG',
            'COMMENT1',
            'COMMENT2',
            'RETURN_TSS',
            'SHIPPING_CODE',
            'SHIPPING_CO',
            'BILLING_CODE',
            'BILLING_CO',
            'CUST_NUM',
            'VAR_REF',
            'PROD_ID'
        );

        $requestValues = $this->request->request->all();

		$flashBag = $this->getFlashBag();
		$paramValues = array();
		foreach ($params as $param) {
			$paramValues[$param] = $requestValues[$param];
		}

		// RealVault
		if ($this->request->request->get('OFFER_SAVE_CARD')) {
			$paramValues['PAYER_REF'] = $this->request->request->get('PAYER_REF') ?: $this->generateRandomString();
			$paramValues['PMT_REF'] = $this->request->request->get('PMT_REF') ?: $this->generateRandomString();
		}

		$flashBag->set('params', $paramValues);
		$this->request->getSession()->set('hash_type', ($this->request->request->has('SHA1HASH')) ? 'sha1' : 'md5');

        $extra = array();
        foreach ($requestValues as $key => $requestValue) {
            if (!in_array($key, $realexFields)) {
                $extra[$key] = $requestValue;
            }
        }
        $flashBag->set('extra', $extra);
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface
	 */
	private function getFlashBag()
	{
		return $this->request->getSession()->getFlashBag();
	}

	/**
	 * @param int $length
	 * @return string
	 */
	private function generateRandomString($length = 10)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}

		return $randomString;
	}
}