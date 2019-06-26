<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Location\Admin\TypeHelper;
use Bitrix\Sale\Location\Admin\LocationHelper;
use Bitrix\Sale\Location\Admin\ExternalServiceHelper;
use Bitrix\Sale\Location\GroupLocationTable;
use Bitrix\Sale\Location\GroupTable;
use Exception;

class MoscowDistrictsLocationsAndZones20190611130708 extends SprintMigrationBase
{
    protected $description = 'Создание новых местоположений районов Москвы и Зон для них';

    protected $locationType = 'DISTRICT_MOSCOW';

    protected $locationService = 'OKATO';

    protected $zoneSortStart = 6000;

    protected $parentLocationsID = '92';
    protected $locations = [
        0  => [
            'CODE'          => '45290554',
            'NAME_RU'       => 'Район Выхино-Жулебино',
            'SHORT_NAME_RU' => 'р-н Выхино-Жулебино'
        ],
        1  => [
            'CODE'          => '45290564',
            'NAME_RU'       => 'Район Лефортово',
            'SHORT_NAME_RU' => 'р-н Лефортово'
        ],
        2  => [
            'CODE'          => '45290568',
            'NAME_RU'       => 'Район Люблино',
            'SHORT_NAME_RU' => 'р-н Люблино'
        ],
        3  => [
            'CODE'          => '45290582',
            'NAME_RU'       => 'Район Печатники',
            'SHORT_NAME_RU' => 'р-н Печатники'
        ],
        4 => [
            'CODE'          => '45290590',
            'NAME_RU'       => 'Район Текстильщики',
            'SHORT_NAME_RU' => 'р-н Текстильщики'
        ]
    ];

    /**
     * @return bool
     * @throws Exception
     */
    public function up(): bool
    {
        $locationTypeID = null;
        $types = TypeHelper::getTypes();
        foreach ($types as $type) {
            if ($type['CODE'] == $this->locationType) {
                $locationTypeID = $type['ID'];
                break;
            }
        }

        if (!$locationTypeID) {
            throw new Exception('Location type with ' . $this->locationType . ' code not found');
        }

        $locationServiceID = null;
        $dbServices = ExternalServiceHelper::getList([
            'filter' => [
                'CODE' => $this->locationService
            ],
            'select' => [
                'ID',
                'CODE'
            ]
        ]);
        while ($service = $dbServices->Fetch()) {
            if ($service['CODE'] == $this->locationService) {
                $locationServiceID = $service['ID'];
                break;
            }
        }

        if (!$locationServiceID) {
            throw new Exception('Location service with ' . $this->locationService . ' code not found');
        }
        $i = $this->zoneSortStart;
        foreach ($this->locations as &$location) {
            //add location
            $okato = $location['CODE'];
            $location['CODE'] = $this->locationType . '_' . $location['CODE'];
            $location['SORT'] = 1000;
            $location['EXTERNAL']['n0'] = [
                'SERVICE_ID' => (string)$locationServiceID,
                'XML_ID'     => $okato
            ];

            $res = LocationHelper::add(
                array_merge(
                    $location,
                    [
                        'SORT'      => '0',
                        'PARENT_ID' => (string)$this->parentLocationsID,
                        'TYPE_ID'   => (string)$locationTypeID,
                        'LATITUDE'  => '0',
                        'LONGITUDE' => '0'
                    ]
                )
            );

            if (!$res) {
                throw new Exception(implode(' ,', $res['errors']));
            }

            //add zone
            $locationId = $res['id'];
            $zoneCode = \CUtil::translit('ZONE_MOSCOW_DISTRICT_' . str_replace('Район ', '', $location['NAME_RU']), 'ru', ['change_case' => 'U']);
            $addResult = GroupTable::add(
                [
                    'NAME' => [
                        'ru' => [
                            'NAME' => str_replace('Район ', 'Район Москвы ', $location['NAME_RU'])
                        ]
                    ],
                    'SORT' => $i,
                    'CODE' => $zoneCode,
                ]
            );
            if (!$addResult->isSuccess()) {
                throw new Exception(implode(', ', $addResult->getErrorMessages()));
            }
            $groupID = $addResult->getId();

            //add location to zone
            $addResult = GroupLocationTable::add(
                [
                    'LOCATION_ID'       => $locationId,
                    'LOCATION_GROUP_ID' => $groupID
                ]
            );

            if (!$addResult->isSuccess()) {
                throw new Exception(implode(', ', $addResult->getErrorMessages()));
            }

            $i++;
        }

        return true;

    }

    /**
     * @return void
     */
    public function down()
    {
        $locationCodes = array_map(function ($location) {
            return $this->locationType . '_' . $location['CODE'];
        }, $this->locations);

        $dbLocations = LocationHelper::getList([
            'filter' => [
                'CODE' => $locationCodes
            ],
            'select' => [
                'ID',
                'CODE'
            ]
        ]);

        /**
         * did not delete zones
         */

        while ($location = $dbLocations->Fetch()) {
            LocationHelper::delete($location['ID']);
        }
    }
}
