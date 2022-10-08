<?php

namespace Bouledepate\CaffyApi\Forms;

use Bouledepate\CaffyApi\Models\Bill;
use Bouledepate\CaffyApi\Models\Member;
use yii\base\Model;

class HistoryForm extends Model
{
    public int|null $bill;
    public string $uuid;

    private Member $member;

    public const SCENARIO_ALL = 'all';
    public const SCENARIO_DETAIL = 'detail';

    public function scenarios(): array
    {
        return [
            self::SCENARIO_ALL => ['uuid'],
            self::SCENARIO_DETAIL => ['uuid', 'bill']
        ];
    }

    public function rules(): array
    {
        return [
            [['uuid', 'bill'], 'required'],
            ['uuid', 'string'],
            ['bill', 'integer']
        ];
    }

    public function allHistory(): array
    {
        return $this->member->getBillsAsArray();
    }

    public function detailHistory(): array
    {
        $bill = Bill::findOne(['id' => $this->bill]);
        if (!is_null($bill)) {
            $data = [
                'id' => $bill->id,
                'title' => $bill->title,
                'owner' => $bill->owner->username,
                'total' => rand(1000, 3000),
                'dishes' => [],
                'members' => $bill->getBillMembers()
            ];
        }
        return $data ?? [];
    }

    public function afterValidate()
    {
        parent::afterValidate();
        if (!empty($this->uuid)) {
            $this->member = $this->defineUser($this->uuid);
        }
    }

    private function defineUser(string $uuid)
    {
        return Member::findOne(['uuid' => $uuid]);
    }
}