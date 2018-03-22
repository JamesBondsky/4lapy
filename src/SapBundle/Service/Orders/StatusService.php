<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\Orders;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\DeliveryBundle\Service\DeliveryService;
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
        'V' => 'A'
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
        'V' => 'K'
    ];

    private const STATUS_MAP = [
        DeliveryService::INNER_DELIVERY_CODE => self::STATUS_COURIER_MAP,
        DeliveryService::DPD_DELIVERY_CODE => self::STATUS_COURIER_MAP,
        DeliveryService::DPD_PICKUP_CODE => self::STATUS_PICKUP_MAP,
        DeliveryService::INNER_PICKUP_CODE => self::STATUS_PICKUP_MAP
    ];

    /**
     * @param string $deliveryType
     * @param string $sapStatus
     * @return string
     *
     * @throws NotFoundOrderStatusException
     */
    public function getStatusBySapStatus(string $deliveryType, string $sapStatus): string
    {
        $orderStatus = null;
        $deliveryStatusMap = self::STATUS_MAP[$deliveryType];

        if ($deliveryStatusMap) {
            $orderStatus = $deliveryStatusMap[$sapStatus];
        }

        if (null === $orderStatus) {
            throw new NotFoundOrderStatusException(sprintf(
                'Не найден статус %s для службы доставка %s',
                $sapStatus,
                $deliveryType
            ));
        }

        return $orderStatus;
    }
}
