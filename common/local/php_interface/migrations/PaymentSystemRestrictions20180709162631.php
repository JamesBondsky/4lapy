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
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Restrictions\PaymentByDeliveryRestriction;
use FourPaws\SaleBundle\Service\OrderService;

class PaymentSystemRestrictions20180709162631 extends SprintMigrationBase
{
    protected $description = 'Задание ограничений платежным системам по службам доставки';

    protected $restrictions = [
        OrderPayment::PAYMENT_CASH_OR_CARD => [
            'CLASS_NAME' => '\\' . PaymentByDeliveryRestriction::class,
            'PARAMS'     => [
                DeliveryService::DPD_PICKUP_CODE     => 'Y',
                DeliveryService::DPD_DELIVERY_CODE   => 'Y',
                DeliveryService::INNER_DELIVERY_CODE => 'Y',
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

            $existingRestrictions = ServiceRestrictionTable::getList(
                [
                    'filter' => [
                        'SERVICE_ID' => $payment['ID'],
                    ],
                ]
            );

            while ($existingRestriction = $existingRestrictions->fetch()) {
                $result = ServiceRestrictionTable::delete($existingRestriction['ID']);
                if (!$result->isSuccess()) {
                    $this->log()->error(
                        sprintf(
                            'Ошибка при удалении ограничений платежной системы %s: %s',
                            $paymentCode,
                            implode(
                                ',',
                                $result->getErrorMessages()
                            )
                        )
                    );

                    return false;
                }

                $this->log()->info(sprintf('Удалены ограничения для платежной системы %s', $paymentCode));
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
                        'Ошибка при задании ограничений платежной системы %s: %s' ,
                        $paymentCode,
                        implode(',', $result->getErrorMessages())
                    )
                );

                return false;
            }

            $this->log()->info(sprintf('Заданы ограничения для платежной системы %s', $paymentCode));
        }

        return true;
    }

    public function down()
    {

    }
}
