<?php

namespace Bouledepate\CaffyApi\Models;

use Bouledepate\CaffyApi\Components\CodeGenerator;
use yii\db\ActiveRecord;


/**
 * @property string $code
 * @property int $bill_id
 * @property int $expired_at
 */
class InviteCode extends ActiveRecord
{
    public function rules()
    {
        return [
            [['code', 'bill_id', 'expired_at'], 'required'],
            ['code', 'unique'],
            ['code', 'string'],
            ['code', 'trim'],
            ['expired_at', 'safe']
        ];
    }

    private static function createModel(string $bill): self|false
    {
        $model = new self;
        $model->setAttributes([
            'code' => CodeGenerator::generate(),
            'bill_id' => $bill,
            'expired_at' => date('Y-m-d H:i:s', time() + 86400)
        ]);
        return $model->save() ? $model : false;
    }

    public static function create(int $bill): string|false
    {
        if ($result = self::createModel($bill)) {
            return $result->code;
        }
        return $result;
    }

    public static function getCode(int $bill): string
    {
        $data = self::findAll(['bill_id' => $bill]);
        if (!empty($data)) {
            foreach ($data as $item) {
                if (time() < strtotime($item->expired_at)) {
                    return $item->code;
                }
            }
        }
        return self::create($bill);
    }

    public static function getCodeBill(string $code): ?int
    {
        $model = self::findOne(['code' => $code]);
        return $model->bill_id ?? null;
    }
}