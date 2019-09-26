<?php


namespace FourPaws\MobileApiBundle\Tables;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\StringField;

class ManzanaContactIdTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'manzana_contact_id';
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
            'USER_PHONE' => new IntegerField('USER_PHONE', [
                'required' => true,
            ]),
            'CONTACT_DATA' => new StringField('CONTACT_DATA', [
                'required' => true,
            ]),
        ];
    }
}
