<?php

$components = require_once 'components.php';

return [
    'id' => 'caffy-api',
    'basePath' => __DIR__,
    'defaultRoute' => 'core/index',
    'controllerNamespace' => 'Bouledepate\CaffyApi\Controllers',
    'components' => $components
];