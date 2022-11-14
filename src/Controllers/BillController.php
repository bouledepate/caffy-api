<?php

namespace Bouledepate\CaffyApi\Controllers;

use Bouledepate\CaffyApi\Forms\DishesForm;
use Bouledepate\CaffyApi\Models\Bill;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\rest\OptionsAction;

class BillController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'open' => ['POST'],
                    'close' => ['POST'],
                    'code' => ['POST']
                ]
            ]
        ];
    }

    public function actions()
    {
        return [
            'options' => OptionsAction::class
        ];
    }

    public function actionOpen(): array
    {
        $model = new Bill();
        $model->setScenario(Bill::SCENARIO_OPEN);
        $model->setAttributes(\Yii::$app->request->getBodyParams());
        if ($model->validate()) {
            $result = $model->open();
            if ($result['success']) {
                return [
                    'success' => $result['success'],
                    'id' => $result['bill_id'],
                    'title' => $result['bill_title'],
                    'owner' => $result['bill_owner'],
                    'is_owner' => $result['bill_is_owner'],
                    'code' => $model->getInviteCode(),
                ];
            }
        }
        \Yii::$app->response->setStatusCode(422);
        return $model->getFirstErrors();
    }

    public function actionCode(): array
    {
        $model = new Bill();
        $model->setAttributes(\Yii::$app->request->getBodyParams());
        if ($model->validate()) {
            $code = $model->getInviteCode();
            if (!is_null($code)) {
                return ['code' => $code];
            }
        }
        \Yii::$app->response->setStatusCode(422);
        return $model->getFirstErrors();
    }

    public function actionClose(): array
    {
        $model = new Bill();
        $model->setAttributes(\Yii::$app->request->getBodyParams());
        if ($model->validate()) {
            if ($model->close()) {
                return ['success' => true];
            }
        }
        \Yii::$app->response->setStatusCode(422);
        return $model->getFirstErrors();
    }
}