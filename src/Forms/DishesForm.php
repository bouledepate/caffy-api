<?php

namespace Bouledepate\CaffyApi\Forms;

use yii\base\Model;

class DishesForm extends Model
{
    public $uuid;
    public $bill;

    public function rules()
    {
        return [
            [['uuid', 'bill'], 'required'],
            ['bill', 'integer'],
            ['uuid', 'string']
        ];
    }
}