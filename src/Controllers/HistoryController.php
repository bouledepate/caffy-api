<?php

namespace Bouledepate\CaffyApi\Controllers;

use Bouledepate\CaffyApi\Forms\HistoryForm;
use Bouledepate\CaffyApi\Models\Member;
use yii\filters\VerbFilter;
use yii\rest\OptionsAction;
use yii\rest\Controller;
use yii\web\UnauthorizedHttpException;

class HistoryController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'all' => ['POST'],
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

    public function actionAll(): array
    {
        $model = new HistoryForm();
        $model->setScenario(HistoryForm::SCENARIO_ALL);
        $model->setAttributes(\Yii::$app->request->getBodyParams());
        if ($model->validate()) {
            $data = $model->allHistory();
        } else {
            $data = [
                'own_bills' => null,
                'guest_bills' => null
            ];
        }
        return $data;
    }

    public function actionDetail()
    {
        $model = new HistoryForm();
        $model->setScenario(HistoryForm::SCENARIO_DETAIL);
        $model->setAttributes(\Yii::$app->request->getBodyParams());
        if ($model->validate()) {
            $data = $model->detailHistory();
        } else {
            $data = null;
        }
        if (is_null($data)) {
            $data = [
                'id' => null,
                'title' => null,
                'owner' => null,
                'total' => rand(1000, 3000),
                'dishes' => [],
                'members' => []
            ];
        }
        return $data;
    }
}