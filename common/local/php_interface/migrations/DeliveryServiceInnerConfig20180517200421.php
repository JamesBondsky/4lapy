<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Delivery\Restrictions\ByLocation;
use Bitrix\Sale\Delivery\Services\Table as ServicesTable;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use FourPaws\DeliveryBundle\Service\DeliveryService;

class DeliveryServiceInnerConfig20180517200421 extends SprintMigrationBase
{
    protected $description = 'Задание интервалов собственной доставки';

    protected $restrictions = [
        '4lapy_delivery' => [
            [
                'CLASS_NAME' => ByLocation::class,
                'ITEMS' => [
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_1,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_2,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                ],
            ],
        ],
    ];

    public function up()
    {
        $deliveryServices = ServicesTable::getList(
            [
                'filter' => [
                    'CODE' => array_keys($this->restrictions),
                ],
            ]
        );

        while ($deliveryService = $deliveryServices->fetch()) {
            $restrictions = ServiceRestrictionTable::getList(
                [
                    'filter' => [
                        'SERVICE_ID' => $deliveryService['ID'],
                    ],
                ]
            );
            while ($restriction = $restrictions->fetch()) {
                $deleteResult = ServiceRestrictionTable::delete($restriction['ID']);

                if ($deleteResult->isSuccess()) {
                    $this->log()->info(
                        'Удалена группа ограничений для доставки ' . $deliveryService['CODE']
                    );
                } else {
                    $this->log()->warning(
                        'Не удалось удалить группу ограничений для доставки ' . $deliveryService['CODE'] . ': ' . implode(
                            ', ',
                            $deleteResult->getErrorMessages()
                        )
                    );
                }
            }

            foreach ($this->restrictions[$deliveryService['CODE']] as $restriction) {
                $addResult = ServiceRestrictionTable::add(
                    [
                        'SERVICE_ID' => $deliveryService['ID'],
                        'SERVICE_TYPE' => 0,
                        'CLASS_NAME' => $restriction['CLASS_NAME'],
                    ]
                );

                if (!$addResult->isSuccess()) {
                    $this->log()->warning(
                        'Не удалось добавить группу ограничений для доставки ' . $deliveryService['CODE'] . ': ' . implode(
                            ', ',
                            $addResult->getErrorMessages()
                        )
                    );
                    continue;
                } else {
                    $this->log()->info(
                        'Добавлена группа ограничений для доставки ' . $deliveryService['CODE']
                    );
                }

                if (!empty($restriction['ITEMS'])) {
                    $restrictionItems = DeliveryLocationTable::getList([
                        'filter' => ['DELIVERY_ID' => $deliveryService['ID']]
                    ]);
                    while ($restrictionItem = $restrictionItems->fetch()) {
                        $deleteResult = DeliveryLocationTable::delete($restrictionItem);
                        if (!$deleteResult->isSuccess()) {
                            $this->log()->warning(
                                'Не удалось удалить ограничение для доставки ' . $deliveryService['CODE'] . ': ' . implode(
                                    ', ',
                                    $addResult->getErrorMessages()
                                )
                            );
                        }
                    }
                }

                foreach ($restriction['ITEMS'] as $restrictionItem) {
                    $restrictionItem['DELIVERY_ID'] = $deliveryService['ID'];
                    $addResult = DeliveryLocationTable::add($restrictionItem);
                    if (!$addResult->isSuccess()) {
                        $this->log()->warning(
                            'Не удалось добавить ограничение для доставки ' . $deliveryService['CODE'] . ': ' . implode(
                                ', ',
                                $addResult->getErrorMessages()
                            )
                        );
                    } else {
                        $this->log()->info(
                            'Добавлено ограничение для доставки ' . $deliveryService['CODE']
                        );
                    }
                }
            }

            unset($this->deliveries[$deliveryService['CODE']]);
        }

        if (!empty($this->deliveries)) {
            $this->log()->error('Не найдены доставки: ' . implode(', ', array_keys($this->deliveries)));

            return false;
        }

        return true;
    }

    public function down()
    {
        return true;
    }
}
