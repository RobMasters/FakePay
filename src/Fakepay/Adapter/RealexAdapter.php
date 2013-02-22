<?php

namespace Fakepay\Adapter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Form;

class RealexAdapter implements AdapterInterface
{
    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @param \Symfony\Component\Form\FormFactory $formFactory
     */
    function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function validateRequest(Request $request)
    {
        $out = true;

        $required = [
            'MERCHANT_ID',
            'ACCOUNT',
            'ORDER_ID',
            'AMOUNT',
            'CURRENCY',
            'TIMESTAMP',
            'AUTO_SETTLE_FLAG'
        ];

        foreach ($required as $value) {
            // TODO use $request->request when only accepting POST
            if (null === $request->get($value)) {
                $request->getSession()->getFlashBag()->set('realex_error', sprintf('`%s` must be provided', $value));
                $out = false;
            }
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
}