<?php

$app = require_once __DIR__.'/bootstrap.php';

$app->get('/{adapter}', "payment.controller:displayAction");
//$app->post('/form/{handler}', "payment.controller:formAction");

$app->get('/info', function() {
    return phpinfo();
});

return $app;