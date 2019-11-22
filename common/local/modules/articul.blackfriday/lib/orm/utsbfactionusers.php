<?php

namespace Articul\BlackFriday\Orm;

use Bitrix\Main\Loader;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class UtsBFSectionsTable
 * @package Articul\Landing\Orm
 */
class UtsBFActionUsersTable extends DataManager
{
    public static $iblockCode = 'black_friday_action_user';
    
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        $id = self::getIblockId();
        
        return 'b_iblock_element_prop_s' . $id;
    }
    
    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        $id = self::getIblockId();
        
        $fields = [
            'IBLOCK_ELEMENT_ID' => [
                'data_type' => 'integer',
                'primary'   => true,
                'title'     => Loc::getMessage('ELEMENT_PROP_S' . $id . '_ENTITY_IBLOCK_ELEMENT_ID_FIELD'),
            ],
        ];
        
        $props = self::getProps($id);
        foreach ($props as $prop) {
            $fields[$prop['CODE']] = [
                'data_type'   => 'string',
                'title'       => Loc::getMessage('ELEMENT_PROP_S' . $id . '_ENTITY_PROPERTY_' . $prop['ID'] . '_FIELD'),
                'column_name' => 'PROPERTY_' . $prop['ID'],
            ];
        }
        
        return $fields;
    }
    
    public static function getIblockId()
    {
        return \CIBlock::GetList([], ['CODE' => self::$iblockCode])->Fetch()['ID'];
    }
    
    public static function getProps($id)
    {
        Loader::includeModule('iblock');
        
        return PropertyTable::query()
            ->setSelect(['ID', 'CODE'])
            ->setFilter(['=IBLOCK_ID' => $id])
            ->exec()
            ->fetchAll();
    }
    
}
