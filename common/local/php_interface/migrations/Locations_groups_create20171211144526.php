<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Location\GroupLocationTable;
use Bitrix\Sale\Location\GroupTable;
use Bitrix\Sale\Location\LocationTable;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryServiceHandlerBase;
use RuntimeException;

class Locations_groups_create20171211144526 extends SprintMigrationBase
{
    protected $description = 'Создание и заполнение групп местоположений (являющихся зонами доставки)';

    const ZONE_1 = 'ZONE_1';

    const ZONE_2 = 'ZONE_2';

    const ZONE_3 = 'ZONE_3';

    const ZONE_4 = 'ZONE_4';

    protected $groups = [
        DeliveryServiceHandlerBase::ZONE_1 => [
            'NAME' => ['ru' => ['NAME' => 'Зона 1']],
            'SORT' => 100,
            'CODE' => DeliveryServiceHandlerBase::ZONE_1,
        ],
        DeliveryServiceHandlerBase::ZONE_2 => [
            'NAME' => ['ru' => ['NAME' => 'Зона 2']],
            'SORT' => 200,
            'CODE' => DeliveryServiceHandlerBase::ZONE_2,
        ],
        DeliveryServiceHandlerBase::ZONE_3 => [
            'NAME' => ['ru' => ['NAME' => 'Зона 3']],
            'SORT' => 300,
            'CODE' => DeliveryServiceHandlerBase::ZONE_3,
        ],
        DeliveryServiceHandlerBase::ZONE_4 => [
            'NAME' => ['ru' => ['NAME' => 'Зона 4']],
            'SORT' => 400,
            'CODE' => DeliveryServiceHandlerBase::ZONE_4,
        ],
    ];

    public function up()
    {
        $groupCodes = array_keys($this->groups);
        $groupIds = [];

        $groups = GroupTable::getList();
        while ($group = $groups->fetch()) {
            if (in_array($group['CODE'], $groupCodes)) {
                $this->log()->info('Зона ' . $group['CODE'] . ' уже существует');
                $groupIds[$group['CODE']] = $group['ID'];
                unset($this->groups[$group['CODE']]);
            }
        }

        if (!empty($this->groups)) {
            foreach ($this->groups as $group) {
                $addResult = GroupTable::add($group);
                if (!$addResult->isSuccess()) {
                    $this->log()->error('Ошибка при создании зоны ' . $group['CODE']);

                    return false;
                } else {
                    $this->log()->info('Создана зона ' . $group['CODE']);
                }

                $groupIds[$group['CODE']] = $addResult->getId();
            }
        }

        $allLocations = $this->getLocations();

        foreach ($allLocations as $groupCode => $location) {
            if (!isset($groupIds[$groupCode]) || empty($allLocations[$groupCode])) {
                $this->log()->warning('Пропускаем добавление местоположений для зоны ' . $groupCode);
                continue;
            }

            $groupId = $groupIds[$groupCode];
            $locationsToAdd = $allLocations[$groupCode];

            $locations = LocationTable::getList(
                [
                    'filter' => ['CODE' => array_column($locationsToAdd, 'CODE')],
                ]
            );
            while ($location = $locations->fetch()) {
                $locationsToAdd[$location['CODE']]['ID'] = $location['ID'];
            }

            foreach ($locationsToAdd as $code => $locationToAdd) {
                if (!isset($locationToAdd['ID'])) {
                    $this->log()->warning('Местоположение с кодом ' . $code . ' не найдено');
                    unset($locationsToAdd[$code]);
                }
            }

            $groupLocations = GroupLocationTable::getList(
                [
                    'filter' => [
                        'LOCATION_GROUP_ID' => $groupId,
                        'LOCATION_ID'       => array_column($locationsToAdd, 'ID'),
                    ],
                    'select' => [
                        'LOCATION_ID',
                        'LOCATION.CODE',
                    ],
                ]
            );

            while ($groupLocation = $groupLocations->fetch()) {
                $code = $groupLocation['SALE_LOCATION_GROUP_LOCATION_LOCATION_CODE'];
                $this->log()->warning(
                    'Местоположение ' . $code . ' для группы ' . $groupCode . ' уже существует'
                );
                unset($locationsToAdd[$code]);
            }

            foreach ($locationsToAdd as $code => $locationToAdd) {
                $addResult = GroupLocationTable::add(
                    [
                        'LOCATION_ID'       => $locationToAdd['ID'],
                        'LOCATION_GROUP_ID' => $groupId,
                    ]
                );

                if (!$addResult->isSuccess()) {
                    $this->log()->warning(
                        'Ошибка при добавлении местоположения ' . $code . ' в группу ' . $groupCode
                    );
                } else {
                    $this->log()->info(
                        'Местоположение ' . $code . ' добавлено в группу ' . $groupCode
                    );
                }
            }
        }

        return true;
    }

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

        return true;
    }

    protected function getLocations()
    {
        $filePath = Application::getAbsolutePath('/local/php_interface/migration_sources/group_locations.csv');
        $fp = fopen($filePath, 'rb');
        if (false === $fp) {
            throw new RuntimeException(
                sprintf(
                    'Can not open file %s',
                    $filePath
                )
            );
        }

        $groups = [];
        while ($row = fgetcsv($fp)) {
            $groups['ZONE_' . trim($row[1])][trim($row[2])] = [
                'NAME' => trim($row[0]),
                'CODE' => trim($row[2]),
            ];
        }

        fclose($fp);

        return $groups;
    }
}
