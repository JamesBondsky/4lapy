<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\BitrixUtils;
use Bitrix\Main\ArgumentException;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Delivery\Services\Table as ServicesTable;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use Exception;
use FourPaws\DeliveryBundle\Restrictions\LocationExceptRestriction;
use FourPaws\DeliveryBundle\Service\DeliveryService;

class DpdDeliveryServiceRestrictions20190912164148 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Добавляет в список местоположений исключений зону ZONE_DPD_EXCLUDE';

    protected $restrictions = [
        DeliveryService::DPD_DELIVERY_CODE => [
            [
                DeliveryService::ZONE_DPD_EXCLUDE => BitrixUtils::BX_BOOL_TRUE,
            ],
        ],
        DeliveryService::DPD_PICKUP_CODE => [
            [
                DeliveryService::ZONE_DPD_EXCLUDE => BitrixUtils::BX_BOOL_TRUE,
            ],
        ],
    ];

    /**
     * @return bool
     * @throws ArgumentException
     * @throws Exception
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
            foreach ($this->restrictions[$deliveryService['CODE']] as $restriction) {
                $restrictions = ServiceRestrictionTable::getList(
                    [
                        'filter' => [
                            'SERVICE_ID' => $deliveryService['ID'],
                        ],
                    ]
                );

                $existRestriction = false;
                while ($dbRestriction = $restrictions->fetch()) {
                    if (strpos($dbRestriction['CLASS_NAME'], LocationExceptRestriction::class)) {
                        $existRestriction = $dbRestriction;
                        break;
                    }
                }

                if ($existRestriction) {
                    $existParams = ($existRestriction['PARAMS'] && !empty($existRestriction['PARAMS'])) ? $existRestriction['PARAMS'] : [];
                    $result = ServiceRestrictionTable::update(
                        $existRestriction['ID'],
                        [
                            'PARAMS' => array_merge($existParams, $restriction)
                        ]
                    );
                } else {
                    $result = ServiceRestrictionTable::add(
                        [
                            'SERVICE_ID' => $deliveryService['ID'],
                            'SERVICE_TYPE' => 0,
                            'CLASS_NAME' => $restriction['CLASS_NAME'],
                            'PARAMS' => $restriction['PARAMS']
                        ]
                    );
                }

                if (!$result->isSuccess()) {
                    $this->log()->warning(
                        \sprintf(
                            'Не удалось добавить группу ограничений для доставки %s: %s',
                            $deliveryService['CODE'],
                            implode(
                                ', ',
                                $result->getErrorMessages()
                            )
                        )
                    );
                    continue;
                }

                $this->log()->info(
                    \sprintf('Добавлена группа ограничений для доставки %s', $deliveryService['CODE'])
                );
            }
        }

        return true;
    }

    public function down()
    {
        return true;
    }
}
