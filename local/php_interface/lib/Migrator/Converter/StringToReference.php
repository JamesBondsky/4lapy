<?php

namespace FourPaws\Migrator\Converter;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

class StringToReference extends AbstractConverter
{
    private        $referenceCode;
    
    private static $referenceValues = [];
    
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
     * @param mixed  $value
     * @param string $fieldToSearch
     *
     * @return mixed
     */
    public function convert($value, $fieldToSearch = 'UF_NAME')
    {
        return $this->searchValue($value, $fieldToSearch);
    }
    
    /**
     * @param $value
     * @param $fieldToSearch
     *
     * @return mixed
     */
    protected function searchValue($value, $fieldToSearch)
    {
        $referenceValues = $this->getReferenceValues();
        
        return $referenceValues[$fieldToSearch][array_search($value,
                                                             array_column($referenceValues, $fieldToSearch),
                                                             true)];
    }
    
    /**
     * @return array
     *
     * @throws \Exception
     */
    private function getReferenceValues() : array
    {
        if (!self::$referenceValues) {
            $table = HighloadBlockTable::getList(['filter' => ['=NAME' => $this->getReferenceCode()]])->fetch();
            
            if (!$table) {
                /**
                 * @todo придумать сюда нормальный Exception
                 */
                throw new \Exception('Highloadblock with name ' . $this->getReferenceCode() . ' is not found.');
            }
            
            $entity         = HighloadBlockTable::compileEntity($table);
            $referenceClass = $entity->getDataClass();
            
            self::$referenceValues = $referenceClass::getList()->fetchAll();
        }
        
        return self::$referenceValues;
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
        
        parent::__construct($fieldName);
    }
}