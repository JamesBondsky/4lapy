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
use FourPaws\App\Application;

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
            'PUSH_TOKEN' => new StringField('PUSH_TOKEN', [
                'required' => true,
            ]),
            'DATE_TIME_EXEC' => new DatetimeField('DATE_TIME_EXEC', [
                'default_value' => new DateTime(),
            ]),
            'MESSAGE_ID' => new IntegerField('MESSAGE_ID', [
                'required' => true,
            ]),
            'SUCCESS_EXEC' => new StringField('SUCCESS_EXEC', []),
            'VIEWED' => new BooleanField('VIEWED', []),
            'MD5'=> new StringField('MD5', []),
            new ReferenceField(
                'MESSAGE',
                (Application::getHlBlockDataManager('bx.hlblock.pushmessages'))::getEntity(),
                ['=ref.ID' => 'this.MESSAGE_ID']
            ),
        ];
    }

    /**
     * @param Event $event
     * @return EventResult
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function OnBeforeAdd(Event $event)
    {
        $result = new EventResult();
        $result->modifyFields([
            'MD5' => md5(serialize($event->getEntity()->getField('MESSAGE')))
        ]);
        return $result;
    }
}
