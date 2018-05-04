<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\Services\Automatic as BitrixAutomatic;
use Bitrix\Sale\Delivery\Services\AutomaticProfile as BitrixAutomaticProfile;
use Bitrix\Sale\Delivery\Services\Table as ServicesTable;
use FourPaws\DeliveryBundle\Dpd\Services\Automatic;
use FourPaws\DeliveryBundle\Dpd\Services\AutomaticProfile;

class DeliveryServicesUpdate20180504115732 extends SprintMigrationBase
{
    protected $description = 'Изменение обработчиков у доставок DPD';

    protected $fields = [
        'ipolh_dpd' => [
            'CLASS_NAME' => Automatic::class
        ],
        'ipolh_dpd:PICKUP' => [
            'CLASS_NAME' => AutomaticProfile::class
        ],
        'ipolh_dpd:COURIER' => [
            'CLASS_NAME' => AutomaticProfile::class
        ]
    ];

    protected $oldFields = [
        'ipolh_dpd' => [
            'CLASS_NAME' => BitrixAutomatic::class
        ],
        'ipolh_dpd:PICKUP' => [
            'CLASS_NAME' => BitrixAutomaticProfile::class
        ],
        'ipolh_dpd:COURIER' => [
            'CLASS_NAME' => BitrixAutomaticProfile::class
        ]
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
        $deliveryServices = ServicesTable::getList(
            [
                'filter' => [
                    'CODE' => array_keys($this->fields),
                ],
            ]
        );

        while ($deliveryService = $deliveryServices->fetch()) {
            $updateResult = ServicesTable::update($deliveryService['ID'], $this->fields[$deliveryService['CODE']]);
            if (!$updateResult->isSuccess()) {
                $this->log()->error(sprintf('Ошибка при изменении доставки %s', $deliveryService['CODE']), [
                    'messages' => $updateResult->getErrorMessages()
                ]);

                return false;
            }

            $this->log()->info(sprintf('Обновлена доставка %s', $deliveryService['CODE']));
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
        $deliveryServices = ServicesTable::getList(
            [
                'filter' => [
                    'CODE' => array_keys($this->oldFields),
                ],
            ]
        );

        while ($deliveryService = $deliveryServices->fetch()) {
            $updateResult = ServicesTable::update($deliveryService['ID'], $this->oldFields[$deliveryService['CODE']]);
            if (!$updateResult->isSuccess()) {
                $this->log()->error(sprintf('Ошибка при изменении доставки %s', $deliveryService['CODE']), [
                    'messages' => $updateResult->getErrorMessages()
                ]);

                return false;
            }

            $this->log()->info(sprintf('Обновлена доставка %s', $deliveryService['CODE']));
        }

        return true;
    }
}
