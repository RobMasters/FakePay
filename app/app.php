<?php

$app = require_once __DIR__.'/bootstrap.php';

$app->get('/info', function() {
	return phpinfo();
});

$adapterConverter = function($name) use ($app) {
	return $app['fakepay.adapter_factory']->create($name);
};

$app->match('/{adapter}', "payment.controller:displayAction")
	->method('GET|POST')
	->convert('adapter', $adapterConverter)
;

$app->post('/process/{adapter}', "payment.controller:processAction")
	->convert('adapter', $adapterConverter)
;

return $app;