<?php

namespace Bouledepate\CaffyApi\Interfaces;

interface BillInterface
{
    public function getInviteCode();

    public function validateInviteCode(string $code);

    public function open(): bool;

    public function close();

    public static function currentByUuid(string $uuid);

    public static function allByUuid(string $uuid);
}