<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Restrictions;

use Bitrix\Sale\Delivery\Restrictions;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Payment;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Service\OrderService;

class PaymentByDeliveryRestriction extends Restrictions\Base
{
    public static function getClassTitle(): string
    {
        return 'Ограничение по службам доставки, которое работает';
    }

    public static function getClassDescription(): string
    {
        return 'способ оплаты будет доступна для указанных доставок';
    }

    public static function check($sum, array $restrictionParams, $paySystemId = 0): bool
    {
        if (!$paySystemId) {
            return false;
        }

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        $orderStorage = $orderService->getStorage();
        if (!$orderStorage->getDeliveryId()) {
            return true;
        }

        $deliveryCode = $deliveryService->getDeliveryCodeById($orderStorage->getDeliveryId());
        if (!isset($restrictionParams[$deliveryCode])) {
            return true;
        }
        
        if ($restrictionParams[$deliveryCode] === 'Y') {
            return true;
        }

        return false;
    }

    protected static function extractParams(Entity $payment)
    {
        if (!$payment instanceof Payment) {
            return '';
        }

        return $payment->getField('SUM');
    }

    public static function getParamsStructure($entityId = 0)
    {
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        $result = [];

        foreach (DeliveryService::DELIVERY_CODES as $code) {
            $delivery = $deliveryService->getDeliveryByCode($code);
            $result[$code] = [
                'TYPE'  => 'Y/N',
                'VALUE' => 'N',
                'LABEL' => 'Разрешить для доставки ' . $delivery['NAME'],
            ];
        }

        return $result;
    }
}
