<?php

namespace Bouledepate\CaffyApi\Controllers;

use Bouledepate\CaffyApi\Models\Dish;
use yii\filters\VerbFilter;
use yii\rest\OptionsAction;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;

class DishController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add' => ['POST'],
                    'remove' => ['GET']
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

    public function actionAdd()
    {
        $model = new Dish();
        $model->setAttributes(\Yii::$app->request->getBodyParams());
        if ($model->validate() && $model->save(false)) {
            return $model;
        } else {
            throw new BadRequestHttpException("Переданы некорректные параметры блюда");
        }
    }

    public function actionRemove()
    {
        $id = \Yii::$app->request->getBodyParam('id');
        $model = Dish::findOne(['id' => $id]);
        $response = ['success' => true];
        if (is_null($model)) {
            return $response;
        }
        return [
            'success' => (bool)$model->delete()
        ];
    }
}