<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\UserBundle\Repository;

use Bitrix\Main\GroupTable;

/**
 * Class GroupRepository
 * @package FourPaws\UserBundle\Repository
 */
class GroupRepository
{
    /** @var array */
    protected static $groups = [];

    /**
     * @param string $code
     * @return int
     */
    public static function getIdByCode(string $code): int
    {
        if (!empty(self::$groups)) {
            return self::$groups[$code]['ID'] ?? 0;
        }
        $groups = GroupTable::query()
            ->addSelect('ID')
            ->addSelect('STRING_ID')
            ->exec()
            ->fetchAll();

        $groupMap = [];
        foreach ($groups as $group) {
            $groupMap[$group['STRING_ID']] = $group;
        }

        self::$groups = $groupMap;
        return self::$groups[$code]['ID'] ?? 0;
    }
}