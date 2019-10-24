<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Enum;

class OrderStatus
{
    /**
     * Дефолтный статус заказа при курьерской доставке
     */
    public const STATUS_NEW_COURIER = 'Q';

    /**
     * Дефолтный статус заказа при самовывозе
     */
    public const STATUS_NEW_PICKUP = 'N';

    /**
     * Заказ в сборке
     */
    public const STATUS_IN_ASSEMBLY_2 = 'W';

    /**
     * Заказ в сборке
     */
    public const STATUS_IN_ASSEMBLY_1 = 'H';

    /**
     * Заказ в пункте выдачи
     */
    public const STATUS_ISSUING_POINT = 'F';

    /**
     * Заказ доставлен
     */
    public const STATUS_DELIVERED = 'J';

    /**
     * Заказ доставляется ("Исполнен" для курьерской доставки)
     */
    public const STATUS_DELIVERING = 'Y';

    /**
     * Заказ выполнен
     */
    public const STATUS_FINISHED = 'G';

    /**
     * Заказ отменен (для курьерской доставки)
     */
    public const STATUS_CANCEL_COURIER = 'A';

    /**
     * Заказ отменен (для самовывоза)
     */
    public const STATUS_CANCEL_PICKUP = 'K';

    /**
     * Перезвонить (приходит из SAP, конда клиент не подошел за три попытки)
     */
    public const STATUS_CALL_BACK = 'B';

    /*
    * В пути
    */
    public const STATUS_IN_PROGRESS = 'D';

    /**
     * Продление хранения
     */
    public const STATUS_PICKUP_EXTEND = 'X';
}
