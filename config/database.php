<?php

$URL = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');
$dbOptions = parse_url($_ENV['DATABASE_URL']);
$host = $dbOptions["host"];
$dbName = ltrim($dbOptions["path"],'/');
$dsn = "pgsql:host=$host;dbname=$dbName";

return [
    'class' => 'yii\db\Connection',
    'dsn' => $dsn,
    'username' => $dbOptions["user"],
    'password' => $dbOptions["pass"],
    'charset' => 'utf8',
];