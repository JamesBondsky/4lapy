<?php

namespace Articul\Landing\Orm;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class TrainingAppsTable
 *
 * @package Bitrix\Iblock
 **/

class TrainingAppsTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_hlbd_training_apps';
    }
    
    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
            ),
            'UF_USER_ID' => array(
                'data_type' => 'integer',
            ),
            'UF_NAME' => array(
                'data_type' => 'string',
            ),
            'UF_PHONE' => array(
                'data_type' => 'string',
            ),
            'UF_EVENT_ID' => array(
                'data_type' => 'integer',
            ),
            'UF_EMAIL' => array(
                'data_type' => 'string',
            )
        );
    }
}
