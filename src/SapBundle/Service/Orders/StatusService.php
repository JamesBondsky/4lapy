<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\Orders;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Enum\OrderStatus;
use FourPaws\SapBundle\Exception\NotFoundOrderStatusException;
use Psr\Log\LoggerAwareInterface;

/**
 * Class StatusService
 *
 * @package FourPaws\SapBundle\Service\Orders
 */
class StatusService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    private const STATUS_COURIER_MAP = [
        '1' => 'Q',
        '2' => 'C',
        '3' => 'W',
        '4' => 'E',
        '5' => 'R',
        '6' => 'A',
        '7' => 'D',
        '8' => 'F',
        '9' => 'I',
        'B' => 'Y',
        'P' => 'J',
        'K' => '',
        'E' => '',
        'V' => 'A',
    ];

    /**
     * id-dostavista => id-sap
     *
     * dostavista status    site status             sap status
     * 0 - “Создан”         “Новый”                 O - “Получен подрядчиком” (новый статус)
     * 1 - “Доступен”       “Новый”                 L - “Заказ принят подрядчиком”
     * 2 - “Активен”        “Исполнен автоматом”    M - “Заказ едет по маршруту”
     * 3 - “Завершен”       “Оплачен(доставлен)”    N - “Заказ вручен”
     * 10 - “Отменен”       “Отменен”               6 - “Отменен”
     * 16 - “Отложен”       Статус не изменяется    K - “Треб. соглас. операт.”
     */
    private const STATUS_DOSTAVISTA_MAP = [
        'O' => OrderStatus::STATUS_NEW_COURIER,
        'L' => OrderStatus::STATUS_NEW_COURIER,
        'M' => OrderStatus::STATUS_DELIVERING,
        'N' => OrderStatus::STATUS_FINISHED,
        '6' => OrderStatus::STATUS_CANCEL_COURIER,
        'K' => ''
    ];

    public const STATUS_SITE_DOSTAVISTA_MAP = [
        'O' => 'new',
        'L' => 'available',
        'M' => 'active',
        'N' => 'completed',
        '6' => 'canceled',
        'K' => 'delayed'
    ];

    private const STATUS_PICKUP_MAP = [
        '1' => 'N',
        '2' => 'C',
        '3' => 'H',
        '4' => 'M',
        '5' => 'P',
        '6' => 'K',
        '7' => 'D',
        '8' => 'F',
        'B' => 'G',
        'P' => 'J',
        'K' => '',
        'E' => '',
        'V' => 'K',
    ];

    private const STATUS_MAP = [
        DeliveryService::INNER_DELIVERY_CODE => self::STATUS_COURIER_MAP,
        DeliveryService::DPD_DELIVERY_CODE => self::STATUS_COURIER_MAP,
        DeliveryService::DPD_PICKUP_CODE => self::STATUS_PICKUP_MAP,
        DeliveryService::INNER_PICKUP_CODE => self::STATUS_PICKUP_MAP,
        DeliveryService::DELIVERY_DOSTAVISTA_CODE => self::STATUS_DOSTAVISTA_MAP,
        /**@todo remove - it`s from old site */
        16 => self::STATUS_PICKUP_MAP,
        17 => self::STATUS_COURIER_MAP,
        18 => self::STATUS_COURIER_MAP,
        19 => self::STATUS_COURIER_MAP,
        20 => self::STATUS_COURIER_MAP,
        21 => self::STATUS_COURIER_MAP,
        22 => self::STATUS_COURIER_MAP,
        23 => self::STATUS_COURIER_MAP,
        24 => self::STATUS_COURIER_MAP,
        25 => self::STATUS_COURIER_MAP,
        26 => self::STATUS_PICKUP_MAP,
        27 => self::STATUS_PICKUP_MAP,
        28 => self::STATUS_PICKUP_MAP,
    ];

    /**
     * @param string $deliveryType
     * @param string $sapStatus
     * @throws NotFoundOrderStatusException
     * @return string
     *
     */
    public function getStatusBySapStatus(string $deliveryType, string $sapStatus): string
    {
        $orderStatus = null;
        $deliveryStatusMap = self::STATUS_MAP[$deliveryType];

        if ($deliveryStatusMap) {
            $orderStatus = $deliveryStatusMap[$sapStatus];
        }

        if (null === $orderStatus) {
            throw new NotFoundOrderStatusException(
                \sprintf(
                    'Не найден статус %s для службы доставки %s',
                    $sapStatus,
                    $deliveryType
                )
            );
        }

        return $orderStatus;
    }
}
