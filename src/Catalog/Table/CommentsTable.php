<?php

namespace FourPaws\Catalog\Table;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class TrainingAppsTable
 *
 * @package Bitrix\Iblock
 **/
class CommentsTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'adv_comments';
    }
    
    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            'ID'           => [
                'data_type' => 'integer',
                'primary'   => true,
            ],
            'UF_USER_ID'   => [
                'data_type' => 'integer',
            ],
            'UF_TEXT'      => [
                'data_type' => 'string',
            ],
            'UF_MARK'      => [
                'data_type' => 'integer',
            ],
            'UF_ACTIVE'    => [
                'data_type' => 'string',
            ],
            'UF_OBJECT_ID' => [
                'data_type' => 'integer',
            ],
            'UF_TYPE'      => [
                'data_type' => 'string',
            ],
            'UF_DATE'      => [
                'data_type' => 'string',
            ],
            'UF_XML_ID'    => [
                'data_type' => 'string',
            ],
            'UF_PHOTOS'    => [
                'data_type' => 'string',
            ],
        ];
    }
}
