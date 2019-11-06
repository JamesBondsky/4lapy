<?php

namespace Articul\Landing\Orm;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class LectionAppsTable
 *
 * @package Bitrix\Iblock
 **/

class LectionAppsTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_hlbd_lection_apps';
    }
    
    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'UF_USER_ID' => array(
                'data_type' => 'integer',
                'primary' => true,
            ),
            'UF_NAME' => array(
                'data_type' => 'string',
            ),
            'UF_PHONE' => array(
                'data_type' => 'string',
            ),
            'UF_EVENT_ID' => array(
                'data_type' => 'int',
            )
        );
    }
}
