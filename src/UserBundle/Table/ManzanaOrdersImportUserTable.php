<?php
namespace FourPaws\UserBundle\Table;

use Bitrix\Main;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;

/**
 * Class ManzanaOrdersImportUserTable
 *
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> user_id int mandatory
 * <li> datetime_insert datetime mandatory default 'CURRENT_TIMESTAMP'
 * </ul>
 *
 * @package FourPaws\UserBundle\Table
 **/

class ManzanaOrdersImportUserTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return '4lapy_manzana_orders_import_user';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws Main\SystemException
     */
    public static function getMap(): array
    {
        return array(
            'id' => new IntegerField(
                'id',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => 'ID записи',
                ]
            ),
            'user_id' => new IntegerField(
                'user_id',
                [
                    'unique' => true,
                    'title' => 'ID пользователя',
                ]
            ),
            'datetime_insert' => new DatetimeField(
                'datetime_insert',
                [
                    'required' => true,
                    'title' => 'Время добавления записи',
                ]
            ),
        );
    }
}