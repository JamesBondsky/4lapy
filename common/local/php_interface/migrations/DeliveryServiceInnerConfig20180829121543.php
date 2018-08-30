<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Delivery\Services\Table as ServicesTable;
use FourPaws\DeliveryBundle\Handler\InnerDeliveryHandler;
use FourPaws\DeliveryBundle\Service\DeliveryService;

class DeliveryServiceInnerConfig20180829121543 extends SprintMigrationBase
{
    protected $description = 'Обновление конфигурации собственной службы доставки (для зон 5 и 6)';

    protected $deliveries = [
        DeliveryService::INNER_DELIVERY_CODE => [
            'CLASS_NAME' => InnerDeliveryHandler::class,
            'CONFIG'     => [
                'MAIN'      => [
                    'CURRENCY'  => 'RUB',
                    'INTERVALS' => [
                        [
                            'ZONE_CODE' => DeliveryService::ZONE_1,
                            'INTERVALS' => [
                                [
                                    'FROM'  => '10',
                                    'TO'    => '14',
                                    'RULES' => [
                                        'ADD_DAYS' => [
                                            0 => '1',
                                            1 => '1',
                                            2 => '2',
                                        ],
                                    ],
                                ],
                                [
                                    'FROM'  => '14',
                                    'TO'    => '18',
                                    'RULES' => [
                                        'ADD_DAYS' => [
                                            0 => '1',
                                            1 => '1',
                                            2 => '2',
                                        ],
                                    ],
                                ],
                                [
                                    'FROM'  => '18',
                                    'TO'    => '22',
                                    'RULES' => [
                                        'ADD_DAYS' => [
                                            0 => '0',
                                            1 => '1',
                                            2 => '1',
                                        ],
                                    ],
                                ],
                                [
                                    'FROM'  => '22',
                                    'TO'    => '00',
                                    'RULES' => [
                                        'ADD_DAYS' => [
                                            0 => '0',
                                            1 => '1',
                                            2 => '1',
                                        ],
                                    ],
                                ],
                            ],
                            'RULES'     => [
                                'ADD_DAYS' => [
                                    [
                                        'FROM' => '0',
                                        'TO'   => '14',
                                    ],
                                    [
                                        'FROM' => '14',
                                        'TO'   => '17',
                                    ],
                                    [
                                        'FROM' => '17',
                                        'TO'   => '0',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'ZONE_CODE' => DeliveryService::ZONE_2,
                            'INTERVALS' => [
                                [
                                    'FROM'  => '8',
                                    'TO'    => '12',
                                    'RULES' => [
                                        'ADD_DAYS' => [
                                            0 => '1',
                                            1 => '1',
                                            2 => '2',
                                        ],
                                    ],
                                ],
                                [
                                    'FROM'  => '12',
                                    'TO'    => '16',
                                    'RULES' => [
                                        'ADD_DAYS' => [
                                            0 => '1',
                                            1 => '1',
                                            2 => '1',
                                        ],
                                    ],
                                ],
                                [
                                    'FROM'  => '16',
                                    'TO'    => '20',
                                    'RULES' => [
                                        'ADD_DAYS' => [
                                            0 => '0',
                                            1 => '1',
                                            2 => '1',
                                        ],
                                    ],
                                ],
                                [
                                    'FROM'  => '20',
                                    'TO'    => '0',
                                    'RULES' => [
                                        'ADD_DAYS' => [
                                            0 => '0',
                                            1 => '1',
                                            2 => '1',
                                        ],
                                    ],
                                ],
                            ],
                            'RULES'     => [
                                'ADD_DAYS' => [
                                    [
                                        'FROM' => '0',
                                        'TO'   => '14',
                                    ],
                                    [
                                        'FROM' => '14',
                                        'TO'   => '20',
                                    ],
                                    [
                                        'FROM' => '20',
                                        'TO'   => '0',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'ZONE_CODE' => DeliveryService::ZONE_5,
                            'INTERVALS' => [
                                [
                                    'FROM'  => '8',
                                    'TO'    => '12',
                                    'RULES' => [
                                        'ADD_DAYS' => [],
                                    ],
                                ],
                                [
                                    'FROM'  => '12',
                                    'TO'    => '16',
                                    'RULES' => [
                                        'ADD_DAYS' => [],
                                    ],
                                ],
                                [
                                    'FROM'  => '16',
                                    'TO'    => '20',
                                    'RULES' => [
                                        'ADD_DAYS' => [],
                                    ],
                                ],
                                [
                                    'FROM'  => '20',
                                    'TO'    => '0',
                                    'RULES' => [],
                                ],
                            ],
                            'RULES'     => [],
                        ],
                        [
                            'ZONE_CODE' => DeliveryService::ZONE_6,
                            'INTERVALS' => [
                                [
                                    'FROM'  => '10',
                                    'TO'    => '18',
                                    'RULES' => [
                                        'ADD_DAYS' => [],
                                    ],
                                ],
                                [
                                    'FROM'  => '18',
                                    'TO'    => '0',
                                    'RULES' => [
                                        'ADD_DAYS' => [],
                                    ],
                                ],
                            ],
                            'RULES'     => [],
                        ],
                    ],
                ],
                'PRICES'    => [
                    '0000263227'            => '150',
                    DeliveryService::ZONE_1 => '200',
                    DeliveryService::ZONE_2 => '150',
                    DeliveryService::ZONE_5 => '199',
                    DeliveryService::ZONE_6 => '399',
                ],
                'FREE_FROM' => [
                    '0000263227'            => '700',
                    DeliveryService::ZONE_1 => '2000',
                    DeliveryService::ZONE_2 => '500',
                    DeliveryService::ZONE_5 => '1999',
                    DeliveryService::ZONE_6 => '3999',
                ],
                'DAYS'      => [
                    DeliveryService::ZONE_1 =>
                        [
                            0 => '0',
                            1 => '1',
                            2 => '2',
                            3 => '3',
                            4 => '4',
                            5 => '5',
                            6 => '6',
                        ],
                    DeliveryService::ZONE_2 =>
                        [
                            0 => '0',
                            1 => '1',
                            2 => '2',
                            3 => '3',
                            4 => '4',
                            5 => '5',
                            6 => '6',
                        ],
                    DeliveryService::ZONE_5 =>
                        [
                            0 => '0',
                            1 => '1',
                            2 => '2',
                            3 => '3',
                            4 => '4',
                            5 => '5',
                            6 => '6',
                        ],
                    DeliveryService::ZONE_6 =>
                        [
                            0 => '2',
                            1 => '5',
                        ],
                ],
            ],
        ],
    ];

    /**
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
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
        }

        return true;
    }

    public function down()
    {
        return true;
    }
}
