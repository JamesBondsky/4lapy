<?php


namespace FourPaws\MobileApiBundle\Tables;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\StringField;

class UserApiLastUsingTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'user_api_last_using';
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getMap()
    {
        return [
            'ID' => new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            'USER_ID' => new IntegerField('USER_ID', [
                'required' => false,
            ]),
            'DATE_INSERT'          => new DatetimeField('DATE_INSERT', [
                'required'      => true,
                'default_value' => new DateTime(),
            ]),
        ];
    }
}
