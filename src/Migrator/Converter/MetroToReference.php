<?php

namespace FourPaws\Migrator\Converter;

use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use FourPaws\Migrator\Converter\Exception\ReferenceException;

/**
 * Class MetroToReference
 *
 * Специфичный для проекта конвертер
 * Сохраняет в справочники связку метро/ветка
 *
 * @todo    ужасно написано
 *
 * @package FourPaws\Migrator\Converter
 */
final class MetroToReference extends StringToReference
{
    const WAY_FIELD_NAME       = 'METRO_WAY';
    
    const WAY_COLOR_FIELD_NAME = 'METRO_WAY_COLOR';
    
    private $branch;
    
    public function convert(array $data) : array
    {
        $fieldName = $this->getFieldName();
        
        if (!$data[$fieldName]) {
            return $data;
        }
        
        if ($data[self::WAY_FIELD_NAME]) {
            $this->branch = $this->getBranch($data[self::WAY_FIELD_NAME], $data[self::WAY_COLOR_FIELD_NAME]);
        }
        
        unset($data[self::WAY_FIELD_NAME], $data[self::WAY_COLOR_FIELD_NAME]);
        
        return parent::convert($data);
    }
    
    /**
     * @param string $name
     * @param string $color
     *
     * @return string
     *
     * @throws ArgumentException
     * @throws SystemException
     * @throws ReferenceException
     */
    private function getBranch(string $name, string $color) : string
    {
        $dataClass = $this->getDataClass('MetroWays');
        
        $exists = $dataClass::getList([
                                          'filter' => ['=UF_XML_ID' => md5($name)],
                                          'select' => ['ID'],
                                      ])->fetch();
        
        if ($exists['ID']) {
            return $exists['ID'];
        }
        
        return $this->setBranch($dataClass, $name, $color);
    }
    
    /**
     * @param $value
     * @param $fieldName
     *
     * @return string
     *
     * @throws ReferenceException
     * @throws ArgumentException
     * @throws SystemException
     */
    protected function addValue(string $value, string $fieldName) : string
    {
        $externalKey = md5($value);
        
        $fields = [
            $fieldName               => $value,
            self::FIELD_EXTERNAL_KEY => $externalKey,
            'UF_BRANCH'              => $this->branch,
        ];
        
        $exists = $this->getDataClass()::getList([
                                                     'filter' => [self::FIELD_EXTERNAL_KEY => $externalKey],
                                                     'select' => [self::FIELD_EXTERNAL_KEY],
                                                 ])->fetch();
        
        if ($exists[self::FIELD_EXTERNAL_KEY]) {
            return $exists[self::FIELD_EXTERNAL_KEY];
        }
        
        $result = $this->getDataClass()::add($fields);
        
        if (!$result->isSuccess()) {
            throw new ReferenceException('Reference value add error: ' . implode(', ', $result->getErrorMessages()));
        }
        
        self::$referenceValues[$this->getReferenceCode()][] = $fields;
        
        return $externalKey;
    }
    
    /**
     * @param DataManager $dataManager
     * @param string      $name
     * @param string      $color
     *
     * @return string
     *
     * @throws ReferenceException
     */
    private function setBranch(DataManager $dataManager, string $name, string $color) : string
    {
        $result = $dataManager::add([
                                        'UF_XML_ID'      => md5($name),
                                        'UF_NAME'        => $name,
                                        'UF_COLOUR_CODE' => $color,
                                    ]);
        
        if ($result->isSuccess()) {
            return $result->getId();
        }
        
        throw new ReferenceException(sprintf('Branch create error: %s', implode(', ', $result->getErrorMessages())));
    }
}
