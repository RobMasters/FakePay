<?php

use Symfony\Component\HttpFoundation\Request;
use Silex\Provider\FormServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Fakepay\Controller\PaymentController;

$app = new Silex\Application();
$app['debug'] = true;

/**
 * SERVICE PROVIDERS
 */

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), [
    'locale_fallback' => 'en',
]);

$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__.'/Resources/views',
]);

$app->register(new FormServiceProvider());



/**
 * APPLICATION SERVICES
 */

$app['payment.controller'] = $app->share(function() use ($app) {
    $controller = new PaymentController($app['request'], $app['twig']);
    $controller->setAdapterFactory($app['fakepay.adapter_factory']);

    return $controller;
});

$app['fakepay.adapter_factory'] = $app->share(function() use ($app) {
    return new \Fakepay\AdapterFactory($app);
});

$app['fakepay.adapter.beaver'] = function() use ($app) {
    return new \Fakepay\Adapter\RealexAdapter($app['form.factory']);
};

return $app;