<?php

namespace FourPaws\LocationBundle\Repository;

use FourPaws\LocationBundle\Repository\Table\LocationParentsTable;

class LocationParentsRepository
{
    /**
     * @param int $id
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getById(int $id): array
    {
        $dbLocation = LocationParentsTable::query()
            ->setFilter(['=ID' => $id])
            ->setSelect([
                'ID',
                'PARENTS',
            ])
            ->setLimit(1)
            ->exec()
            ->fetch();

        if (!$dbLocation) {
            return [];
        }

        return json_decode($dbLocation['PARENTS'], true);
    }
}