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
 * @property Member $owner
 */
class Bill extends ActiveRecord implements BillInterface
{
    public ?string $uuid = null;

    public ?string $code = null;

    public ?string $clientCode = null;

    public const SCENARIO_OPEN = 'open';

    private ?Member $_currentUser = null;
    private array $_currentUserDishes = [];
    private array $_commonDishes = [];
    private array $_billMembers = [];

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

    public function open(): array
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
                    return [
                        'success' => true,
                        'bill_id' => $model->id
                    ];
                } else {
                    $this->addError('uuid', 'Не удалось открыть счёт по данному UUID.');
                }
            } else {
                $this->addError('uuid', 'Невозможно открыть счёт при наличии существующего.');
            }
        } else {
            $this->addError('uuid', 'Пользователь в системе не найден с данным UUID.');
        }
        return [
            'success' => false,
            'bill_id' => null
        ];
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

    public function getBillMembers(): array
    {
        $result = [];
        /** @var Member $member */
        foreach ($this->_billMembers as $member) {
            if ($member->uuid !== $this->_currentUser->uuid) {
                $result[] = [
                    'username' => $member->username,
                    'owner' => $this->owner->uuid === $member->uuid,
                    'dishes' => $member->getDishes($this->id, false, true)[Dish::TYPE_PERSONAL],
                    'per_person_amount' => $this->getPerPersonAmount($member),
                    'personal_amount' => $this->getPersonalAmount($member)
                ];
            }
        }
        return $result;
    }


    public function getPersonalDishes(): array
    {
        $result = [];
        /** @var Dish $dish */
        foreach ($this->_currentUserDishes as $dish) {
            $result[] = [
                'id' => $dish->id,
                'title' => $dish->title,
                'cost' => $dish->cost
            ];
        }
        return $result;
    }

    public function getCommonDishes(bool $forHistory = false)
    {
        $data =  $this->owner->getDishes($this->id, true)[Dish::TYPE_COMMON];
        if ($forHistory) {
            $result = [];
            foreach ($data as $datum) {
                $result[] = [
                    'id' => $datum->id,
                    'title' => $datum->title,
                    'cost' => $datum->cost
                ];
            }
            $data = $result;
        }
        return $data;
    }

    public function getAmountInfo(string $uuid)
    {
        $this->_currentUser = Bill::getUserInfo($uuid);
        $this->prepareData($this->_currentUser);

        return [
            'total' => $this->getTotalAmount(),
            'per_person' => $this->getPerPersonAmount(),
            'common' => $this->getCommonAmount(),
            'personal' => $this->getPersonalAmount()
        ];
    }

    private function prepareData(Member $member)
    {
        $this->_currentUserDishes = $member->getDishes($this->id)[Dish::TYPE_PERSONAL];
        $this->_commonDishes = $this->owner->getDishes($this->id, true)[Dish::TYPE_COMMON];
        $this->_billMembers = $this->members;
    }

    private function getTotalAmount(): float
    {
        $amount = 0;
        /** @var Member $member */
        foreach ($this->_billMembers as $member) {
            $amount += $this->getPerPersonAmount($member);
        }
        return $amount;
    }

    private function getPerPersonAmount(Member $member = null): float
    {
        $commonAmount = $this->getCommonAmount();
        $personalAmount = $this->getPersonalAmount($member);
        $partOfCommonAmount = round($commonAmount / $this->getCountOfBillMembers());
        return $personalAmount + $partOfCommonAmount;
    }

    private function getCommonAmount(): float
    {
        $amount = 0;
        /** @var Dish $dish */
        foreach ($this->_commonDishes as $dish) {
            $amount += $dish->cost;
        }
        return $amount;
    }

    private function getPersonalAmount(Member $member = null): float
    {
        $amount = 0;
        $dishes = is_null($member)
            ? $this->_currentUserDishes
            : $member->getDishes($this->id)[Dish::TYPE_PERSONAL];
        /** @var Dish $dish */
        foreach ($dishes as $dish) {
            $amount += $dish->cost;
        }
        return $amount;
    }
    private function getCountOfBillMembers(): int
    {
        return count($this->_billMembers);
    }
}