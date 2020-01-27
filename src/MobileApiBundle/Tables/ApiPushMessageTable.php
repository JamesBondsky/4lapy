<?php

namespace FourPaws\MobileApiBundle\Tables;

use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\Event;
use Bitrix\Main\Entity\EventResult;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\TextField;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application;

class ApiPushMessageTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'api_push_messages';
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
            'UF_ACTIVE' => new IntegerField('UF_ACTIVE', []),
            'UF_MESSAGE' => new StringField('UF_MESSAGE', []),
            'UF_TYPE' => new IntegerField('UF_TYPE', []),
            'UF_EVENT_ID' => new IntegerField('UF_EVENT_ID', []),
        ];
    }
}
