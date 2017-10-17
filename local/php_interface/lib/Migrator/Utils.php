<?php

namespace FourPaws\Migrator;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Entity\Query;
use Bitrix\Sale\Payment;

/**
 * Class Utils
 *
 * Утилиты для отвязки от проекта
 *
 * Стащено с IBlockUtils
 *
 * @package FourPaws\Migrator
 */
class Utils
{
    
    /**
     * @var array
     */
    private static $iblockInfo;
    
    /**
     * Возвращает id инфоблока по его типу и символьному коду
     *
     * @param string $type
     * @param string $code
     *
     * @return int
     * @throws IblockNotFoundException
     * @throws \InvalidArgumentException
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function getIblockId($type, $code) : int
    {
        return (int)self::getIblockField($type, $code, 'ID');
    }
    
    /**
     * Возвращает xml id инфоблока по его типу и символьному коду
     *
     * @param $type
     * @param $code
     *
     * @return string
     * @throws IblockNotFoundException
     * @throws \InvalidArgumentException
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function getIblockXmlId($type, $code) : string
    {
        return trim(self::getIblockField($type, $code, 'XML_ID'));
    }
    
    /**
     * @param $type
     * @param $code
     * @param $field
     *
     * @return string
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \InvalidArgumentException
     * @throws \FourPaws\Migrator\IblockNotFoundException
     */
    private static function getIblockField($type, $code, $field) : string
    {
        $type = trim($type);
        $code = trim($code);
        
        if (!$type || !$code) {
            throw new \InvalidArgumentException('Iblock type and code must be specified');
        }
        
        //Перед тем, как ругаться, что инфоблок не найден, попытаться перезапросить информацию из базы
        if (!isset(self::getAllIblockInfo()[$type][$code])) {
            self::$iblockInfo = null;
        }
        
        if (isset(self::getAllIblockInfo()[$type][$code])) {
            return trim(self::getAllIblockInfo()[$type][$code][$field]);
        }
        
        throw new IblockNotFoundException(sprintf('Iblock `%s\%s` not found',
                                                  $type,
                                                  $code));
        
    }
    
    /**
     * Возвращает краткую информацию обо всех инфоблоках в виде многомерного массива.
     *
     * @throws \Bitrix\Main\ArgumentException
     *
     * @return array <iblock type> => <iblock code> => array of iblock fields
     */
    private static function getAllIblockInfo() : array
    {
        if (self::$iblockInfo === null) {
            $iblockList = (new Query(IblockTable::getEntity()))->setSelect([
                                                                               'ID',
                                                                               'IBLOCK_TYPE_ID',
                                                                               'CODE',
                                                                               'XML_ID',
                                                                           ])->exec();
            $iblockInfo = [];
            while ($iblock = $iblockList->fetch()) {
                $iblockInfo[$iblock['IBLOCK_TYPE_ID']][$iblock['CODE']] = $iblock;
            }
            
            self::$iblockInfo = $iblockInfo;
        }
        
        return self::$iblockInfo;
    }
    
    /**
     * @param array $fields
     * @param array $replace
     *
     * @return array
     */
    public static function replaceFields(array $fields, array $replace) : array
    {
        if (empty($replace)) {
            return $fields;
        }
        
        $replacedFields = $fields;
        
        foreach ($fields as $name => $value) {
            if (array_key_exists($name, $replace)) {
                $replacedFields[$replace[$name]] = $replacedFields[$name];
                unset($replacedFields[$name]);
            }
        }
        
        return $replacedFields;
    }
    
    /**
     * @param array $fields
     * @param array $availableFields
     *
     * @return array
     */
    public static function clearFields(array $fields, array $availableFields) : array
    {
        foreach ($fields as $fieldName => $fieldValue) {
            if (!in_array($fieldName, $availableFields, true)) {
                unset($fields[$fieldName]);
            }
        }
        
        return $fields;
    }
    
    /**
     * @return array
     */
    public static function getPaymentDateFields() : array
    {
        return [
            'DATE_PAYED'       => 'datetime',
            'DATE_PAID'        => 'datetime',
            'PAY_VOUCHER_DATE' => 'date',
            'PS_RESPONSE_DATE' => 'datetime',
        ];
    }
    
    /**
     * @return array
     */
    public static function getPaymentReplaceFields() : array
    {
        return [
            'PAYED'        => 'PAID',
            'DATE_PAYED'   => 'DATE_PAID',
            'EMP_PAYED_ID' => 'EMP_PAID_ID',
        ];
    }
    
    /**
     * @return array
     */
    public static function getPaymentAvailableFields() : array
    {
        return Payment::getAvailableFields();
    }
}
