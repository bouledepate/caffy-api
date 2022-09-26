<?php

namespace Bouledepate\CaffyApi\Models;

use Bouledepate\CaffyApi\Components\CodeGenerator;
use Bouledepate\CaffyApi\Interfaces\BillInterface;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $title
 * @property bool $is_closed
 * @property int $created_at
 */
class Bill extends ActiveRecord implements BillInterface
{
    public ?string $uuid = null;

    public ?string $code = null;

    public ?string $clientCode = null;

    public const SCENARIO_OPEN = 'open';

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_at']
                ],
                'value' => new Expression('CURRENT_TIMESTAMP'),
            ]
        ];
    }

    public function rules()
    {
        return [
            [['title'], 'required', 'on' => self::SCENARIO_OPEN],
            ['title', 'string'],
            ['title', 'trim'],
            ['owner_id', 'exist', 'targetAttribute' => 'id', 'targetClass' => Member::class],
            [['created_at', 'is_closed', 'uuid', 'owner_id'], 'safe']
        ];
    }

    public function getMembers()
    {
        return $this->hasMany(Member::class, ['id' => 'member_id'])
            ->viaTable('bill_member', ['bill_id' => 'id']);
    }

    public function getOwner()
    {
        return $this->hasOne(Member::class, ['id' => 'owner_id']);
    }

    private static function getUserInfo(string $uuid): ?Member
    {
        return Member::findOne(['uuid' => $uuid]);
    }

    private static function createModel(array $data): self|false
    {
        list($title, $owner) = $data;
        $model = new self();
        $model->setAttributes([
            'title' => $title,
            'owner_id' => $owner
        ]);
        return $model->save() ? $model : false;
    }

    private function createInviteCode(int $bill): void
    {
        InviteCode::create($bill);
    }

    public function getInviteCode(string $uuid = null): ?string
    {
        $bill = self::currentByUuid($uuid ?? $this->uuid);
        if (!is_null($bill)) {
            return InviteCode::getCode($bill->id);
        }
        $this->addError('uuid', 'По данному UUID не найдено открытого счёта.');
        return null;
    }

    public function validateInviteCode(string $code): bool
    {
        $billOwnerUuid = $this->owner->uuid;
        $currentCode = $this->getInviteCode($billOwnerUuid);
        return $currentCode === $code;
    }

    public function open(): bool
    {
        /** @var Member $user */
        $user = self::getUserInfo($this->uuid);
        if (!is_null($user)) {
            if (!$user->hasActiveBill()) {
                $model = self::createModel([
                    $this->title, $user->id
                ]);
                if ($model) {
                    $this->createInviteCode($model->id);
                    $this->linkModel($user, $model->id);
                    return true;
                } else {
                    $this->addError('uuid', 'Не удалось открыть счёт по данному UUID.');
                }
            } else {
                $this->addError('uuid', 'Невозможно открыть счёт при наличии существующего.');
            }
        } else {
            $this->addError('uuid', 'Пользователь в системе не найден с данным UUID.');
        }
        return false;
    }

    public function close(): bool
    {
        $bill = self::currentByUuid($this->uuid);
        if (!is_null($bill)) {
            $bill->setAttribute('is_closed', true);
            return $bill->update();
        } else {
            $this->addError('uuid', 'По данному UUID не найдено открытого счёта.');
        }
        return false;
    }

    public static function currentByUuid(string $uuid): ?self
    {
        $user = self::getUserInfo($uuid);
        if (!is_null($user)) {
            /** @var self $bill */
            foreach ($user->bills as $bill) {
                if (!$bill->is_closed) {
                    return $bill;
                }
            }
        }
        return null;
    }

    public static function allByUuid(string $uuid): ?array
    {
        $user = self::getUserInfo($uuid);
        if (!is_null($user)) {
            return $user->bills;
        }
        return null;
    }

    public static function getByCode(string $code): ?self
    {
        $billId = InviteCode::getCodeBill($code);
        if (!is_null($billId)) {
            return self::findOne(['id' => $billId]);
        }
        return null;
    }

    public function hasMember(Member $user): bool
    {
        foreach ($this->members as $member) {
            if ($member->uuid === $user->uuid) {
                return true;
            }
        }
        return false;
    }

    public function addMember(Member $member, string $code): bool
    {
        if ($this->validateInviteCode($code)) {
            $this->linkModel($member, $this->id);
            return true;
        }
        return false;
    }

    public function removeMember(Member $member)
    {
        $this->unlinkModel($member, $this->id);
    }

    private function linkModel(Member $member, int $bill): bool
    {
        try {
            \Yii::$app->db->createCommand()->insert('bill_member', [
                'bill_id' => $bill,
                'member_id' => $member->id
            ])->execute();
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    private function unlinkModel(Member $member, int $bill)
    {
        try {
            \Yii::$app->db->createCommand()->delete('bill_member', [
                'bill_id' => $bill,
                'member_id' => $member->id
            ])->execute();
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }
}