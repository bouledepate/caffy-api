<?php

$host = $_ENV['DB_HOST'];
$dbName = $_ENV['DB_NAME'];
$dsn = "pgsql:host=$host;dbname=$dbName";

return [
    'class' => 'yii\db\Connection',
    'dsn' => $dsn,
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD'],
    'charset' => 'utf8',
];