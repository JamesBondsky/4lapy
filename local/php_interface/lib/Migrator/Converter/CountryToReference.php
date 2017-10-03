<?php

namespace FourPaws\Migrator\Converter;

/**
 * Class CountryToReference
 *
 * Специфичный для проекта конвертер
 * Сохраняет в справочник пару код страны/название страны
 *
 * @package FourPaws\Migrator\Converter
 */
final class CountryToReference extends StringToReference
{
    const CODE_FIELD_NAME = 'PROPERTY_COUNTRY_NAME';
    
    public function convert(array $data) : array
    {
        $this->setDataClass();
        
        $fieldName = $this->getFieldName();
        
        if (!$data[$fieldName]) {
            return $data;
        }
        
        $result = $this->searchValue($data[self::CODE_FIELD_NAME]);
        
        if (!$result) {
            $result = $this->addValue($data[self::CODE_FIELD_NAME], $data[$fieldName]);
            unset($data[self::CODE_FIELD_NAME]);
        }
        
        $data[$fieldName] = $result;
        
        return $data;
    }
    
    /**
     * @param $code
     * @param $name
     *
     * @return string
     * @throws \Exception
     */
    protected function addValue(string $code, string $name) : string
    {
        $fields = [
            $this->getFieldToSearch() => $code,
            self::FIELD_EXTERNAL_KEY  => $name,
        ];
        
        $result = $this->getDataClass()::add($fields);
        
        if (!$result->isSuccess()) {
            /**
             * @todo придумать сюда нормальный Exception
             */
            throw new \Exception('Reference value add error: ' . implode(', ', $result->getErrorMessages()));
        }
        
        self::$referenceValues[$this->getReferenceCode()] = $fields;
        
        return $code;
    }
    
    /**
     * @param $code
     * @param $fieldToSearch
     *
     * @return mixed
     */
    protected function searchValue($code, $fieldToSearch = self::FIELD_EXTERNAL_KEY) : string
    {
        return parent::searchValue($code, self::FIELD_EXTERNAL_KEY);
    }
}