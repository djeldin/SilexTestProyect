<?php
require_once __DIR__.'/../vendor/autoload.php';
//Symfony\Component\HttpFoundation\Request::enableHttpMethodParameterOverride();
$app = require __DIR__.'/../TS/app.php';

$app->run();