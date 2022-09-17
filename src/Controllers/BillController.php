<?php

namespace Bouledepate\CaffyApi\Controllers;

use yii\rest\Controller;
use yii\rest\OptionsAction;

class BillController extends Controller
{
    public function actions()
    {
        return [
            'options' => OptionsAction::class
        ];
    }
}