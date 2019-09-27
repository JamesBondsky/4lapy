<?php

namespace Sprint\Migration;

use Bitrix\Main\ArgumentException;
use Bitrix\Sale\Location\GroupLocationTable;
use Bitrix\Sale\Location\GroupTable;
use Exception;
use FourPaws\DeliveryBundle\Service\DeliveryService;

class RegionalZonesDpdExcludeCreate20190912162630 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Добавляет новую зону с городами исключениями для DPD';

    protected $groups = [
        DeliveryService::ZONE_DPD_EXCLUDE => [
            'NAME' => ['ru' => ['NAME' => 'Исключить из DPD']],
            'SORT' => 80,
            'CODE' => DeliveryService::ZONE_DPD_EXCLUDE,
            'LOCATIONS' => [
                809473,
                1007461,
                1032187,
            ]
        ],
    ];

    /**
     * @return bool
     * @throws Exception
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
                }
            }
        }

        return true;

    }

    /**
     * @return bool
     * @throws ArgumentException
     * @throws Exception
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

        return true;
    }
}
