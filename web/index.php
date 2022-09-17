<?php

//defined('YII_DEBUG') or define('YII_DEBUG', true);
//defined('YII_ENV') or define('YII_ENV', 'dev');

require realpath(dirname(__DIR__) . '/vendor/autoload.php');
require realpath(dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php');

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$config = require realpath(dirname(__DIR__) . '/config/main.php');
(new yii\web\Application($config))->run();