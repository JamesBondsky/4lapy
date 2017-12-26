<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Delivery\Services\Table as DeliveryServiceTable;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use FourPaws\DeliveryBundle\Service\DeliveryService;

class Delivery_dpd_add_codes_restrictions20171218112210 extends SprintMigrationBase
{
    protected $description = 'Добавление ограничений для доставок DPD';

    protected $restrictions = [
        DeliveryService::DPD_DELIVERY_CODE => [
            [
                'CLASS_NAME' => '\Bitrix\Sale\Delivery\Restrictions\ByLocation',
                'ITEMS'      => [
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_1,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_2,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_3,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_4,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                ],
            ],
            [
                'CLASS_NAME' => '\FourPaws\DeliveryBundle\Restrictions\LocationExceptRestriction',
                'PARAMS'     => [
                    'ZONE_1' => 'Y',
                    'ZONE_2' => 'Y',
                    'ZONE_3' => 'N',
                    'ZONE_4' => 'N',
                ],
            ],
        ],
        DeliveryService::DPD_PICKUP_CODE   => [
            [
                'CLASS_NAME' => '\FourPaws\DeliveryBundle\Restrictions\LocationExceptRestriction',
                'PARAMS'     => [
                    'ZONE_1' => 'Y',
                    'ZONE_2' => 'Y',
                    'ZONE_3' => 'Y',
                    'ZONE_4' => 'N',
                ],
            ],
            [
                'CLASS_NAME' => '\Bitrix\Sale\Delivery\Restrictions\ByLocation',
                'ITEMS'      => [
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_1,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_2,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_3,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_4,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                ],
            ],
        ],
    ];

    public function up()
    {
        $deliveries = DeliveryServiceTable::getList(
            [
                'filter' => [
                    'CODE' => [
                        DeliveryService::DPD_PICKUP_CODE,
                        DeliveryService::DPD_DELIVERY_CODE,
                    ],
                ],
            ]
        );

        while ($delivery = $deliveries->fetch()) {
            foreach ($this->restrictions[$delivery['CODE']] as $restriction) {
                $addResult = ServiceRestrictionTable::add(
                    [
                        'SERVICE_ID'   => $delivery['ID'],
                        'SERVICE_TYPE' => 0,
                        'CLASS_NAME'   => $restriction['CLASS_NAME'],
                        'PARAMS'       => $restriction['PARAMS'],
                    ]
                );

                if (!$addResult->isSuccess()) {
                    $this->log()->warning(
                        'Не удалось добавить группу ограничений для доставки ' . $delivery['CODE'] . ': ' . implode(
                            ', ',
                            $addResult->getErrorMessages()
                        )
                    );
                    continue;
                } else {
                    $this->log()->info(
                        'Добавлена группа ограничений для доставки ' . $delivery['CODE']
                    );
                }

                foreach ($restriction['ITEMS'] as $restrictionItem) {
                    $restrictionItem['DELIVERY_ID'] = $delivery['ID'];
                    $addResult = DeliveryLocationTable::add($restrictionItem);
                    if (!$addResult->isSuccess()) {
                        $this->log()->warning(
                            'Не удалось добавить ограничение для доставки ' . $delivery['CODE'] . ': ' . implode(
                                ', ',
                                $addResult->getErrorMessages()
                            )
                        );
                    } else {
                        $this->log()->info(
                            'Добавлено ограничение для доставки ' . $delivery['CODE']
                        );
                    }
                }
            }
        }
    }

    public function down()
    {
        $deliveries = DeliveryServiceTable::getList(
            [
                'filter' => [
                    'CODE' => [
                        DeliveryService::DPD_PICKUP_CODE,
                        DeliveryService::DPD_DELIVERY_CODE,
                    ],
                ],
            ]
        );

        while ($delivery = $deliveries->fetch()) {
            $restrictions = ServiceRestrictionTable::getList(
                [
                    'filter' => [
                        'SERVICE_ID' => $delivery['ID'],
                    ],
                ]
            );

            while ($restriction = $restrictions->fetch()) {
                $deleteResult = ServiceRestrictionTable::delete($restriction['ID']);
                if (!$deleteResult->isSuccess()) {
                    $this->log()->warning(
                        'Не удалось удалить группу ограничений для доставки ' . $delivery['CODE'] . ': ' . implode(
                            ', ',
                            $deleteResult->getErrorMessages()
                        )
                    );
                } else {
                    $this->log()->info(
                        'Удалена группа ограничений для доставки ' . $delivery['CODE']
                    );
                }
            }

            $locationRestrictions = DeliveryLocationTable::getList(
                [
                    'filter' => [
                        'DELIVERY_ID' => $delivery['ID'],
                    ],
                ]
            );

            while ($locationRestriction = $locationRestrictions->fetch()) {
                $deleteResult = DeliveryLocationTable::delete($locationRestriction);
                if ($deleteResult->isSuccess()) {
                    $this->log()->info(
                        'Удалены ограничения по местоположению для доставки ' . $delivery['CODE']
                    );
                } else {
                    $this->log()->warning(
                        'Не удалось удалить ограничения по местоположению для доставки ' . $delivery['CODE']
                    );
                }
            }
        }
    }
}
