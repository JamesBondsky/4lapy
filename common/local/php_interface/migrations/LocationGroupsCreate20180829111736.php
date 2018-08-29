<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Location\GroupTable;
use FourPaws\DeliveryBundle\Service\DeliveryService;

class LocationGroupsCreate20180829111736 extends SprintMigrationBase
{
    protected $description = 'Создание и заполнение групп местоположений для зон доставки 5 и 6';

    protected $groups = [
        DeliveryService::ZONE_1 => [
            'NAME' => ['ru' => ['NAME' => 'Зона 1 (Москва + 10км)']],
            'SORT' => 100,
        ],
        DeliveryService::ZONE_5 => [
            'NAME' => ['ru' => ['NAME' => 'Зона 5 (30 - 60км от МКАД)']],
            'SORT' => 200,
            'CODE' => DeliveryService::ZONE_5
        ],
        DeliveryService::ZONE_6 => [
            'NAME' => ['ru' => ['NAME' => 'Зона 6 (10 - 30км от МКАД)']],
            'SORT' => 300,
            'CODE' => DeliveryService::ZONE_6
        ],
        DeliveryService::ZONE_2 => [
            'SORT' => 400,
        ],
        DeliveryService::ZONE_3 => [
            'SORT' => 500,
        ],
        DeliveryService::ZONE_4 => [
            'SORT' => 600,
        ],
    ];

    protected $toDelete = [
        DeliveryService::ZONE_5,
        DeliveryService::ZONE_6,
    ];

    /**
     * @return bool
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Exception
     */
    public function up()
    {
        $groupCodes = array_keys($this->groups);

        $groups = GroupTable::getList();
        while ($group = $groups->fetch()) {
            if (\in_array($group['CODE'], $groupCodes, true)) {
                $updateResult = GroupTable::update($group['ID'], $this->groups[$group['CODE']]);
                if (!$updateResult->isSuccess()) {
                    $this->log()->error(
                        \sprintf(
                            'Ошибка при обновлении зоны %s: %s',
                            $group['CODE'],
                            \implode(', ', $updateResult->getErrorMessages())
                        )
                    );

                    return false;
                }

                $this->log()->info(
                    \sprintf('Зона %s обновлена', $group['CODE'])
                );

                unset($this->groups[$group['CODE']]);
            }
        }

        if (!empty($this->groups)) {
            foreach ($this->groups as $group) {
                $addResult = GroupTable::add($group);
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
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentNullException
     * @throws SqlQueryException
     * @throws \Exception
     */
    public function down()
    {
        $groupCodes = $this->toDelete;
        $groupIds = [];

        $groups = GroupTable::getList();
        while ($group = $groups->fetch()) {
            if (\in_array($group['CODE'], $groupCodes, true)) {
                $groupIds[$group['CODE']] = $group['ID'];
            }
        }

        foreach ($this->groups as $groupCode => $group) {
            if (!isset($groupIds[$groupCode])) {
                $this->log()->warning(
                    \sprintf('Группа с кодом %s не найдена', $groupCode)
                );
            }
        }

        foreach ($groupIds as $groupCode => $groupId) {
            $deleteResult = GroupTable::delete($groupId);
            if ($deleteResult->isSuccess()) {
                $this->log()->info(
                    \sprintf('Группа с кодом %s удалена', $groupCode)
                );
            } else {
                $this->log()->error(
                    \sprintf(
                        'Ошибка при удалении группы с кодом %s: %s',
                        $groupCode,
                        \implode(', ', $deleteResult->getErrorMessages())
                    )
                );

                return false;
            }
        }

        return true;
    }
}
