<?php

namespace FourPaws\DeliveryBundle\Dpd;

use Bitrix\Main\Loader;

if (!Loader::includeModule('ipol.dpd')) {
    class LocationTable
    {
    }

    return;
}

use Bitrix\Sale\Location\LocationTable as BitrixLocationTable;
use WebArch\BitrixCache\BitrixCache;

class LocationTable extends \Ipolh\DPD\DB\Location\Table
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
            $ret = static::getList(
                array_filter(
                    [
                        'filter' => ['LOCATION_ID' => $locationId],
                    ]
                )
            );

            $ret->addReplacedAliases(['LOCATION_ID' => 'ID']);

            return $ret->fetch();
        };

        return (new BitrixCache())
            ->withId(__METHOD__ . $locationId)
            ->resultOf($getDpdLocation);
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

            return $ret->fetch();
        };

        return (new BitrixCache())
            ->withId(__METHOD__ . $locationCode)
            ->resultOf($getDpdLocation);
    }
}
