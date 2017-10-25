<?php

namespace FourPaws\Migrator\Converter;

/**
 * Class StringToMultipleString
 *
 * Разделяет строки в множественные поля, раделяя по разделителю
 *
 * @package FourPaws\Migrator\Converter
 */
final class StringToMultipleString extends AbstractConverter
{
    const DEFAULT_SEPARATOR = ',';
    
    private $separator = self::DEFAULT_SEPARATOR;
    
    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function convert(array $data) : array
    {
        $fieldName = $this->getFieldName();
        
        if (!$data[$fieldName] || is_array($data[$fieldName])) {
            return $data;
        }
        
        $data[$fieldName] = explode($this->separator, $data[$fieldName]);
        
        return $data;
    }
    
    /**
     * @param string $separator
     */
    public function setSeparator(string $separator)
    {
        $this->separator = $separator;
    }
}
