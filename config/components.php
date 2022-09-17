<?php

$rules = require_once 'routes/main.php';
$database = require_once 'database.php';

return [
    'request' => [
        'cookieValidationKey' => 'EirtlWCUMLqCNoD5N7zXxS-bSlxvp4Ai',
        'parsers' => [
            'application/json' => 'yii\web\JsonParser',
        ],
    ],
    'response' => [
        'class' => 'yii\web\Response',
        'on beforeSend' => function ($event) {
            $response = $event->sender;
            if ($response->data !== null) {
                $response->data = [
                    'success' => $response->isSuccessful,
                    'data' => $response->data,
                ];
                $response->statusCode = 200;
            }
        },
        'format' => 'json',
        'charset' => 'UTF-8',
        'formatters' => [
            'json' => [
                'class' => 'yii\web\JsonResponseFormatter',
                'prettyPrint' => YII_DEBUG,
                'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            ],
            'xml' => 'yii\web\XmlResponseFormatter',
        ],
    ],
    'db' => $database,
    'urlManager' => [
        'enablePrettyUrl' => true,
        'enableStrictParsing' => true,
        'showScriptName' => false,
        'rules' => $rules
    ]
];