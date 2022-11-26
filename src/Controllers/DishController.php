<?php

namespace Bouledepate\CaffyApi\Controllers;

use Bouledepate\CaffyApi\Models\Dish;
use yii\db\StaleObjectException;
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
                    'remove' => ['POST'],
                    'refuse' => ['POST']
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

    /**
     * @throws StaleObjectException
     * @throws \Throwable
     * @throws BadRequestHttpException
     */
    public function actionRefuse(): array
    {
        $id = \Yii::$app->request->getBodyParam('dish');
        $model = Dish::findOne(['id' => $id]);
        $model->uuid = \Yii::$app->request->getBodyParam('uuid');
        $response = ['success' => false];
        if (is_null($model)) {
            return $response;
        }
        if ($model->validate()) {
            $model->toggleRefusedProperty();
            $response['success'] = true;
            return $response;
        } else {
            throw new BadRequestHttpException("Невозможно изменить статус блюда.");
        }
    }
}