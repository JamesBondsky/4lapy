<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Delivery\Services\Table as ServicesTable;
use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;
use FourPaws\DeliveryBundle\Handler\InnerDeliveryHandler;
use FourPaws\DeliveryBundle\Handler\InnerPickupHandler;
use FourPaws\DeliveryBundle\Service\DeliveryService;

class DeliveryServiceInnerConfig20180221151004 extends SprintMigrationBase
{
    protected $description = 'Задание интервалов собственной доставки';

    protected $deliveries = [
        DeliveryService::INNER_DELIVERY_CODE => [
            'CLASS_NAME' => InnerDeliveryHandler::class,
            'CONFIG'     => [
                'MAIN'      => [
                    'CURRENCY'  => 'RUB',
                    'INTERVALS' => [
                        [
                            'ZONE_CODE' => 'ZONE_1',
                            'INTERVALS' => [
                                [
                                    'FROM'  => '9',
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
                                    'FROM'  => '15',
                                    'TO'    => '21',
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
                                    'TO'    => '23',
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
                            'ZONE_CODE' => 'ZONE_2',
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
                    ],
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
        DeliveryService::INNER_PICKUP_CODE   => [
            'CLASS_NAME' => InnerPickupHandler::class,
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
