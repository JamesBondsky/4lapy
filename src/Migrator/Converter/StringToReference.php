<?php

namespace FourPaws\Migrator\Converter;

use Bitrix\Highloadblock\DataManager;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use FourPaws\Migrator\Converter\Exception\ReferenceException;

/**
 * Class StringToReference
 *
 * Конвертация строковых значений в значения из справочника. Сравнение по $fieldToSearch (UF_NAME по умолчанию).
 * В случае, если значений не найдено, значения добавляются в справочник.
 *
 * @package FourPaws\Migrator\Converter
 */
class StringToReference extends AbstractConverter
{
    const FIELD_EXTERNAL_KEY = 'UF_XML_ID';
    
    private $referenceCode;
    
    private $fieldToSearch;
    
    private $returnFieldName = '';
    
    /**
     * @var DataManager
     */
    private $dataClass;
    
    /**
     * @param string $returnFieldName
     */
    public function setReturnFieldName(string $returnFieldName)
    {
        $this->returnFieldName = $returnFieldName;
    }
    
    protected static $referenceValues = [];
    
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
    public function setFieldToSearch(string $fieldToSearch = 'UF_NAME')
    {
        $this->fieldToSearch = $fieldToSearch;
    }
    
    /**
     * @param string $dataClassName
     *
     * @return DataManager
     *
     * @throws ReferenceException
     * @throws SystemException
     * @throws ArgumentException
     */
    protected function getDataClass(string $dataClassName = '') : DataManager
    {
        if (!$dataClassName && $this->dataClass) {
            return $this->dataClass;
        }
        
        $externalCall = (bool)$dataClassName;
        
        $dataClassName = $dataClassName ?: $this->getReferenceCode();
        
        $table = HighloadBlockTable::getList(['filter' => ['=NAME' => $dataClassName]])->fetch();
        
        if (!$table) {
            throw new ReferenceException('Highloadblock with name ' . $dataClassName . ' is not found.');
        }
        
        $dataClass = HighloadBlockTable::compileEntity($table)->getDataClass();
        
        if (is_string($dataClass)) {
            $dataClass = new $dataClass();
        }
        
        if (!$externalCall) {
            $this->dataClass = $dataClass;
        }
        
        /**
         * @var DataManager $dataClass
         */
        return $dataClass;
    }
    
    /**
     * @return string
     */
    public function getReferenceCode() : string
    {
        return $this->referenceCode;
    }
    
    /**
     * @param string $referenceCode
     */
    public function setReferenceCode(string $referenceCode)
    {
        $this->referenceCode = $referenceCode;
    }
    
    /**
     * @param array $data
     *
     * @return array
     *
     * @throws ReferenceException
     * @throws SystemException
     * @throws ArgumentException
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
            $value = trim($value);
            
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
        ];
        
        $select = [self::FIELD_EXTERNAL_KEY];
        
        if ($this->returnFieldName) {
            $select[] = $this->returnFieldName;
        }
        
        $exists = $this->getDataClass()::getList([
                                                     'filter' => [self::FIELD_EXTERNAL_KEY => $externalKey],
                                                     'select' => $select,
                                                 ])->fetch();
        
        if ($exists[$this->returnFieldName ?: self::FIELD_EXTERNAL_KEY]) {
            return $exists[$this->returnFieldName ?: self::FIELD_EXTERNAL_KEY];
        }
        
        $result = $this->getDataClass()::add($fields);
        
        if (!$result->isSuccess()) {
            throw new ReferenceException('Reference value add error: ' . implode(', ', $result->getErrorMessages()));
        }
        
        self::$referenceValues[$this->getReferenceCode()][] = $fields;
        
        return $this->returnFieldName ? $result->getId() : $externalKey;
    }
    
    /**
     * @param $value
     * @param $fieldToSearch
     *
     * @return mixed
     *
     * @throws ArgumentException
     * @throws SystemException
     * @throws ReferenceException
     */
    protected function searchValue($value, $fieldToSearch)
    {
        $referenceValues = $this->getReferenceValues();
        
        $position = array_search($value,
                                 array_column($referenceValues, $fieldToSearch),
                                 true);
        
        return $position
               === false ? '' : $referenceValues[$position][$this->returnFieldName ?: self::FIELD_EXTERNAL_KEY];
    }
    
    /**
     * @return array
     *
     * @throws ArgumentException
     * @throws SystemException
     * @throws ReferenceException
     */
    protected function getReferenceValues() : array
    {
        if (!self::$referenceValues[$this->getReferenceCode()]) {
            self::$referenceValues[$this->getReferenceCode()] = $this->getDataClass()::getList()->fetchAll();
        }
        
        return self::$referenceValues[$this->getReferenceCode()];
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
        if (!Loader::includeModule('highloadblock')) {
            throw new LoaderException('Module highloadblock must be installed');
        }
        
        $this->setFieldToSearch();
        
        parent::__construct($fieldName);
    }
}
