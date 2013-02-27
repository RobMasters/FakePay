<?php

use Symfony\Component\HttpFoundation\Request;
use Silex\Provider\FormServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use FakePay\Controller\PaymentController;
use FakePay\Controller\SandboxController;

$app = new Silex\Application();

$app['debug'] = true;
$app['sandbox'] = false; // Leave this alone - it will be set as true automatically when necessary


/**
 * APPLICATION CONFIGURATION
 *
 * (is added to $app as normal, but provides a way of isolating the configuration)
 */

$replacements = parse_ini_file(__DIR__.'/config/parameters.ini');

$env = getenv('APP_ENV') ?: 'prod';
$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__."/config/config.yml"));
$envConfig = __DIR__."/config/config_$env.yml";
if (file_exists($envConfig)) {
	$app->register(new Igorw\Silex\ConfigServiceProvider($envConfig));
}


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

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());


/**
 * APPLICATION SERVICES
 */

$app['payment.controller'] = $app->share(function() use ($app) {
    return new PaymentController($app);
});

$app['sandbox.controller'] = $app->share(function() use ($app) {
    return new SandboxController($app);
});

$app['fakepay.adapter_factory'] = $app->share(function() use ($app) {
    return new \FakePay\AdapterFactory($app);
});


/**
 * ADAPTERS
 * note: must match /^fakepay\.adapter\.[a-z_-]+$/
 */

//$app['fakepay.adapter.realex'] = function() use ($app) {
//    return new \FakePay\Adapter\RealexAdapter(
//		$app['form.factory'],
//		$app['request'],
//		$app['fakepay']['adapter']['realex'],
//        $app['sandbox']
//	);
//};
$app['fakepay.adapter.realex.class'] = '\FakePay\Adapter\RealexAdapter';




return $app;