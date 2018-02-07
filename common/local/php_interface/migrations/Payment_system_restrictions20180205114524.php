<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use Bitrix\Sale\Services\PaySystem\Restrictions\Delivery as DeliveryRestriction;
use Bitrix\Sale\Services\PaySystem\Restrictions\Manager;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Service\OrderService;

class Payment_system_restrictions20180205114524 extends SprintMigrationBase
{
    protected $description = 'Задание ограничений платежным системам по службам доставки';

    protected $payToDelivery = [
        OrderService::PAYMENT_CARD => [
            DeliveryService::DPD_PICKUP_CODE,
            DeliveryService::DPD_DELIVERY_CODE,
        ],
    ];

    public function up()
    {
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);

        foreach ($this->payToDelivery as $paymentCode => $deliveryCodes) {
            $paymentId = $orderService->getPaymentIdByCode($paymentCode);

            $deliveryIds = [];
            foreach ($deliveryCodes as $deliveryCode) {
                $deliveryIds[] = $deliveryService->getDeliveryIdByCode($deliveryCode);
            }

            $fields = [
                'SERVICE_ID'   => $paymentId,
                'SERVICE_TYPE' => Manager::SERVICE_TYPE_PAYMENT,
                'SORT'         => 10,
                'PARAMS'       => ['DELIVERY' => $deliveryIds],
            ];

            $result = DeliveryRestriction::save($fields);
            if (!$result->isSuccess()) {
                $this->log()->error(
                    sprintf(
                        'Ошибка при задании ограничений платежной системы %s: ' . implode(
                            ',',
                            $result->getErrorMessages()
                        )
                    ),
                    $paymentCode
                );

                return false;
            }

            $this->log()->info(sprintf('Заданы ограничения для платежной системы %s', $paymentCode));
        }

        return true;
    }

    public function down()
    {
        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);
        foreach ($this->payToDelivery as $paymentCode => $deliveryCodes) {
            $paymentId = $orderService->getPaymentIdByCode($paymentCode);

            $restrictions = ServiceRestrictionTable::getList(
                [
                    'filter' => [
                        'SERVICE_ID'   => $paymentId,
                        'SERVICE_TYPE' => Manager::SERVICE_TYPE_PAYMENT,
                    ],
                ]
            );

            $class = '\\' . DeliveryRestriction::class;
            while ($restriction = $restrictions->fetch()) {
                if ($restriction['CLASS_NAME'] !== $class) {
                    continue;
                }
                $result = DeliveryRestriction::delete($restriction['ID'], $paymentId);
                if (!$result->isSuccess()) {
                    $this->log()->error(
                        sprintf(
                            'Ошибка при удалении ограничений платежной системы %s: ' . implode(
                                ',',
                                $result->getErrorMessages()
                            )
                        ),
                        $paymentCode
                    );

                    return false;
                }

                $this->log()->info(sprintf('Удалены ограничения для платежной системы %s', $paymentCode));
            }
        }

        return true;
    }
}
