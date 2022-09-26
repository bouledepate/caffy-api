<?php

namespace Bouledepate\CaffyApi\Controllers;

use Bouledepate\CaffyApi\Models\Member;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\rest\OptionsAction;
use yii\web\NotFoundHttpException;

class ClientController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'sign-in' => ['POST'],
                    'index' => ['GET']
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

    public function actionIndex(string $uuid = null)
    {
        $model = Member::findOne(['uuid' => $uuid]);
        if (is_null($model)) {
            throw new NotFoundHttpException('Данные по пользователю не найдены');
        } else {
            return $model;
        }
    }

    public function actionSignIn()
    {
        $model = new Member();
        $model->setAttributes(\Yii::$app->request->getBodyParams());
        if ($model->validate()) {
            $model->save(false);
            \Yii::$app->response->setStatusCode(201);
        } else {
            \Yii::$app->response->setStatusCode(422);
            return $model->getFirstErrors();
        }
    }

    public function actionJoin(): array
    {
        $uuid = \Yii::$app->request->getBodyParam('uuid');
        if (is_null($uuid)) {
            return $this->handleInvalidUuid();
        }
        $model = $this->identifyUser($uuid);
        $result = $model->join(\Yii::$app->request->getBodyParam('code'));
        return ['success' => $result];
    }

    public function actionLeft(): array
    {
        $uuid = \Yii::$app->request->getBodyParam('uuid');
        if (is_null($uuid)) {
            return $this->handleInvalidUuid();
        }
        $model = $this->identifyUser($uuid);
        $result = $model->left();
        return ['success' => $result];
    }

    private function identifyUser(string $uuid): ?Member
    {
        return Member::findOne(['uuid' => $uuid]);
    }

    private function handleInvalidUuid()
    {
        \Yii::$app->response->setStatusCode(401);
        return [
            'uuid' => 'Ошибка идентификации. Передано некорректное значение UUID.'
        ];
    }
}