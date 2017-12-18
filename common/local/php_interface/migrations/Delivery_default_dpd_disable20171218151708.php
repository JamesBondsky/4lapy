<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Delivery\Services\Table as DeliveryServicesTable;
use FourPaws\DeliveryBundle\Service\DeliveryService;

class Delivery_default_dpd_disable20171218151708 extends SprintMigrationBase
{
    protected $description = 'Отключение дефолтной доставки DPD';

    public function up()
    {
        $deliveries = DeliveryServicesTable::getList(
            [
                'filter' => [
                    '%NAME' => 'DPD',
                    '!CODE' => [
                        DeliveryService::DPD_DELIVERY_GROUP_CODE,
                        DeliveryService::DPD_PICKUP_CODE,
                        DeliveryService::DPD_DELIVERY_CODE,
                    ],
                ],
            ]
        );

        while ($delivery = $deliveries->fetch()) {
            $updateResult = DeliveryServicesTable::update($delivery['ID'], ['ACTIVE' => 'N']);
            if (!$updateResult->isSuccess()) {
                $this->log()->error('Не удалось деактивировать доставку ' . $delivery['NAME']);

                return false;
            } else {
                $this->log()->info('Доставка ' . $delivery['NAME'] . ' деактивирована');
            }
        }

        return true;
    }

    public function down()
    {
        $deliveries = DeliveryServicesTable::getList(
            [
                'filter' => [
                    '%NAME' => 'DPD',
                    '!CODE' => [
                        DeliveryService::DPD_DELIVERY_GROUP_CODE,
                        DeliveryService::DPD_PICKUP_CODE,
                        DeliveryService::DPD_DELIVERY_CODE,
                    ],
                ],
            ]
        );

        while ($delivery = $deliveries->fetch()) {
            $updateResult = DeliveryServicesTable::update($delivery['ID'], ['ACTIVE' => 'Y']);
            if (!$updateResult->isSuccess()) {
                $this->log()->error('Не удалось активировать доставку ' . $delivery['NAME']);

                return false;
            } else {
                $this->log()->info('Доставка ' . $delivery['NAME'] . ' активирована');
            }
        }

        return true;
    }
}
