<?php

namespace FourPaws\Migrator\Converter;

/**
 * Class StringToNotEmptyString
 *
 * Если свойство не заполнено, то заполняет его значением по умолчанию
 *
 * @package FourPaws\Migrator\Converter
 */
final class StringToNotEmptyString extends AbstractConverter
{
    const NOT_EMPTY_VALUE = ' ';
    
    private $notEmptyValue = self::NOT_EMPTY_VALUE;
    
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
            $data[$fieldName] = $this->notEmptyValue;
        }
        
        return $data;
    }
    
    /**
     * @param string $notEmptyValue
     */
    public function setNotEmptyValue(string $notEmptyValue)
    {
        $this->notEmptyValue = $notEmptyValue;
    }
}
