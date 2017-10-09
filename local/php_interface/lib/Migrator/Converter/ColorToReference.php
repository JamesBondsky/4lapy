<?php

namespace FourPaws\Migrator\Converter;

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
        $this->setDataClass();

        $fieldName = $this->getFieldName();
        
        if (!$data[$fieldName]) {
            return $data;
        }

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
        $externalKey = md5($value);
        
        $fields = [
            $fieldName                      => $value,
            self::FIELD_EXTERNAL_KEY        => $externalKey,
            self::CODE_FIELD_REFERENCE_CODE => $colorCode,
        ];
        
        $result = $this->getDataClass()::add($fields);
        
        if (!$result->isSuccess()) {
            /**
             * @todo придумать сюда нормальный Exception
             */
            throw new \Exception('Reference value add error: ' . implode(', ', $result->getErrorMessages()));
        }
        
        self::$referenceValues[$this->getReferenceCode()][] = $fields;
        
        return $externalKey;
    }
}