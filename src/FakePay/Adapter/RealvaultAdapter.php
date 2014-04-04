<?php

namespace FakePay\Adapter;

use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Form;

class RealvaultAdapter extends RealexAdapter implements XmlResponseInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $templating;

	/**
	 * @return mixed
	 */
	protected function configure()
	{
		$this
			->setName('realvault')
		;
	}

    /**
     * @return mixed
     */
    public function process()
    {
        $xml = new \SimpleXMLElement($this->request->getContent());
        $postVars = (array) $xml;
        $timestamp = time();
        $pasref = '13837447728538395';

        $hash = sprintf('%s.%s.%s.%s.%s.%s.%s',
            $timestamp,
            $postVars['merchantid'],
            $postVars['orderid'],
            '00',
            '[ test system ] Authorised',
            $pasref,
            '12345'
        );

        $response = new Response($this->templating->render('Adapter/realvault_response.html.twig', array(
            'timestamp' => $timestamp,
            'merchantid' => $postVars['merchantid'],
            'account' => $postVars['account'],
            'orderid' => $postVars['orderid'],
            'result' => '00',
            'message' => '[ test system ] Authorised',
            'pasref' => $pasref,
            'authcode' => '12345',
            'batchid' => '987654',
            'timetaken' => '0',
            'sha1hash' => $this->getSha1Hash($hash)
        )));
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @param $timestamp
     * @param $merchantId
     * @param $orderId
     * @param $amount
     * @param $currency
     * @param $payerRef
     * @return string
     */
    protected function getHashString($timestamp, $merchantId, $orderId, $amount, $currency, $payerRef)
    {
        return sprintf('%s.%s.%s.%s.%s.%s.%s',
            $timestamp,
            $merchantId,
            $orderId,
            $amount,
            $currency,
            $payerRef
        );
    }

    /**
     * @param \Twig_Environment $templating
     * @return mixed
     */
    public function setTemplating(\Twig_Environment $templating)
    {
        $this->templating = $templating;
    }
}