<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Location\GroupLocationTable;
use Bitrix\Sale\Location\GroupTable;
use FourPaws\DeliveryBundle\Service\DeliveryService;

class RegionalZonesCreate20181127165700 extends SprintMigrationBase
{
    protected $description = 'Создание и заполнение региональных групп местоположений';

    protected $groups = [
        DeliveryService::ZONE_NIZHNY_NOVGOROD => [
            'NAME' => ['ru' => ['NAME' => 'Нижний Новгород']],
            'SORT' => 1000,
            'CODE' => DeliveryService::ZONE_NIZHNY_NOVGOROD,
            'LOCATIONS' => [
                560520
            ]
        ],
        DeliveryService::ZONE_NIZHNY_NOVGOROD_REGION => [
            'NAME' => ['ru' => ['NAME' => 'Нижегородская область']],
            'SORT' => 1100,
            'CODE' => DeliveryService::ZONE_NIZHNY_NOVGOROD_REGION,
            'LOCATIONS' => [
                564171,
                561787,
                560673
            ]
        ],
        DeliveryService::ZONE_VLADIMIR => [
            'NAME' => ['ru' => ['NAME' => 'Владимир']],
            'SORT' => 1200,
            'CODE' => DeliveryService::ZONE_VLADIMIR,
            'LOCATIONS' => [
                236524
            ]
        ],
        DeliveryService::ZONE_VLADIMIR_REGION => [
            'NAME' => ['ru' => ['NAME' => 'Владимирская область']],
            'SORT' => 1300,
            'CODE' => DeliveryService::ZONE_VLADIMIR_REGION,
            'LOCATIONS' => [
                236538,
                236548,
                236563,
                236681,
                239351,
                239615,
                239862,
                239924,
                239934,
                240015,
                240057
            ]
        ],
        DeliveryService::ZONE_VORONEZH => [
            'NAME' => ['ru' => ['NAME' => 'Воронеж']],
            'SORT' => 1400,
            'CODE' => DeliveryService::ZONE_VORONEZH,
            'LOCATIONS' => [
                217996
            ]
        ],
        DeliveryService::ZONE_VORONEZH_REGION => [
            'NAME' => ['ru' => ['NAME' => 'Воронежская область']],
            'SORT' => 1500,
            'CODE' => DeliveryService::ZONE_VORONEZH_REGION,
            'LOCATIONS' => [
                218956,
                219892,
                220099
            ]
        ],
        DeliveryService::ZONE_YAROSLAVL => [
            'NAME' => ['ru' => ['NAME' => 'Ярославль']],
            'SORT' => 1600,
            'CODE' => DeliveryService::ZONE_YAROSLAVL,
            'LOCATIONS' => [
                187625
            ]
        ],
        DeliveryService::ZONE_YAROSLAVL_REGION => [
            'NAME' => ['ru' => ['NAME' => 'Ярославская область']],
            'SORT' => 1700,
            'CODE' => DeliveryService::ZONE_YAROSLAVL_REGION,
            'LOCATIONS' => [
                187716,
                187750,
                187768,
                187812,
                187837,
                187877,
                187998,
                188108,
                188264,
                188466,
                191143,
                193992
            ]
        ],
        DeliveryService::ZONE_TULA => [
            'NAME' => ['ru' => ['NAME' => 'Тула']],
            'SORT' => 1800,
            'CODE' => DeliveryService::ZONE_TULA,
            'LOCATIONS' => [
                174851
            ]
        ],
        DeliveryService::ZONE_TULA_REGION => [
            'NAME' => ['ru' => ['NAME' => 'Тульская область']],
            'SORT' => 1900,
            'CODE' => DeliveryService::ZONE_TULA_REGION,
            'LOCATIONS' => [
                174864,
                175003,
                175272,
                176750,
                176810,
                177202,
                177251,
                177443,
                177471,
                177560,
                177624,
                177649,
                177656,
                177733,
                177742,
                177751,
                177992,
                178750,
                179199,
                179370,
                179515,
                179536
            ]
        ],
        DeliveryService::ZONE_KALUGA => [
            'NAME' => ['ru' => ['NAME' => 'Калуга']],
            'SORT' => 2000,
            'CODE' => DeliveryService::ZONE_KALUGA,
            'LOCATIONS' => [
                71873
            ]
        ],
        DeliveryService::ZONE_KALUGA_REGION => [
            'NAME' => ['ru' => ['NAME' => 'Калужская область']],
            'SORT' => 2100,
            'CODE' => DeliveryService::ZONE_KALUGA_REGION,
            'LOCATIONS' => [
                71924,
                71931,
                72134,
                72294,
                72323,
                73134,
                73137,
                74012,
                74099,
                74184,
                74830
            ]
        ],
        DeliveryService::ZONE_IVANOVO => [
            'NAME' => ['ru' => ['NAME' => 'Иваново']],
            'SORT' => 2200,
            'CODE' => DeliveryService::ZONE_IVANOVO,
            'LOCATIONS' => [
                60097
            ]
        ],
        DeliveryService::ZONE_IVANOVO_REGION => [
            'NAME' => ['ru' => ['NAME' => 'Ивановская область']],
            'SORT' => 2300,
            'CODE' => DeliveryService::ZONE_IVANOVO_REGION,
            'LOCATIONS' => [
                60253,
                60348,
                60350,
                60359,
                60672
            ]
        ]
    ];


    /**
     * @return bool
     * @throws \Exception
     */
    public function up()
    {
        if (!empty($this->groups)) {
            foreach ($this->groups as $group) {
                $addResult = GroupTable::add(
                    [
                        'NAME' => $group['NAME'],
                        'SORT' => $group['SORT'],
                        'CODE' => $group['CODE']
                    ]
                );
                if (!$addResult->isSuccess()) {
                    $this->log()->error(
                        \sprintf(
                            'Ошибка при создании зоны %s: %s',
                            $group['CODE'],
                            \implode(', ', $addResult->getErrorMessages())
                        )
                    );
                    return false;
                }

                $this->log()->info(
                    \sprintf('Создана зона %s', $group['CODE'])
                );

                $groupID = $addResult->getId();

                foreach ($group['LOCATIONS'] as $locationToAdd) {
                    $addResult = GroupLocationTable::add(
                        [
                            'LOCATION_ID' => $locationToAdd,
                            'LOCATION_GROUP_ID' => $groupID
                        ]
                    );

                    if (!$addResult->isSuccess()) {
                        $this->log()->error(
                            \sprintf(
                                'Ошибка при добавлении местоположения %s в зону %s: %s',
                                $locationToAdd,
                                $group['CODE'],
                                \implode(', ', $addResult->getErrorMessages())
                            )
                        );
                        return false;
                    }

                    $this->log()->info(
                        \sprintf('Добавлено местоположение %s для зоны %s', $locationToAdd, $group['CODE'])
                    );

                    GroupLocationTable::delete(
                        [
                            'LOCATION_ID' => $locationToAdd,
                            'LOCATION_GROUP_ID' => 2
                        ]
                    );
                }
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function down()
    {
        $groupCodes = array_keys($this->groups);
        $groupIds = [];

        $groups = GroupTable::getList();
        while ($group = $groups->fetch()) {
            if (in_array($group['CODE'], $groupCodes)) {
                $groupIds[$group['CODE']] = $group['ID'];
            }
        }

        foreach ($this->groups as $groupCode => $group) {
            if (!isset($groupIds[$groupCode])) {
                $this->log()->warning(
                    'Группа с кодом ' . $groupCode . ' не найдена'
                );
            }
        }

        foreach ($groupIds as $groupCode => $groupId) {
            $deleteResult = GroupTable::delete($groupId);
            if ($deleteResult->isSuccess()) {
                $this->log()->info(
                    'Группа с кодом ' . $groupCode . ' удалена'
                );
            } else {
                $this->log()->error(
                    'Ошибка при удалении групы с кодом ' . $groupCode
                );

                return false;
            }
        }

        foreach ($this->groups as $group) {
            foreach ($group['LOCATIONS'] as $locationToAdd) {
                GroupLocationTable::add(
                    [
                        'LOCATION_ID' => $locationToAdd,
                        'LOCATION_GROUP_ID' => 2
                    ]
                );
            }
        }


        return true;
    }
}
