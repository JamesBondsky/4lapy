<?php

namespace FourPaws\Migrator\Converter;

/**
 * Class StoreGpsSeparator
 *
 * !!! специфичный для проекта конвертер
 *
 * Разделяет свойство типа "привязка к карте" на поля для координат склада
 *
 * @package FourPaws\Migrator\Converter
 */
final class StoreGpsSeparator extends AbstractConverter
{
    const GPS_N_FIELD_NAME = 'GPS_N';
    
    const GPS_S_FIELD_NAME = 'GPS_S';
    
    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function convert(array $data) : array
    {
        $fieldName = $this->getFieldName();
        
        if (!$data[$fieldName]) {
            return $data;
        }
        
        $coordinates = explode(',', $data[$fieldName]);
        
        $data[self::GPS_N_FIELD_NAME] = $coordinates[0];
        $data[self::GPS_S_FIELD_NAME] = $coordinates[1];
        
        unset($data[$fieldName]);
        
        return $data;
    }
}
