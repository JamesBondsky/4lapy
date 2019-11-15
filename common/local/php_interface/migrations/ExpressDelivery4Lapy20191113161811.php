<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Delivery\Restrictions\ByLocation;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\Table as ServicesTable;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use Exception;
use FourPaws\DeliveryBundle\Handler\ExpressDeliveryHandler;
use FourPaws\DeliveryBundle\Service\DeliveryService;

class ExpressDelivery4Lapy20191113161811 extends SprintMigrationBase
{
    protected $description = 'Экспресс доставка "4 лапы"';

    protected $defaultFields = [
        'CODE' => null,
        'PARENT_ID' => null,
        'NAME' => '',
        'ACTIVE' => 'Y',
        'DESCRIPTION' => '',
        'SORT' => 2000,
        'LOGOTIP' => null,
        'CONFIG' => null,
        'CLASS_NAME' => null,
        'CURRENCY' => 'RUB',
        'ALLOW_EDIT_SHIPMENT' => 'Y',
    ];

    protected $expressDeliveryCode = '4lapy_express';

    protected $expressDelivery = [
        'NAME' => 'Экспресс доставка "4 лапы"',
        'DESCRIPTION' => 'Обработчик экспресс доставки "Четыре лапы"',
        'CLASS_NAME' => ExpressDeliveryHandler::class,
        'CONFIG' => [
            'MAIN' => [
                'CURRENCY' => 'RUB',
                'PRICE' => 397,
                'FREE_PRICE_FROM' => 3997,
                'PERIOD' => [
                    'FROM' => 45,
                    'TO' => 90
                ]
            ]
        ]
    ];

    protected $parentName = 'Актуальные группы доставки';

    protected $restriction = [
        'CLASS_NAME' => ByLocation::class,
        'ITEMS' => [
            [
                'LOCATION_CODE' => DeliveryService::ZONE_MOSCOW,
                'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
            ],
        ],
    ];

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

        $deliveryServiceId = $addResult->getPrimary()['ID'];

        $addResult = ServiceRestrictionTable::add(
            [
                'SERVICE_ID' => $deliveryServiceId,
                'SERVICE_TYPE' => 0,
                'CLASS_NAME' => $this->restriction['CLASS_NAME'],
            ]
        );

        if (!$addResult->isSuccess()) {
            return false;
        }

        $restrictionItems = DeliveryLocationTable::getList([
            'filter' => ['DELIVERY_ID' => $deliveryServiceId]
        ]);
        while ($restrictionItem = $restrictionItems->fetch()) {
            $deleteResult = DeliveryLocationTable::delete($restrictionItem);
            if (!$deleteResult->isSuccess()) {
                return false;
            }
        }


        foreach ($this->restriction['ITEMS'] as $restrictionItem) {
            $restrictionItem['DELIVERY_ID'] = $deliveryServiceId;
            $addResult = DeliveryLocationTable::add($restrictionItem);
            if (!$addResult->isSuccess()) {
                return false;
            }
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
