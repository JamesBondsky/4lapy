<?php

namespace FourPaws\SaleBundle\Enum;


class ForgotBasketEnum
{
    public const TYPE_FIELD_CODE = 'UF_TASK_TYPE';

    public const TYPE_NOTIFICATION = 'NOTIFICATION';
    public const TYPE_REMINDER     = 'REMINDER';
    public const TYPE_ALL          = 'ALL';

    public const INTERVAL_NOTIFICATION = 3600;
    public const INTERVAL_REMINDER     = 259200;
}
