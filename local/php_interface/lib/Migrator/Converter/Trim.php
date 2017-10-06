<?php

namespace FourPaws\Migrator\Converter;

/**
 * Class Trim
 *
 * Применяет trim к полю
 *
 * @package FourPaws\Migrator\Converter
 */
final class Trim extends AbstractConverter
{
    const CHARS = ' ';
    
    private $chars = self::CHARS;
    
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
        
        if (is_array($data[$fieldName])) {
            foreach ($data[$fieldName] as &$field) {
                $field = trim($field, $this->chars);
            }
        } else {
            $data[$fieldName] = trim($data[$fieldName], $this->chars);
        }

        return $data;
    }
    
    /**
     * @param string $chars
     */
    public function setChars(string $chars)
    {
        $this->chars = $chars;
    }
}