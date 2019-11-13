<?php

namespace Articul\Landing\Orm;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class ElementPropS21Table
 *
 * Fields:
 * <ul>
 * <li> IBLOCK_ELEMENT_ID int mandatory
 * <li> PROPERTY_114 string optional
 * </ul>
 *
 * @package Bitrix\Iblock
 **/
class UtsLectionsTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        $id = \CIBlock::GetList([], ['CODE' => 'flagman_lections'])->Fetch()['ID'];
        
        return 'b_iblock_element_prop_s' . $id;
    }
    
    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            'IBLOCK_ELEMENT_ID' => [
                'data_type' => 'integer',
                'primary'   => true,
                'title'     => Loc::getMessage('ELEMENT_PROP_S21_ENTITY_IBLOCK_ELEMENT_ID_FIELD'),
            ],
            'EVENT_DATE'        => [
                'data_type'   => 'string',
                'title'       => Loc::getMessage('ELEMENT_PROP_S3_ENTITY_PROPERTY_236_FIELD'),
                'column_name' => 'PROPERTY_236',
            ],
            'EVENT_TIME'        => [
                'data_type'   => 'string',
                'title'       => Loc::getMessage('ELEMENT_PROP_S3_ENTITY_PROPERTY_237_FIELD'),
                'column_name' => 'PROPERTY_237',
            ],
            'FREE_SITS'         => [
                'data_type'   => 'string',
                'title'       => Loc::getMessage('ELEMENT_PROP_S3_ENTITY_PROPERTY_238_FIELD'),
                'column_name' => 'PROPERTY_238',
            ],
            'SITS'              => [
                'data_type'   => 'string',
                'title'       => Loc::getMessage('ELEMENT_PROP_S3_ENTITY_PROPERTY_239_FIELD'),
                'column_name' => 'PROPERTY_239',
            ],
        ];
    }
}