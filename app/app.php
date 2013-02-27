<?php

/** @var $app \Silex\Application */
$app = require_once __DIR__.'/bootstrap.php';

$adapterConverter = function($name) use ($app) {
    return $app['fakepay.adapter_factory']->create($name);
};


$app->get('/info', function() {
	return phpinfo();
});

$app->get('/sandbox', 'sandbox.controller:indexAction')->bind('sandbox');

$app->match('/sandbox/{adapter}/request', 'payment.controller:displayAction')
    ->method('GET|POST')
    ->convert('adapter', $adapterConverter)
    ->bind('sandbox_request')
    ->before(function() use ($app) {
        $app['sandbox'] = true;
    })
;

$app->match('/sandbox/{adapter}/response', 'sandbox.controller:responseAction')
    ->method('GET|POST')
    ->convert('adapter', $adapterConverter)
    ->bind('sandbox_response')
;

$app->match('/{adapter}', 'payment.controller:displayAction')
	->method('GET|POST')
	->convert('adapter', $adapterConverter)
    ->bind('request')
;

$app->post('/{adapter}/response', 'payment.controller:processAction')
	->convert('adapter', $adapterConverter)
    ->bind('response')
;

return $app;