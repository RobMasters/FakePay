<?php

namespace Fakepay\Controller;

use Fakepay\AdapterFactory;

class PaymentController extends BaseController
{
    /**
     * @var AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @param \Fakepay\AdapterFactory $adapterFactory
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
     * @return \Fakepay\Adapter\AdapterInterface
     */
    protected function getAdapter($name)
    {
        return $this->adapterFactory->create($name);
    }
}