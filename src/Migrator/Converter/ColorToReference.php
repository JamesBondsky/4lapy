<?php

namespace FourPaws\Migrator\Converter;

use FourPaws\Migrator\Converter\Exception\ReferenceException;

/**
 * Class ColorToReference
 *
 * Специфичный для проекта конвертер
 * Сохраняет в справочник пару rgb-код цвета/название цвета
 *
 * @package FourPaws\Migrator\Converter
 */
final class ColorToReference extends StringToReference
{
    const CODE_FIELD_NAME           = 'PROPERTY_CODE_COLOUR';
    
    const CODE_FIELD_REFERENCE_CODE = 'UF_COLOUR_CODE';
    
    public function convert(array $data) : array
    {
        $fieldName = $this->getFieldName();
        
        if (!$data[$fieldName]) {
            return $data;
        }
    
        if (!$data[self::CODE_FIELD_NAME]) {
            throw new ReferenceException(sprintf('Color value add error: empty color code for %s',
                                                 $data[$fieldName]));
        }
        
        $data[$fieldName] = trim($data[$fieldName]);
        
        $result = $this->searchValue($data[$fieldName], $this->getFieldToSearch());
        
        if (!$result) {
            $result = $this->addValue($data[$fieldName], $this->getFieldToSearch(), $data[self::CODE_FIELD_NAME]);
            unset($data[self::CODE_FIELD_NAME]);
        }
        
        $data[$fieldName] = $result;
        
        return $data;
    }
    
    /**
     * @param string $value
     * @param string $fieldName
     * @param string $colorCode
     *
     * @return string
     * @throws \Exception
     */
    protected function addValue(string $value, string $fieldName, string $colorCode) : string
    {
        $externalKey = $this->transliterate($value);
        $exists      =
            $this->getDataClass()::getList([
                                               'filter' => [self::FIELD_EXTERNAL_KEY => $externalKey],
                                               'select' => [self::FIELD_EXTERNAL_KEY],
                                           ])->fetch();

        if ($exists[self::FIELD_EXTERNAL_KEY]) {
            return $exists[self::FIELD_EXTERNAL_KEY];
        }
        
        $fields = [
            $fieldName                      => $value,
            self::FIELD_EXTERNAL_KEY        => $externalKey,
            self::CODE_FIELD_REFERENCE_CODE => $colorCode,
        ];
        
        $result = $this->getDataClass()::add($fields);
        
        if (!$result->isSuccess()) {
            throw new ReferenceException('Reference value add error: ' . implode(', ', $result->getErrorMessages()));
        }
        
        self::$referenceValues[$this->getReferenceCode()][] = $fields;
        
        return $externalKey;
    }
}
