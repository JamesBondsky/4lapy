<?php

namespace FourPaws\Migrator\Converter;

/**
 * Class CodeBuilder
 *
 * !!! специфичный для проекта конвертер
 *
 * Строит уникальный! код элемента из кода и внешнего кода
 *
 * @package FourPaws\Migrator\Converter
 */
final class CodeBuilder extends AbstractConverter
{
    const XML_ID_FIELD_NAME = 'XML_ID';
    
    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function convert(array $data) : array
    {
        $fieldName = $this->getFieldName();
        
        $data[$fieldName] = $data[$fieldName] . '_' . $data[self::XML_ID_FIELD_NAME];
        
        return $data;
    }
}
