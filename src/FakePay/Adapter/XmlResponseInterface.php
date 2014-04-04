<?php

namespace FakePay\Adapter;

interface XmlResponseInterface
{
    /**
     * @param \Twig_Environment $templating
     * @return mixed
     */
    public function setTemplating(\Twig_Environment $templating);
}