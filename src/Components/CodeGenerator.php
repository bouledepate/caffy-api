<?php

namespace Bouledepate\CaffyApi\Components;

class CodeGenerator
{
    private const CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

    public static function generate(): string
    {
        return substr(str_shuffle(self::CHARS), 0, 8);
    }
}