<?php

namespace FourPaws\MobileApiBundle\Tables;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\Event;
use Bitrix\Main\Entity\EventResult;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Type\DateTime;

class ApiUserSessionTable extends DataManager
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
     * @throws \Bitrix\Main\ArgumentException
     * @return array
     */
    public static function getMap(): array
    {
        return [
            'ID'                   => new IntegerField('ID', [
                'primary'      => true,
                'autocomplete' => true,
            ]),
            'DATE_INSERT'          => new DatetimeField('DATE_INSERT', [
                'required'      => true,
                'default_value' => new DateTime(),
            ]),
            'DATE_UPDATE'          => new DatetimeField('DATE_UPDATE', [
                'required'      => true,
                'default_value' => new DateTime(),
            ]),
            'USER_AGENT'           => new StringField('USER_AGENT', [
                'default_value' => '',
            ]),
            'REMOTE_ADDR'          => new StringField('REMOTE_ADDR', [
                'default_value' => $_SERVER['REMOTE_ADDR'],
            ]),
            'HTTP_CLIENT_IP'       => new StringField('HTTP_CLIENT_IP', [
                'default_value' => $_SERVER['HTTP_CLIENT_IP'],
            ]),
            'HTTP_X_FORWARDED_FOR' => new StringField('HTTP_X_FORWARDED_FOR', [
                'default_value' => $_SERVER['HTTP_X_FORWARDED_FOR'],
            ]),
            'FUSER_ID'             => new IntegerField('FUSER_ID', [
                'default_value' => null,
            ]),
            'TOKEN'                => new StringField('TOKEN', [
                'required'      => true,
                'unique'        => true,
                'default_value' => md5(random_bytes(32)),
            ]),
            'PLATFORM'                => new StringField('PLATFORM', [
                'default_value' => '',
            ]),
            'PUSH_TOKEN'                => new StringField('PUSH_TOKEN', [
                'unique'        => true,
                'default_value' => '',
            ]),
            new ReferenceField(
                'FUSER',
                'Bitrix\Sale\Internals\Fuser',
                ['=this.FUSER_ID' => 'ref.ID'],
                ['join_type' => 'INNER']
            ),
            new ReferenceField(
                'USER',
                'Bitrix\Main\User',
                ['=ref.ID' => 'this.FUSER.USER_ID']
            ),

        ];
    }

    /**
     * @param Event $event
     *
     * @return EventResult
     */
    public static function onBeforeAdd(Event $event): EventResult
    {
        $result = new EventResult();
        static::uniqueToken($event, $result);
        return $result;
    }

    /**
     * @param Event       $event
     * @param EventResult $result
     */
    protected static function uniqueToken(Event $event, EventResult $result)
    {
        $data = $event->getParameter('fields');

        $token = $data['TOKEN'] ?? md5(random_bytes(32));
        do {
            $count = static::query()
                ->addFilter('=TOKEN', $token)
                ->exec()
                ->getSelectedRowsCount();
            if ($count > 0) {
                $token = md5(random_bytes(32));
            }
        } while ($count);
        $result->modifyFields(['TOKEN' => $token]);
    }
}
