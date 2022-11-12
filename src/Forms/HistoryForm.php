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
        $detail = [
            'id' => $bill->id,
            'title' => $bill->title,
            'owner' => $bill->owner->username,
            'closed' => $bill->is_closed,
            'created_at' => $bill->created_at,
            'amount' => $bill->getAmountInfo($this->uuid),
            'common_dishes' => $bill->getCommonDishes(true),
            'personal_dishes' => $bill->getPersonalDishes(),
            'members' => $bill->getBillMembers()
        ];
        return $detail;
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