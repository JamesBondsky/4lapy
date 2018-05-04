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

class PaymentSystemRestrictions20180503183809 extends SprintMigrationBase
{
    protected $description = 'Удаление ограничений у платежных систем по службам доставки';

    protected $restrictions = [
        OrderService::PAYMENT_CASH
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
        foreach ($this->restrictions as $paymentCode) {
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

    public function down()
    {
    }
}
