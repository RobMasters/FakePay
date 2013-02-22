<?php

namespace FakePay\Controller;

use FakePay\AdapterFactory;

class PaymentController extends BaseController
{
    /**
     * @var AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @param \FakePay\AdapterFactory $adapterFactory
     */
    public function setAdapterFactory(AdapterFactory $adapterFactory)
    {
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * @param $adapter
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayAction($adapter = '')
    {
        $name = $adapter;
        $adapter = $this->getAdapter($adapter);

        $errors = [];
        if (!$adapter->validateRequest($this->request)) {
            $errors = $this->request->getSession()->getFlashBag()->peek("{$name}_error");
        }

        return $this->templating->render('pay.html.twig', [
            'form' => $adapter->buildForm()->createView(),
            'errors' => $errors
        ]);
    }

    /**
     * @param $name
     * @return \FakePay\Adapter\AdapterInterface
     */
    protected function getAdapter($name)
    {
        return $this->adapterFactory->create($name);
    }
}