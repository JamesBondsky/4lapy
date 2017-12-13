<?php

namespace FourPaws\Migrator\Converter;

use Bitrix\Main\ArgumentException;
use FourPaws\Migrator\Converter\Exception\ReferenceException;

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
        $fieldName = $this->getFieldName();
        
        if (!$data[$fieldName]) {
            return $data;
        }
        
        $result = $this->searchValue($data[$fieldName]);
    
        if (!$data[self::CODE_FIELD_NAME]) {
            throw new ReferenceException(sprintf('Country value add error: empty country code for %s',
                                                 $data[$fieldName]));
        }
        
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
     * @throws ReferenceException
     */
    protected function addValue(string $code, string $name) : string
    {
        $fields = [
            $this->getFieldToSearch() => $code,
            self::FIELD_EXTERNAL_KEY  => $name,
        ];
        
        $result = $this->getDataClass()::add($fields);
        
        if (!$result->isSuccess()) {
            throw new ReferenceException(sprintf('Reference value add error: %s',
                                                 implode(', ', $result->getErrorMessages())));
        }
        
        self::$referenceValues[$this->getReferenceCode()][] = $fields;
        
        return $code;
    }
    
    /**
     * @param $code
     * @param $fieldToSearch
     *
     * @return mixed
     *
     * @throws ReferenceException
     * @throws ArgumentException
     */
    protected function searchValue($code, $fieldToSearch = self::FIELD_EXTERNAL_KEY) : string
    {
        return parent::searchValue($code, self::FIELD_EXTERNAL_KEY);
    }
}
