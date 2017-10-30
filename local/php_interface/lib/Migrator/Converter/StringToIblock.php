<?php

namespace FourPaws\Migrator\Converter;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

/**
 * Class StringToIblock
 *
 * Конвертация строковых значений в элементы инфоблока. Сравнение по $fieldToSearch (NAME по умолчанию).
 * В случае, если значений не найдено, значения добавляются в инфоблок.
 *
 * @package FourPaws\Migrator\Converter
 */
class StringToIblock extends AbstractConverter
{
    const FIELD_EXTERNAL_KEY = 'ID';
    
    const DEFAULT_VALUE_KEY  = 'NAME';
    
    private          $iblockId;
    
    private          $fieldToSearch;
    
    protected static $iblockValues = [];
    
    /**
     * @return string
     */
    public function getFieldToSearch() : string
    {
        return $this->fieldToSearch;
    }
    
    /**
     * @param string $fieldToSearch
     */
    public function setFieldToSearch(string $fieldToSearch = self::DEFAULT_VALUE_KEY)
    {
        $this->fieldToSearch = $fieldToSearch;
    }
    
    /**
     * @return string
     */
    public function getIblockId() : string
    {
        return $this->iblockId;
    }
    
    /**
     * @param string $iblockId
     */
    public function setIblockId(string $iblockId)
    {
        $this->iblockId = $iblockId;
    }
    
    /**
     * @param array $data
     *
     * @return array
     *
     * @throws \Exception
     */
    public function convert(array $data) : array
    {
        $isArray   = true;
        $fieldName = $this->getFieldName();
        
        if (!$data[$fieldName]) {
            return $data;
        }
        
        $fieldToSearch = $this->getFieldToSearch();
        
        if (!is_array($data[$fieldName])) {
            $isArray          = false;
            $data[$fieldName] = [$data[$fieldName]];
        }
        
        $result = [];
        
        foreach ($data[$fieldName] as $value) {
            $r = $this->searchValue($value, $fieldToSearch);
            
            if (!$r) {
                $r = $this->addValue($value, $fieldToSearch);
            }
            
            $result[] = $r;
        }
        
        $data[$fieldName] = $isArray ? $result : array_shift($result);
        
        return $data;
    }
    
    /**
     * @param $value
     * @param $fieldName
     *
     * @return string
     * @throws \Exception
     */
    protected function addValue(string $value, string $fieldName) : string
    {
        
        $cIBlockElement = new \CIBlockElement();
        
        $code = \CUtil::translit($value,
                                 'ru',
                                 [
                                     'replace_space' => '-',
                                     'replace_other' => '-',
                                 ]);
        
        $exists = ElementTable::getList([
                                            'filter' => [
                                                'CODE'      => $code,
                                                'IBLOCK_ID' => $this->getIblockId(),
                                            ],
                                            'select' => [self::FIELD_EXTERNAL_KEY],
                                        ])->fetch();
        
        if ($exists[self::FIELD_EXTERNAL_KEY]) {
            return $exists[self::FIELD_EXTERNAL_KEY];
        }
        
        $fields = [
            $fieldName  => $value,
            'CODE'      => $code,
            'IBLOCK_ID' => $this->getIblockId(),
        ];
        
        if ($fieldName !== self::DEFAULT_VALUE_KEY) {
            $fields[self::DEFAULT_VALUE_KEY] = '-';
        }
        
        $result = $cIBlockElement->Add($fields);
        
        if (!$result) {
            /**
             * @todo придумать сюда нормальный Exception
             */
            throw new \Exception('Iblock element add error: ' . $cIBlockElement->LAST_ERROR);
        }
        
        $fields['ID'] = $result;
        
        self::$iblockValues[] = $fields;
        
        return $result;
    }
    
    /**
     * @param $value
     * @param $fieldToSearch
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function searchValue($value, $fieldToSearch)
    {
        $referenceValues = $this->getReferenceValues();
        $position        = array_search($value,
                                        array_column($referenceValues, $fieldToSearch),
                                        true);
        
        return $position === false ? '' : $referenceValues[$position][self::FIELD_EXTERNAL_KEY];
    }
    
    /**
     * @return array
     *
     * @throws \Exception
     */
    protected function getReferenceValues() : array
    {
        if (!self::$iblockValues) {
            $collection = \CIBlockElement::GetList([],
                                                   ['IBLOCK_ID' => $this->getIblockId()],
                                                   false,
                                                   false,
                                                   [
                                                       'NAME',
                                                       'ID',
                                                       'CODE',
                                                   ]);
            
            while ($element = $collection->Fetch()) {
                self::$iblockValues[] = $element;
            }
        }
        
        return self::$iblockValues;
    }
    
    /**
     * StringToReference constructor.
     *
     * @param string $fieldName
     *
     * @throws \Bitrix\Main\LoaderException
     */
    public function __construct(string $fieldName)
    {
        if (!Loader::includeModule('iblock')) {
            throw new LoaderException('Module iblock must be installed');
        }
        
        $this->setFieldToSearch();
        
        parent::__construct($fieldName);
    }
}