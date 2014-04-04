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
$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__."/config/config.yml", $replacements));
$envConfig = __DIR__."/config/config_$env.yml";
if (file_exists($envConfig)) {
	$app->register(new Igorw\Silex\ConfigServiceProvider($envConfig));
}


/**
 * SERVICE PROVIDERS
 */

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallback' => 'en',
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/Resources/views',
));

$app->register(new FormServiceProvider());

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->register(new Silex\Provider\MonologServiceProvider(), array(
	'monolog.logfile' => __DIR__.'/logs/development.log',
	'monolog.name' => 'fakepay'
));

/**
 * APPLICATION SERVICES
 */

$app->before(function() use ($app) {
	$app['twig']->addExtension(new FakePay\Twig\FakePayExtension($app));
});

$app['payment.controller'] = $app->share(function() use ($app) {
    return new PaymentController($app);
});

$app['sandbox.controller'] = $app->share(function() use ($app) {
    return new SandboxController($app);
});

$app['fakepay.adapter_factory'] = $app->share(function() use ($app) {
    return new \FakePay\AdapterFactory(
		$app['request'],
		$app['form.factory'],
		$app['fakepay']['adapter'],
		$app['sandbox'],
        $app['twig'],
		$app['monolog']
	);
});



return $app;