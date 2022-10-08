<?php

namespace Bouledepate\CaffyApi\Models;

use Bouledepate\CaffyApi\Interfaces\MemberInterface;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $uuid
 * @property string $username
 */
class Member extends ActiveRecord implements MemberInterface
{
    public function rules()
    {
        return [
            [['uuid', 'username'], 'required'],
            [['uuid', 'username'], 'string'],
            [['uuid', 'username'], 'trim'],
            ['uuid', 'unique'],
            ['code', 'safe']
        ];
    }

    public function getBills()
    {
        return $this->hasMany(Bill::class, ['id' => 'bill_id'])
            ->viaTable('bill_member', ['member_id' => 'id']);
    }

    public function getBillsAsArray()
    {
        $ownBills = [];
        $guestBills = [];

        /** @var Bill $bill */
        foreach ($this->getBills()->orderBy(['created_at' => SORT_DESC])->all() as $bill) {
            if ($bill->owner_id === $this->id) {
                $ownBills[] = [
                    'id' => $bill->id,
                    'title' => $bill->title,
                    'owner' => $this->username,
                    'created_at' => $bill->created_at,
                    'closed' => $bill->is_closed
                ];
            } else {
                $guestBills[] = [
                    'id' => $bill->id,
                    'title' => $bill->title,
                    'owner' => $bill->owner->username,
                    'created_at' => $bill->created_at,
                    'closed' => $bill->is_closed
                ];
            }
        }

        return [
            'own_bills' => $ownBills,
            'guest_bills' => $guestBills
        ];
    }

    public function getDishes(): array
    {
        return [];
    }

    public function join(?string $code): bool
    {
        if (!$this->hasActiveBill()) {
            $bill = Bill::getByCode($code);
            if (!is_null($bill) && !$bill->hasMember($this)) {
                $bill->uuid = $this->uuid;
                return $bill->addMember($this, $code);
            }
        }
        return false;
    }

    public function left(): bool
    {
        $bill = Bill::currentByUuid($this->uuid);
        if (!is_null($bill) && !$this->isBillOwner($bill)) {
            $bill->removeMember($this);
            return true;
        }
        return false;
    }

    public function hasActiveBill(): bool
    {
        /** @var Bill $bill */
        foreach ($this->bills as $bill) {
            if (!$bill->is_closed) {
                return true;
            }
        }
        return false;
    }

    public function isBillOwner(Bill $bill): bool
    {
        return $this->id === $bill->owner_id;
    }
}