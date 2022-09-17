<?php

namespace Bouledepate\CaffyApi\Controllers;

use yii\web\Controller;

class CoreController extends Controller
{
    public function actionIndex()
    {
        return [
            'status' => 'OK'
        ];
    }
}