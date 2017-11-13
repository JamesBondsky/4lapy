<?php

namespace FourPaws\MobileApiBundle\Tables;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\Event;
use Bitrix\Main\Entity\EventResult;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Type\DateTime;

class UserSessionTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'api_user_session';
    }

    /**
     * @throws \Bitrix\Main\ObjectException
     * @return array
     */
    public static function getMap(): array
    {
        return [
            'ID'          => new IntegerField('ID', [
                'primary'      => true,
                'autocomplete' => true,
            ]),
            'DATE_INSERT' => new DatetimeField('DATE_INSERT', [
                'required'      => true,
                'default_value' => new DateTime(),
            ]),
            'DATE_UPDATE' => new DatetimeField('DATE_UPDATE', [
                'required'      => true,
                'default_value' => new DateTime(),
            ]),
            'USER_ID'     => new IntegerField('USER_ID', [
                'default_value' => null,
            ]),
            'USER_AGENT'  => new StringField('USER_AGENT', [
                'default_value' => '',
            ]),
            'FUSER_ID'    => new IntegerField('FUSER_ID', [
                'default_value' => null,
            ]),
            'TOKEN'       => new StringField('TOKEN', [
                'required' => true,
            ]),
        ];
    }

    /**
     * @param Event $event
     * @throws \Bitrix\Main\ObjectException
     * @return EventResult
     */
    public static function onBeforeUpdate(Event $event): EventResult
    {
        $result = new EventResult();
        static::dateUpdate($event, $result);
        return $result;
    }

    /**
     * @param Event       $event
     * @param EventResult $result
     * @throws \Bitrix\Main\ObjectException
     */
    protected static function dateUpdate(Event $event, EventResult $result)
    {
        $data = $event->getParameter('fields');

        if (!isset($data['DATE_UPDATE'])) {
            $result->modifyFields(['DATE_UPDATE' => new DateTime()]);
        }
    }
}
