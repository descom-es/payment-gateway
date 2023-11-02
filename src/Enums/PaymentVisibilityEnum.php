<?php

namespace Descom\Payment\Enums;

enum PaymentVisibilityEnum: string
{
    case public = 'public';
    case private = 'private';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
