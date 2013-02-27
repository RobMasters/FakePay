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

        $required = [
            'MERCHANT_ID',
            'ORDER_ID',
            'AMOUNT',
            'CURRENCY',
            'TIMESTAMP',
            'AUTO_SETTLE_FLAG'
        ];

		// Realvault
		if ($this->request->request->has('OFFER_SAVE_CARD')) {
			$required = array_merge($required, [
				'PAYER_REF',
				'PMT_REF',
				'PAYER_EXIST'
			]);
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
     * @return Form
     */
    public function buildForm()
    {
        return $this->formFactory->createBuilder()
            ->add('name')
            ->add('card_type', 'choice', [
                'choices' => ['visa' => 'Visa', 'mastercard' => 'Mastercard']
            ])
            ->add('card_number')
            ->add('security_code')
            ->add('expiry_date', 'date')
            ->getForm()
        ;
    }

	/**
	 * @return mixed
	 */
	public function process()
	{
		// Check data
		// TODO

		// Post response to client
		$ch = curl_init($this->getResponseUrl());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			'ORDER_ID' => $this->getFlashBag()->get('ORDER_ID')[0],
			'RESULT' => '00', // TODO other status codes...
			'SAVED_PAYER_REF' => $this->getFlashBag()->get('PAYER_REF')[0],
			'SAVED_PMT_REF' => $this->getFlashBag()->get('PMT_REF')[0],

			// TODO - post back everything else in the spec...
		));

		$data = curl_exec($ch);

		return new Response($data);
	}

	/**
	 *
	 */
	private function savePersistentParams()
	{
		$params = [
			'ORDER_ID'
		];

		// RealVault
		if ($this->request->request->get('OFFER_SAVE_CARD')) {
			$params = array_merge($params, [
				'PAYER_REF',
				'PMT_REF'
			]);
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