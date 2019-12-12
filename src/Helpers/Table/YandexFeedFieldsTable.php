<?php
namespace FourPaws\Helpers\Table;

use Bitrix\Main,
    Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity\Validator\Length;

Loc::loadMessages(__FILE__);

/**
 * Class YandexFeedFieldsUfGroupsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> VALUE string(255) mandatory
 * </ul>
 *
 **/

class YandexFeedFieldsTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName() : string
    {
        return 'b_hlbd_yandexfeedfields';
    }
    
    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap() : array
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => 'ID',
            ),
            'UF_TEXT' => array(
                'data_type' => 'string',
                'required' => true,
                'title' => 'TEXT',
            ),
            'UF_SORT' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'SORT',
            )
        );
    }
}