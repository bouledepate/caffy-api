<?php

namespace Bouledepate\CaffyApi\Controllers;

use Bouledepate\CaffyApi\Models\Bill;
use Bouledepate\CaffyApi\Models\Member;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\rest\OptionsAction;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class ClientController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'sign-in' => ['POST'],
                    'index' => ['GET'],
                    'current-bill' => ['POST']
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
            if ($model->save(false)) {
                return ['success' => true];
            } else {
                return ['success' => false];
            }
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
        if ($result) {
            $bill = Bill::currentByUuid($model->uuid);
            return [
                'success' => true,
                'title' => $bill->title,
                'owner' => $bill->owner->username
            ];
        } else {
            throw new UnprocessableEntityHttpException("Передан неверный код приглашения");
        }
    }

    public function actionLeft(): array
    {
        $uuid = \Yii::$app->request->getBodyParam('uuid');
        if (is_null($uuid)) {
            $this->handleInvalidUuid();
        }
        $model = $this->identifyUser($uuid);
        $result = $model->left();
        return ['success' => $result];
    }

    public function actionCurrentBill(): array
    {
        $uuid = \Yii::$app->request->getBodyParam('uuid');
        if (is_null($uuid)) {
            $this->handleInvalidUuid();
        }
        $user = $this->identifyUser($uuid);
        $bill = Bill::currentByUuid($uuid);
        if (!is_null($bill)) {
            $code = $bill->getInviteCode($uuid);
        }

        return [
            'exist' => !is_null($bill),
            'is_owner' => $user->id === $bill->owner_id ?? false,
            'title' => $bill->title ?? null,
            'owner' => $bill->owner->username ?? null,
            'code' => $code ?? null
        ];
    }

    private function identifyUser(string $uuid): ?Member
    {
        return Member::findOne(['uuid' => $uuid]);
    }

    private function handleInvalidUuid()
    {
       throw new ForbiddenHttpException('Передан некорректный UUID');
    }
}