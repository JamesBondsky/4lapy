<?php

namespace FourPaws\Migrator\Converter;

/**
 * Class StoreNameBuilder
 *
 * !!! специфичный для проекта конвертер
 *
 * Строит название склада из названия склада и названия магазина со старого сайта
 *
 * @package FourPaws\Migrator\Converter
 */
final class StoreNameBuilder extends AbstractConverter
{
    const LOCATION_FIELD_NAME = 'UF_LOCATION';
    
    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function convert(array $data) : array
    {
        $fieldName = $this->getFieldName();
        $data[$fieldName] = $data[$fieldName] . ' ' . $data[self::LOCATION_FIELD_NAME];
        
        return $data;
    }
}
