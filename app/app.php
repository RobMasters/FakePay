<?php

/** @var $app \Silex\Application */
use Symfony\Component\HttpFoundation\Session\Session;

$app = require_once __DIR__.'/bootstrap.php';

$adapterConverter = function($name) use ($app) {
    $app['monolog']->debug('Creating adapter for: ' . $name);

    return $app['fakepay.adapter_factory']->create($name);
};

$app->before(function ($request) {
	/** @var Session $session */
	$session = $request->getSession();
	$session->start();
});

$app->get('/info', function() {
	return phpinfo();
});

$app->get('/sandbox', 'sandbox.controller:indexAction')->bind('sandbox');

$app->get('/sandbox/{adapter}/form', 'sandbox.controller:formAction')
    ->convert('adapter', $adapterConverter)
    ->bind('sandbox_form')
;

$app->match('/sandbox/{adapter}/request', 'payment.controller:displayAction')
    ->method('GET|POST')
    ->convert('adapter', $adapterConverter)
	->before(function() use ($app) {
		$app['sandbox'] = true;
	})
    ->bind('sandbox_request')
;

$app->match('/sandbox/{adapter}/process', 'payment.controller:processAction')
    ->method('GET|POST')
    ->convert('adapter', $adapterConverter)
	->before(function() use ($app) {
		$app['sandbox'] = true;
	})
    ->bind('sandbox_process')
;

$app->match('/sandbox/{adapter}/response', 'sandbox.controller:responseAction')
    ->method('GET|POST')
    ->convert('adapter', $adapterConverter)
	->before(function() use ($app) {
		$app['sandbox'] = true;
	})
    ->bind('sandbox_response')
;

$app->match('/{adapter}', 'payment.controller:displayAction')
	->method('GET|POST')
	->convert('adapter', $adapterConverter)
    ->bind('request')
;

$app->post('/{adapter}/response', 'payment.controller:processAction')
	->convert('adapter', $adapterConverter)
    ->bind('process')
;

$app->error(function(\Exception $e) use ($app) {
	$app['monolog']->addError($e->getMessage());
}, \Silex\Application::EARLY_EVENT);

return $app;