<?php

namespace Bouledepate\CaffyApi\Models;

use yii\db\ActiveRecord;

class Dish extends ActiveRecord
{
    public function rules()
    {
        return [
            [['title', 'cost', 'bill_member_id'], 'required'],
            ['title', 'integer']
        ];
    }
}