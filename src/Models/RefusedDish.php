<?php

namespace Bouledepate\CaffyApi\Models;

use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * @property int $dish_id
 * @property int $member_id
 * @property bool $state
 * @property Dish $dish
 */
class RefusedDish extends ActiveRecord
{
    public function rules()
    {
        return [
            [['dish_id', 'member_id'], 'required'],
            ['state', 'default']
        ];
    }

    public function getDish()
    {
        return $this->hasOne(Dish::class, ['dish_id' => 'id']);
    }

    public static function addNote(int $dish, int $member, bool $state)
    {
        $model = new self();
        $model->setAttributes([
            'dish_id' => $dish,
            'member_id' => $member,
            'state' => $state
        ]);
        return $model->save();
    }

    public static function isRefused(int $dish, int $member): bool|null
    {
        $model = self::findOne(['dish_id' => $dish, 'member_id' => $member]);
        if (is_null($model)) {
            return null;
        }
        return $model->state;
    }

    public static function updateNote(int $dish, int $member, bool $state)
    {
        $model = self::findOne(['dish_id' => $dish, 'member_id' => $member]);
        $model->setAttribute('state', $state);
        return $model->update();
    }
}