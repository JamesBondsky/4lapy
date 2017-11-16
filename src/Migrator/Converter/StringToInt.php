<?php

namespace FourPaws\Migrator\Converter;

/**
 * Class StringToInt
 *
 * Явно! преобразует строки к числам
 *
 * @package FourPaws\Migrator\Converter
 */
final class StringToInt extends AbstractConverter
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
        
        if (is_array($data[$fieldName])) {
            foreach ($data[$fieldName] as &$field) {
                $field = (int)$field;
            }
        } else {
            $data[$fieldName] = (int)$data[$fieldName];
        }

        return $data;
    }
}