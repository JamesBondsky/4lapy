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

class NewRegionalZones20190405154733 extends SprintMigrationBase
{
    protected $description = 'Создание 30 новых зон доставков';

    const FIRST_SORT = 2900;
    const CODE_PATTERN = 'ADD_DELIVERY_ZONE_';
    const NAME_PATTERN = 'Дополнительная зона доставки ';

    /**
     * @return bool
     * @throws \Exception
     */
    public function up()
    {
        for ($i = 1; $i <= 30; $i++) {
            $addResult = GroupTable::add(
                [
                    'NAME' => [
                        'ru' => [
                            'NAME' => static::NAME_PATTERN . $i
                        ]
                    ],
                    'SORT' => static::FIRST_SORT + $i * 100,
                    'CODE' => static::CODE_PATTERN . $i,
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
        $groups = GroupTable::getList();
        while ($group = $groups->fetch()) {
            if (mb_strpos($group['CODE'], static::CODE_PATTERN) !== false) {
                $groupIds[$group['CODE']] = $group['ID'];
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
