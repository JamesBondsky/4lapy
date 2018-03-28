<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Restrictions;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\Restrictions;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Payment;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Service\OrderStorageService;

class PaymentByDeliveryRestriction extends Restrictions\Base
{
    /**
     * @return string
     */
    public static function getClassTitle(): string
    {
        return 'Ограничение по службам доставки, которое работает';
    }

    /**
     * @return string
     */
    public static function getClassDescription(): string
    {
        return 'способ оплаты будет доступен для указанных доставок';
    }

    /**
     * @param $sum
     * @param array $restrictionParams
     * @param int $paySystemId
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @return bool
     */
    public static function check($sum, array $restrictionParams, $paySystemId = 0): bool
    {
        if (!$paySystemId) {
            return false;
        }

        /** @var OrderStorageService $orderStorageService */
        $orderStorageService = Application::getInstance()->getContainer()->get(OrderStorageService::class);
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        $orderStorage = $orderStorageService->getStorage();
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

    /**
     * @param Entity $payment
     *
     * @return mixed|null|string
     */
    protected static function extractParams(Entity $payment)
    {
        if (!$payment instanceof Payment) {
            return '';
        }

        return $payment->getField('SUM');
    }

    /**
     * @param int $entityId
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @return array
     */
    public static function getParamsStructure($entityId = 0): array
    {
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        $result = [];

        $deliveryCodes = \array_merge(DeliveryService::DELIVERY_CODES, DeliveryService::PICKUP_CODES);
        foreach ($deliveryCodes as $code) {
            $delivery = $deliveryService->getDeliveryByCode($code);
            $result[$code] = [
                'TYPE' => 'Y/N',
                'VALUE' => 'N',
                'LABEL' => 'Разрешить для доставки ' . $delivery['NAME'],
            ];
        }

        return $result;
    }
}
