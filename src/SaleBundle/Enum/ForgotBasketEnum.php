<?php

namespace FourPaws\SaleBundle\Enum;


class ForgotBasketEnum
{
    public const TYPE_FIELD_CODE = 'UF_TASK_TYPE';

    public const TYPE_NOTIFICATION = 'notification';
    public const TYPE_REMINDER     = 'reminder';

    public const INTERVAL_NOTIFICATION = 3600;
    public const INTERVAL_REMINDER     = 259200;

    public const BLOCK_NOTIFICATION = 259200;
}
