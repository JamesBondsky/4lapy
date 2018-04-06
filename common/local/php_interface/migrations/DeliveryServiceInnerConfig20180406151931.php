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
use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;
use FourPaws\DeliveryBundle\Handler\InnerDeliveryHandler;
use FourPaws\DeliveryBundle\Handler\InnerPickupHandler;
use FourPaws\DeliveryBundle\Service\DeliveryService;

class DeliveryServiceInnerConfig20180406151931 extends SprintMigrationBase
{
    protected $description = 'Задание интервалов собственной доставки';

    protected $deliveries = [
        DeliveryService::INNER_DELIVERY_CODE => [
            'CLASS_NAME' => InnerDeliveryHandler::class,
            'CONFIG' => [
                'MAIN' => [
                    'CURRENCY' => 'RUB',
                    'INTERVALS' => [
                        [
                            'ZONE_CODE' => 'ZONE_1',
                            'INTERVALS' => [
                                [
                                    'FROM' => '9',
                                    'TO' => '18',
                                    'RULES' => [
                                        'ADD_DAYS' => [
                                            0 => '1',
                                            1 => '1',
                                            2 => '2',
                                        ],
                                    ],
                                ],
                                [
                                    'FROM' => '15',
                                    'TO' => '21',
                                    'RULES' => [
                                        'ADD_DAYS' => [
                                            0 => '1',
                                            1 => '1',
                                            2 => '2',
                                        ],
                                    ],
                                ],
                                [
                                    'FROM' => '18',
                                    'TO' => '23',
                                    'RULES' => [
                                        'ADD_DAYS' => [
                                            0 => '0',
                                            1 => '1',
                                            2 => '1',
                                        ],
                                    ],
                                ],
                            ],
                            'RULES' => [
                                'ADD_DAYS' => [
                                    [
                                        'FROM' => '0',
                                        'TO' => '14',
                                    ],
                                    [
                                        'FROM' => '14',
                                        'TO' => '17',
                                    ],
                                    [
                                        'FROM' => '17',
                                        'TO' => '0',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'ZONE_CODE' => 'ZONE_2',
                            'INTERVALS' => [
                                [
                                    'FROM' => '8',
                                    'TO' => '12',
                                    'RULES' => [
                                        'ADD_DAYS' => [
                                            0 => '1',
                                            1 => '1',
                                            2 => '2',
                                        ],
                                    ],
                                ],
                                [
                                    'FROM' => '12',
                                    'TO' => '16',
                                    'RULES' => [
                                        'ADD_DAYS' => [
                                            0 => '1',
                                            1 => '1',
                                            2 => '1',
                                        ],
                                    ],
                                ],
                                [
                                    'FROM' => '16',
                                    'TO' => '20',
                                    'RULES' => [
                                        'ADD_DAYS' => [
                                            0 => '0',
                                            1 => '1',
                                            2 => '1',
                                        ],
                                    ],
                                ],
                                [
                                    'FROM' => '20',
                                    'TO' => '0',
                                    'RULES' => [
                                        'ADD_DAYS' => [
                                            0 => '0',
                                            1 => '1',
                                            2 => '1',
                                        ],
                                    ],
                                ],
                            ],
                            'RULES' => [
                                'ADD_DAYS' => [
                                    [
                                        'FROM' => '0',
                                        'TO' => '14',
                                    ],
                                    [
                                        'FROM' => '14',
                                        'TO' => '20',
                                    ],
                                    [
                                        'FROM' => '20',
                                        'TO' => '0',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'PRICES' => [
                    '0000263227' => '150',
                    'ZONE_1' => '200',
                    'ZONE_2' => '150',
                ],
                'FREE_FROM' => [
                    '0000263227' => '700',
                    'ZONE_1' => '2000',
                    'ZONE_2' => '500',
                ],
            ],
        ]
    ];

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
                    [
                        'LOCATION_CODE' => '0000263227',
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_LOCATION,
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
                    'CODE' => array_keys($this->deliveries),
                ],
            ]
        );

        while ($deliveryService = $deliveryServices->fetch()) {
            $updateResult = ServicesTable::update($deliveryService['ID'], $this->deliveries[$deliveryService['CODE']]);
            if (!$updateResult->isSuccess()) {
                $this->log()->error('Не удалось обновить доставку ' . $deliveryService['CODE']);

                return false;
            }


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
