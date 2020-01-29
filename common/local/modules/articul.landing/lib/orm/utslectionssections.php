<?php

namespace Articul\Landing\Orm;

use Bitrix\Main\Loader;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class UtsLectionsSectionsTable
 **/
class UtsLectionsSectionsTable extends DataManager
{
    public static $iblockCode = 'flagman_lections';
    
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        $id = self::getIblockId();
        
        return 'b_uts_iblock_' . $id . '_section';
    }
    
    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'VALUE_ID' => array(
                'data_type' => 'integer',
                'primary' => true,
            ),
            'UF_LECTION_ADDRESS' => array(
                'data_type' => 'string',
                'primary' => true,
            ),
        );
    }
    
    public static function getIblockId()
    {
        return \CIBlock::GetList([], ['CODE' => self::$iblockCode])->Fetch()['ID'];
    }
}
