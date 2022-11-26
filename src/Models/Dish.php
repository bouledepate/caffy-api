<?php

namespace Bouledepate\CaffyApi\Models;

use yii\db\ActiveRecord;
use yii\db\Query;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;

/**
 * @property int $id
 * @property int $bill_member_id
 * @property string $title
 * @property string $type
 * @property int $cost
 * @property bool $refused
 */
class Dish extends ActiveRecord
{
    public string $uuid = '';
    public int $bill = 0;
    public bool $common = false;

    public const TYPE_PERSONAL = 'personal';
    public const TYPE_COMMON = 'common';

    public function rules(): array
    {
        return [
            [['title', 'cost', 'uuid', 'bill'], 'required'],
            [['title'], 'string'],
            [['bill_member_id', 'common', 'refused'], 'safe']
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->defineRelationId();
        $this->type = $this->common ? self::TYPE_COMMON : self::TYPE_PERSONAL;
        return true;
    }

    public function fields(): array
    {
        $fields = parent::fields();
        unset($fields['bill_member_id']);
        return $fields;
    }

    private function defineRelationId(): void
    {
        $data = (new Query())
            ->select('id')
            ->from('bill_member')
            ->where([
                'bill_id' => $this->bill,
                'member_id' => $this->getMemberId(),
            ])
            ->one();
        $this->bill_member_id = $data['id'];
    }

    private function getMemberId(): int
    {
        $user = Member::findOne(['uuid' => $this->uuid]);
        if (is_null($user)) {
            throw new BadRequestHttpException("Передан некорректный пользовательский UUID");
        }
        return $user->id;
    }

    /**
     * @throws StaleObjectException
     * @throws \Throwable
     */
    public function toggleRefusedProperty(): void
    {
        $member = $this->getMemberId();
        if ($this->type == self::TYPE_COMMON) {
            $currentState = RefusedDish::isRefused($this->id, $member);
            if (is_null($currentState)) {
                RefusedDish::addNote($this->id, $member, true);
            } else {
                RefusedDish::updateNote($this->id, $member, !$currentState);
            }
        }
    }

    public static function types(): array
    {
        return [
            self::TYPE_PERSONAL,
            self::TYPE_COMMON
        ];
    }
}