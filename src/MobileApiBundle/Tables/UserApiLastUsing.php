<?php


namespace FourPaws\MobileApiBundle\Tables;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Type\DateTime;

class UserApiLastUsing extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'user_api_last_using';
    }

    public static function getMap()
    {
        return [
            'ID' => new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            'DATE_TIME_EXEC' => new DatetimeField('DATE_TIME_EXEC', [
                'default_value' => new DateTime(),
            ]),
        ];
    }
}
