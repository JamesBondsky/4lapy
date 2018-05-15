<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use Bitrix\Sale\Services\PaySystem\Restrictions\Manager;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Restrictions\PaymentByDeliveryRestriction;
use FourPaws\SaleBundle\Service\OrderService;

class PaymentSystemRestrictions20180328161352 extends SprintMigrationBase
{
    protected $description = 'Задание ограничений платежным системам по службам доставки';

    protected $restrictions = [
        OrderService::PAYMENT_CASH => [
            'CLASS_NAME' => '\\' . PaymentByDeliveryRestriction::class,
            'PARAMS'     => [
                DeliveryService::DPD_PICKUP_CODE     => 'Y',
                DeliveryService::DPD_DELIVERY_CODE   => 'Y',
                DeliveryService::INNER_DELIVERY_CODE => 'N',
                DeliveryService::INNER_PICKUP_CODE   => 'N',
            ],
        ],
    ];

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Exception
     * @return bool
     */
    public function up()
    {
        foreach ($this->restrictions as $paymentCode => $restriction) {
            if (!$payment = PaySystemActionTable::getList(['filter' => ['CODE' => $paymentCode]])->fetch()) {
                $this->log()->error('Не найдена платежная система с кодом ' . $paymentCode);

                return false;
            }

            $result = ServiceRestrictionTable::add(
                [
                    'SERVICE_ID'   => $payment['ID'],
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

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Exception
     * @return bool
     */
    public function down()
    {
        foreach ($this->restrictions as $paymentCode => $deliveryCodes) {
            if (!$payment = PaySystemActionTable::getList(['filter' => ['CODE' => $paymentCode]])->fetch()) {
                $this->log()->error('Не найдена платежная система с кодом ' . $paymentCode);

                return false;
            }

            $restrictions = ServiceRestrictionTable::getList(
                [
                    'filter' => [
                        'SERVICE_ID' => $payment['ID'],
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
