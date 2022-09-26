<?php

namespace Bouledepate\CaffyApi\Interfaces;

interface MemberInterface
{
    public function join(string $code);

    public function left();
}