<?php

namespace FourPaws\Migrator;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Entity\Query;

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
     */
    public static function getIblockId($type, $code)
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
     */
    public static function getIblockXmlId($type, $code)
    {
        return trim(self::getIblockField($type, $code, 'XML_ID'));
    }
    
    /**
     * @param $type
     * @param $code
     * @param $field
     *
     * @return string
     * @throws IblockNotFoundException
     */
    private static function getIblockField($type, $code, $field)
    {
        $type = trim($type);
        $code = trim($code);
        
        if ($type == '' || $code == '') {
            throw new \InvalidArgumentException('Iblock type and code must be specified');
        }
        
        //Перед тем, как ругаться, что инфоблок не найден, попытаться перезапросить информацию из базы
        if (!isset(self::getAllIblockInfo()[$type][$code])) {
            self::$iblockInfo = null;
        }
        
        if (isset(self::getAllIblockInfo()[$type][$code])) {
            return trim(self::getAllIblockInfo()[$type][$code][$field]);
        }
        
        throw new IblockNotFoundException(
            sprintf(
                'Iblock `%s\%s` not found',
                $type,
                $code
            )
        );
        
    }
    
    /**
     * Возвращает краткую информацию обо всех инфоблоках в виде многомерного массива.
     *
     * @return array <iblock type> => <iblock code> => array of iblock fields
     */
    private static function getAllIblockInfo()
    {
        if (is_null(self::$iblockInfo)) {
            $iblockList = (new Query(IblockTable::getEntity()))
                ->setSelect(['ID', 'IBLOCK_TYPE_ID', 'CODE', 'XML_ID'])
                ->exec();
            $iblockInfo = [];
            while ($iblock = $iblockList->fetch()) {
                $iblockInfo[$iblock['IBLOCK_TYPE_ID']][$iblock['CODE']] = $iblock;
            }
            
            self::$iblockInfo = $iblockInfo;
        }
        
        return self::$iblockInfo;
    }
}