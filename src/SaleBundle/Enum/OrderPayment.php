<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Enum;


class OrderPayment
{
    public const PAYMENT_CASH_OR_CARD = 'cash-or-card';
    public const PAYMENT_CASH         = 'cash';
    public const PAYMENT_ONLINE       = 'card-online';
    public const PAYMENT_INNER        = 'inner';

    public const GENERIC_DELIVERY_CODE = '2000001';
}
