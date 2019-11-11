<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\Table as ServicesTable;
use Exception;
use FourPaws\DeliveryBundle\Handler\ExpressDeliveryHandler;

class ExpressDelivery4Lapy20191111161811 extends SprintMigrationBase
{
    protected $description = 'Экспресс доставка "4 лапы"';

    protected $defaultFields = [
        'CODE' => null,
        'PARENT_ID' => null,
        'NAME' => '',
        'ACTIVE' => 'Y',
        'DESCRIPTION' => '',
        'SORT' => 100,
        'LOGOTIP' => null,
        'CONFIG' => null,
        'CLASS_NAME' => null,
        'CURRENCY' => 'RUB',
        'ALLOW_EDIT_SHIPMENT' => 'Y',
    ];

    protected $expressDeliveryCode = '4lapy_express';

    protected $expressDelivery = [
        'NAME' => 'Экспресс доставка "4 лапы"',
        'DESCRIPTION' => '',
        'CLASS_NAME' => ExpressDeliveryHandler::class,
        'CONFIG' => [
            'MAIN' => [
                'CURRENCY' => 'RUB',
                'PRICE' => 0,
                'PERIOD' => [
                    'FROM' => 0,
                    'TO' => 0
                ]
            ]
        ]
    ];

    protected $parentName = 'Актуальные группы доставки';

    /**
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function up(): bool
    {
        $groupId = Manager::getGroupId($this->parentName);
        if (!$groupId) {
            $this->log()->error('Не найдена группа доставок ' . $this->parentName);

            return false;
        }

        if ($deliveryService = ServicesTable::getList(['filter' => ['CODE' => [$this->expressDeliveryCode]]])->fetch()) {
            $this->log()->info('Доставка ' . $deliveryService['CODE'] . ' уже существует');
            return false;
        }

        /** @var ExpressDeliveryHandler $className */
        $className = '\\' . $this->expressDelivery['CLASS_NAME'];
        $this->expressDelivery['CLASS_NAME'] = $className;
        $this->expressDelivery = array_merge(
            $this->defaultFields,
            [
                'NAME' => $className::getClassTitle(),
                'DESCRIPTION' => $className::getClassDescription(),
                'CODE' => $this->expressDeliveryCode,
                'PARENT_ID' => $groupId,
            ],
            $this->expressDelivery
        );
        $addResult = ServicesTable::add($this->expressDelivery);
        if ($addResult->isSuccess()) {
            $this->log()->info('Доставка ' . $this->expressDeliveryCode . ' создана');
        } else {
            $this->log()->error('Ошибка при создании доставки ' . $this->expressDeliveryCode);
            return false;
        }
        return true;
    }

    /**
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function down(): bool
    {
        $deliveryServices = ServicesTable::getList(
            [
                'filter' => [
                    'CODE' => [
                        $this->expressDeliveryCode
                    ]
                ],
            ]
        );

        while ($deliveryService = $deliveryServices->fetch()) {
            $deleteResult = ServicesTable::delete($deliveryService['ID']);
            if ($deleteResult->isSuccess()) {
                $this->log()->info('Удалена доставка ' . $deliveryService['CODE']);
            } else {
                $this->log()->error('Ошибка при удалении ' . $deliveryService['CODE']);
                return false;
            }
        }

        return true;
    }
}
