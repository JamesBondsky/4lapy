<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use Bitrix\Sale\Services\PaySystem\Restrictions\Manager;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Restrictions\PaymentByDeliveryRestriction;
use FourPaws\SaleBundle\Service\OrderService;

class Payment_system_restrictions20180205114524 extends SprintMigrationBase
{
    protected $description = 'Задание ограничений платежным системам по службам доставки';

    protected $restrictions = [
        OrderService::PAYMENT_CARD => [
            'CLASS_NAME' => '\\' . PaymentByDeliveryRestriction::class,
            'PARAMS'     => [
                DeliveryService::DPD_PICKUP_CODE     => 'Y',
                DeliveryService::DPD_DELIVERY_CODE   => 'Y',
                DeliveryService::INNER_DELIVERY_CODE => 'N',
                DeliveryService::INNER_PICKUP_CODE   => 'N',
            ],
        ],
    ];

    public function up()
    {
        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);

        foreach ($this->restrictions as $paymentCode => $restriction) {
            $paymentId = $orderService->getPaymentIdByCode($paymentCode);

            $result = ServiceRestrictionTable::add(
                [
                    'SERVICE_ID'   => $paymentId,
                    'SERVICE_TYPE' => Manager::SERVICE_TYPE_PAYMENT,
                    'CLASS_NAME'   => $restriction['CLASS_NAME'],
                    'PARAMS'       => $restriction['PARAMS'],
                ]
            );

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
        foreach ($this->restrictions as $paymentCode => $deliveryCodes) {
            $paymentId = $orderService->getPaymentIdByCode($paymentCode);

            $restrictions = ServiceRestrictionTable::getList(
                [
                    'filter' => [
                        'SERVICE_ID' => $paymentId,
                    ],
                ]
            );

            while ($restriction = $restrictions->fetch()) {
                $result = ServiceRestrictionTable::delete($restriction['ID']);
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
