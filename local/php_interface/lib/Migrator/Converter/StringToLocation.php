<?php

namespace FourPaws\Migrator\Converter;

/**
 * Class StringToLocation
 *
 * Преобразует строку к локации
 *
 * @package FourPaws\Migrator\Converter
 */
final class StringToLocation extends AbstractConverter
{
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
        
        $data[$fieldName] = $this->getLocationByString($data[$fieldName]);
        
        return $data;
    }
    
    /**
     * @param string $string
     *
     * @return string
     */
    public function getLocationByString(string $string) : string
    {
        /**
         * @todo implement this
         */
        return $string;
    }
}
