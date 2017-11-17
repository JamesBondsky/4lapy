<?php

namespace FourPaws\Migrator\Converter;

use Bitrix\Main\ArgumentException;
use Bitrix\Sale\Location\LocationTable;

/**
 * Class LocationCodeToLocation
 *
 * Преобразует код локации к id локации (массив кодов к массиву id)
 *
 * @package FourPaws\Migrator\Converter
 */
final class LocationCodeToLocation extends AbstractConverter
{
    /**
     * @param array $data
     *
     * @return array
     *
     * @throws ArgumentException
     */
    public function convert(array $data) : array
    {
        $fieldName = $this->getFieldName();
        
        if (!$data[$fieldName]) {
            return $data;
        }
        
        $data[$fieldName] = $this->getLocationByCode($data[$fieldName]);
        
        return $data;
    }
    
    /**
     * @param string|array $locationCodes
     *
     * @return mixed
     *
     * @throws ArgumentException
     */
    public function getLocationByCode($locationCodes)
    {
        if (!$locationCodes) {
            return $locationCodes;
        }
        
        $filter = is_array($locationCodes) ? ['@CODE' => $locationCodes] : ['=CODE' => $locationCodes];
        
        $locationList = LocationTable::getList([
                                                   'filter' => $filter,
                                                   'select' => ['ID'],
                                               ])->fetchAll();
        
        return array_column($locationList, 'ID');
    }
}
