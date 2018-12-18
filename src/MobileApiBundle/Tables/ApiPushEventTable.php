<?php

namespace FourPaws\MobileApiBundle\Tables;

use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\Event;
use Bitrix\Main\Entity\EventResult;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\TextField;
use Bitrix\Main\Type\DateTime;

class ApiPushEventTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'api_push_event';
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ObjectException
     * @throws \Exception
     */
    public static function getMap(): array
    {
        return [
            'ID' => new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            'PLATFORM' => new StringField('PLATFORM', [
                'required' => true,
            ]),
            'TOKEN' => new StringField('TOKEN', [
                'required' => true,
            ]),
            'DATE_TIME_EXEC' => new DatetimeField('DATE_TIME_EXEC', [
                'default_value' => new DateTime(),
            ]),
            'MESSAGE' => new TextField('MESSAGE', [
                'required' => true,
            ]),
            'SUCCESS_EXEC' => new StringField('SUCCESS_EXEC', []),
            'VIEWED' => new StringField('VIEWED', []),
        ];
    }
}
