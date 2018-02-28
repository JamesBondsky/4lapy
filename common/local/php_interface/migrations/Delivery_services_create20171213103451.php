<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\Table as ServicesTable;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Handler\InnerDeliveryHandler;
use FourPaws\DeliveryBundle\Handler\InnerPickupHandler;

class Delivery_services_create20171213103451 extends SprintMigrationBase
{
    protected $description = "Создание сервисов доставки и самовывоза";

    protected $defaultFields = [
        'CODE'                => null,
        'PARENT_ID'           => null,
        'NAME'                => '',
        'ACTIVE'              => 'Y',
        'DESCRIPTION'         => '',
        'SORT'                => 100,
        'LOGOTIP'             => null,
        'CONFIG'              => null,
        'CLASS_NAME'          => null,
        'CURRENCY'            => 'RUB',
        'ALLOW_EDIT_SHIPMENT' => 'Y',
    ];

    protected $deliveries = [
        '4lapy_delivery' => [
            'CLASS_NAME' => InnerDeliveryHandler::class,
            'CONFIG'     => [
                'MAIN'      => [
                    'CURRENCY' => 'RUB',
                ],
                'PRICES'    => [
                    '0000293598' => '150',
                    'ZONE_1'     => '200',
                    'ZONE_2'     => '150',
                ],
                'FREE_FROM' => [
                    '0000293598' => '700',
                    'ZONE_1'     => '2000',
                    'ZONE_2'     => '500',
                ],
            ],
        ],
        '4lapy_pickup'   => [
            'CLASS_NAME' => InnerPickupHandler::class,
            'CONFIG'     => [
                'MAIN' => [
                    'CURRENCY' => 'RUB',
                ],
            ],
        ],
    ];

    protected $restrictions = [
        '4lapy_delivery' => [
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
                        'LOCATION_CODE' => '0000293598',
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_LOCATION,
                    ],
                ],
            ],
        ],
        '4lapy_pickup'   => [
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
                ],
            ],
        ],
    ];

    protected $parentName = 'Актуальные группы доставки';

    public function up()
    {
        $groupId = Manager::getGroupId($this->parentName);
        if (!$groupId) {
            $this->log()->error('Не найдена группа доставок ' . $this->parentName);

            return false;
        }

        $deliveryServices = ServicesTable::getList(
            [
                'filter' => [
                    'CODE' => array_keys($this->deliveries),
                ],
            ]
        );

        while ($deliveryService = $deliveryServices->fetch()) {
            $this->log()->info('Доставка ' . $deliveryService['CODE'] . ' уже существует');
            unset($this->deliveries[$deliveryService['CODE']]);
        }

        foreach ($this->deliveries as $code => $fields) {
            $className = '\\' . $fields['CLASS_NAME'];
            $fields['CLASS_NAME'] = $className;
            $fields = array_merge(
                $this->defaultFields,
                $fields,
                [
                    'NAME'        => $className::getClassTitle(),
                    'DESCRIPTION' => $className::getClassDescription(),
                    'CODE'        => $code,
                    'PARENT_ID'   => $groupId,
                ]
            );
            $addResult = ServicesTable::add($fields);
            if ($addResult->isSuccess()) {
                $this->log()->info('Доставка ' . $code . ' создана');
            } else {
                $this->log()->error('Ошибка при создании доставки ' . $code);

                return false;
            }

            $deliveryId = $addResult->getId();
            if (empty($this->restrictions[$code])) {
                continue;
            }

            foreach ($this->restrictions[$code] as $restriction) {
                $addResult = ServiceRestrictionTable::add(
                    [
                        'SERVICE_ID'   => $deliveryId,
                        'SERVICE_TYPE' => 0,
                        'CLASS_NAME'   => $restriction['CLASS_NAME'],
                    ]
                );

                if (!$addResult->isSuccess()) {
                    $this->log()->warning(
                        'Не удалось добавить группу ограничений для доставки ' . $code . ': ' . implode(
                            ', ',
                            $addResult->getErrorMessages()
                        )
                    );
                    continue;
                } else {
                    $this->log()->info(
                        'Добавлена группа ограничений для доставки ' . $code
                    );
                }

                foreach ($restriction['ITEMS'] as $restrictionItem) {
                    $restrictionItem['DELIVERY_ID'] = $deliveryId;
                    $addResult = DeliveryLocationTable::add($restrictionItem);
                    if (!$addResult->isSuccess()) {
                        $this->log()->warning(
                            'Не удалось добавить ограничение для доставки ' . $code . ': ' . implode(
                                ', ',
                                $addResult->getErrorMessages()
                            )
                        );
                    } else {
                        $this->log()->info(
                            'Добавлено ограничение для доставки ' . $code
                        );
                    }
                }
            }
        }

        return true;
    }

    public function down()
    {
        $deliveryServices = ServicesTable::getList(
            [
                'filter' => [
                    'CODE' => array_keys($this->deliveries),
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

            $restrictionItems = DeliveryLocationTable::getList(
                [
                    'filter' => ['DELIVERY_ID' => $deliveryService['ID']],
                ]
            );
            while ($restrictionItem = $restrictionItems->fetch()) {
                $deleteResult = DeliveryLocationTable::delete($restrictionItem);
                if ($deleteResult->isSuccess()) {
                    $this->log()->info(
                        'Удалено ограничение по местоположению для доставки ' . $deliveryService['CODE']
                    );
                } else {
                    $this->log()->warning(
                        'Не удалось удалить ограничение по местоположению для доставки ' . $deliveryService['CODE']
                    );
                }
            }
        }

        return true;
    }
}
