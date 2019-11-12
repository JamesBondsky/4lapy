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

class UtsTrainingsTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_iblock_element_prop_s44';
    }
    
    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'IBLOCK_ELEMENT_ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'title' => Loc::getMessage('ELEMENT_PROP_S21_ENTITY_IBLOCK_ELEMENT_ID_FIELD'),
            ),
            'FREE_SITS' => array(
                'data_type' => 'string',
                'title' => Loc::getMessage('ELEMENT_PROP_S3_ENTITY_PROPERTY_240_FIELD'),
                'column_name' => 'PROPERTY_240',
            ),
            'SITS' => array(
                'data_type' => 'string',
                'title' => Loc::getMessage('ELEMENT_PROP_S3_ENTITY_PROPERTY_241_FIELD'),
                'column_name' => 'PROPERTY_241',
            )
        );
    }
}
