<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\BitrixUtils;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Delivery\Restrictions\ByLocation;
use Bitrix\Sale\Delivery\Services\Table as ServicesTable;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use FourPaws\DeliveryBundle\Restrictions\LocationExceptRestriction;
use FourPaws\DeliveryBundle\Service\DeliveryService;

class DeliveryServiceRestrictions20180829114302 extends SprintMigrationBase
{
    protected $description = 'Обновление ограничений по зонам для доставок';

    protected $restrictions = [
        DeliveryService::INNER_DELIVERY_CODE => [
            [
                'CLASS_NAME' => ByLocation::class,
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
                        'LOCATION_CODE' => DeliveryService::ZONE_5,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_6,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                ],
            ],
        ],
        DeliveryService::INNER_PICKUP_CODE   => [
            [
                'CLASS_NAME' => ByLocation::class,
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
                        'LOCATION_CODE' => DeliveryService::ZONE_5,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_6,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                ],
            ],
        ],
        DeliveryService::DPD_DELIVERY_CODE   => [
            [
                'CLASS_NAME' => ByLocation::class,
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
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_5,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_6,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                ],
            ],
            [
                'CLASS_NAME' => LocationExceptRestriction::class,
                'PARAMS'     => [
                    DeliveryService::ZONE_1 => BitrixUtils::BX_BOOL_TRUE,
                    DeliveryService::ZONE_2 => BitrixUtils::BX_BOOL_TRUE,
                    DeliveryService::ZONE_3 => BitrixUtils::BX_BOOL_FALSE,
                    DeliveryService::ZONE_4 => BitrixUtils::BX_BOOL_FALSE,
                    DeliveryService::ZONE_5 => BitrixUtils::BX_BOOL_TRUE,
                    DeliveryService::ZONE_6 => BitrixUtils::BX_BOOL_TRUE,
                ],
            ],
        ],
        DeliveryService::DPD_PICKUP_CODE     => [
            [
                'CLASS_NAME' => LocationExceptRestriction::class,
                'PARAMS'     => [
                    DeliveryService::ZONE_1 => BitrixUtils::BX_BOOL_TRUE,
                    DeliveryService::ZONE_2 => BitrixUtils::BX_BOOL_TRUE,
                    DeliveryService::ZONE_3 => BitrixUtils::BX_BOOL_TRUE,
                    DeliveryService::ZONE_4 => BitrixUtils::BX_BOOL_FALSE,
                    DeliveryService::ZONE_5 => BitrixUtils::BX_BOOL_TRUE,
                    DeliveryService::ZONE_6 => BitrixUtils::BX_BOOL_TRUE,
                ],
            ],
            [
                'CLASS_NAME' => ByLocation::class,
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
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_5,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                    [
                        'LOCATION_CODE' => DeliveryService::ZONE_6,
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_GROUP,
                    ],
                ],
            ],
        ],
    ];

    /**
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Exception
     */
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
                        \sprintf('Удалена группа ограничений для доставки %s', $deliveryService['CODE'])
                    );
                } else {
                    $this->log()->warning(
                        \sprintf(
                            'Не удалось удалить группу ограничений для доставки %s: %s',
                            $deliveryService['CODE'] . ': ',
                            implode(
                                ', ',
                                $deleteResult->getErrorMessages()
                            )
                        )
                    );
                }
            }

            foreach ($this->restrictions[$deliveryService['CODE']] as $restriction) {
                $addResult = ServiceRestrictionTable::add(
                    [
                        'SERVICE_ID'   => $deliveryService['ID'],
                        'SERVICE_TYPE' => 0,
                        'CLASS_NAME'   => $restriction['CLASS_NAME'],
                        'PARAMS'       => $restriction['PARAMS']
                    ]
                );

                if (!$addResult->isSuccess()) {
                    $this->log()->warning(
                        \sprintf(
                            'Не удалось добавить группу ограничений для доставки %s: %s',
                            $deliveryService['CODE'],
                            implode(
                                ', ',
                                $addResult->getErrorMessages()
                            )
                        )
                    );
                    continue;
                }

                $this->log()->info(
                    \sprintf('Добавлена группа ограничений для доставки %s', $deliveryService['CODE'])
                );

                if (!empty($restriction['ITEMS'])) {
                    $restrictionItems = DeliveryLocationTable::getList([
                        'filter' => ['DELIVERY_ID' => $deliveryService['ID']],
                    ]);
                    while ($restrictionItem = $restrictionItems->fetch()) {
                        $deleteResult = DeliveryLocationTable::delete($restrictionItem);
                        if (!$deleteResult->isSuccess()) {
                            $this->log()->warning(
                                \sprintf(
                                    'Не удалось удалить ограничение для доставки %s: %s',
                                    $deliveryService['CODE'],
                                    implode(
                                        ', ',
                                        $addResult->getErrorMessages()
                                    )
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
                            \sprintf('Не удалось добавить ограничение для доставки %s: %s',
                                $deliveryService['CODE'],
                                implode(
                                    ', ',
                                    $addResult->getErrorMessages()
                                )
                            )
                        );
                    } else {
                        $this->log()->info(
                            \sprintf('Добавлено ограничение для доставки %s', $deliveryService['CODE'])
                        );
                    }
                }
            }
        }

        return true;
    }

    public function down()
    {
        return true;
    }
}
