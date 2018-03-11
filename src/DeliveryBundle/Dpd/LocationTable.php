<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Dpd;

use Bitrix\Main\Loader;

if (!Loader::includeModule('ipol.dpd')) {
    class LocationTable
    {
    }

    return;
}

use Bitrix\Sale\Location\LocationTable as BitrixLocationTable;
use Ipolh\DPD\DB\Location\Table;
use WebArch\BitrixCache\BitrixCache;

class LocationTable extends Table
{
    /**
     * Возвращает запись по местоположению битрикса
     *
     * @param  int $locationId
     * @param  array $select
     *
     * @return array|false
     */
    public static function getByLocationId($locationId)
    {
        $getDpdLocation = function () use ($locationId) {
            $location = BitrixLocationTable::getList(
                [
                    'filter' => ['ID' => $locationId],
                ]
            )->fetch();

            $ret = static::getList(
                array_filter(
                    [
                        'filter' => ['LOCATION_ID' => $locationId],
                    ]
                )
            );

            $ret->addReplacedAliases(['LOCATION_ID' => 'ID']);

            $result = $ret->fetch();
            $result['CODE'] = $location['CODE'];

            return ['result' => $result];
        };

        return (new BitrixCache())
            ->withId(__METHOD__ . $locationId)
            ->resultOf($getDpdLocation)['result'];
    }

    /**
     * Возвращает запись по местоположению битрикса
     *
     * @param  string $locationCode
     * @param  array $select
     *
     * @return array|false
     */
    public static function getByLocationCode($locationCode)
    {
        $getDpdLocation = function () use ($locationCode) {
            $location = BitrixLocationTable::getList(
                [
                    'filter' => ['CODE' => $locationCode],
                ]
            )->fetch();

            if (!$location) {
                return false;
            }

            $ret = static::getList(
                array_filter(
                    [
                        'filter' => ['LOCATION_ID' => $location['ID']],
                    ]
                )
            );

            $ret->addReplacedAliases(['LOCATION_ID' => 'ID']);
            $result = $ret->fetch();
            $result['CODE'] = $locationCode;

            return ['result' => $result];
        };

        return (new BitrixCache())
            ->withId(__METHOD__ . $locationCode)
            ->resultOf($getDpdLocation)['result'];
    }
}
