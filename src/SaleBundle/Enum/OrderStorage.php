<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Enum;


class OrderStorage
{
    public const NOVALIDATE_STEP = 'novalidate';

    public const AUTH_STEP = 'auth';

    public const DELIVERY_STEP = 'delivery';

    public const PAYMENT_STEP = 'payment';

    public const PAYMENT_STEP_CARD = 'payment-card';

    public const COMPLETE_STEP = 'complete';

    /**
     * Порядок оформления заказа
     */
    public const STEP_ORDER = [
        self::AUTH_STEP,
        self::DELIVERY_STEP,
        self::PAYMENT_STEP,
        self::COMPLETE_STEP,
    ];
}