<?php

namespace FourPaws\Migrator\Converter;

/**
 * Class PhoneBuilder
 *
 * !!! специфичный для проекта конвертер
 *
 * Строит телефон склада из телефона зоомагазина и названия зоомагазина со старого сайта
 *
 * @package FourPaws\Migrator\Converter
 */
final class PhoneBuilder extends AbstractConverter
{
    const ADDITIONAL_PHONE_FIELD_NAME = 'ADDITIONAL_PHONE';
    
    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function convert(array $data) : array
    {
        $fieldName = $this->getFieldName();
        
        if ($data[self::ADDITIONAL_PHONE_FIELD_NAME]) {
            $data[$fieldName] = $data[$fieldName] . ', доб. ' . $data[self::ADDITIONAL_PHONE_FIELD_NAME];
        }
        
        unset($data[self::ADDITIONAL_PHONE_FIELD_NAME]);
        
        return $data;
    }
}
