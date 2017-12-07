<?php

namespace FourPaws\Migrator\Converter;

use Bitrix\Main\ArgumentException;
use Bitrix\Sale\Location\Name\LocationTable;

/**
 * Class StringToLocation
 *
 * Преобразует строку к коду местоположения
 *
 * @package FourPaws\Migrator\Converter
 */
final class StringToLocation extends AbstractConverter
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
        
        $data[$fieldName] = $this->getLocationByString($data[$fieldName]);
        
        return $data;
    }
    
    /**
     * @param string $string
     *
     * @return string
     *
     * @throws ArgumentException
     */
    public function getLocationByString(string $string) : string
    {
        $locationCode = '';
    
        /**
         * Ищем сёла и посёлки по кодам
         */
        switch (strtoupper($string)) {
            case 'БЫКОВО':
                $locationCode = '0000059219';
                break;
            case 'КРАСКОВО':
                $locationCode = '0000046135';
                break;
            case 'ТОМИЛИНО':
                $locationCode = '0000046724';
                break;
        }
    
        if ($locationCode) {
            return $locationCode;
        }
        
        $location = LocationTable::getList([
                                               'filter' => ['=NAME_UPPER' => strtoupper($string)],
                                               'select' => ['LOCATION.CODE'],
                                           ])->fetch();
    
        return $location['SALE_LOCATION_NAME_LOCATION_LOCATION_CODE'];
    }
}
