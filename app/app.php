<?php

$app = require_once __DIR__.'/bootstrap.php';

$app->get('/info', function() {
	return phpinfo();
});

$app->get('/{adapter}', "payment.controller:displayAction");
$app->post('/{adapter}', "payment.controller:displayAction");
//$app->post('/form/{handler}', "payment.controller:formAction");

return $app;