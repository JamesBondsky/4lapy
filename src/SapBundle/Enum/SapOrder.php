<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Enum;

/**
 * Class SapOrder
 *
 * @package FourPaws\SapBundle\Enum
 */
final class SapOrder
{
    /**
     * Способ получения заказа
     *
     * 01 – Курьерская доставка из РЦ;
     * 02 – Самовывоз из магазина;
     * 06 – Курьерская доставка из магазина;
     * 07 – Доставка внешним подрядчиком (курьер или самовывоз из пункта выдачи заказов);
     * 08 – РЦ – магазин – домой.
     */
    public const DELIVERY_TYPE_COURIER_RC = '01';
    public const DELIVERY_TYPE_PICKUP = '02';
    public const DELIVERY_TYPE_COURIER_SHOP = '06';
    public const DELIVERY_TYPE_CONTRACTOR = '07';
    public const DELIVERY_TYPE_ROUTE = '08';
    
    /**
     * Тип доставки подрядчиком
     * Поле должно быть заполнено, если выбран способ получения заказа 07.
     *
     * ТД – от терминала до двери покупателя;
     * ТТ – от терминала до пункта выдачи заказов.
     */
    public const DELIVERY_TYPE_CONTRACTOR_DELIVERY = 'ТД';
    public const DELIVERY_TYPE_CONTRACTOR_PICKUP = 'ТТ';
    public const DELIVERY_CONTRACTOR_CODE = '0000802070';
    
    public const ORDER_PAYMENT_ONLINE_MERCHANT_ID = '850000314610';
    public const ORDER_PAYMENT_ONLINE_CODE = '05';
    public const ORDER_PAYMENT_STATUS_PAYED = '01';
    public const ORDER_PAYMENT_STATUS_NOT_PAYED = '02';
    public const ORDER_PAYMENT_STATUS_PRE_PAYED = '03';
    
    public const PAYMENT_SYSTEM_ONLINE_ID = 3;
    
    public const UNIT_PTC_CODE = 'PCE';

    public const TEST_PHONE = '79060767386';
    public const TEST_COMMENT = '!!! ТЕСТ';
}
