<?php

namespace FourPaws\SaleBundle\Enum;

class ForgotBasketEnum
{
    public const TYPE_NOTIFICATION = 1;
    public const TYPE_REMINDER     = 2;

    public const INTERVAL_NOTIFICATION = 3600;
    public const INTERVAL_REMINDER     = 259200;

    public const BLOCK_NOTIFICATION = 259200;

    public static function getTypes():array
    {
        return [
            self::TYPE_NOTIFICATION,
            self::TYPE_REMINDER,
        ];
    }
}
