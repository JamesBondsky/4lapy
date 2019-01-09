<?php

namespace FourPaws\Entity\Sms;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;

class LogTable extends DataManager
{
    /**
     * @inheritdoc
     */
    public static function getTableName()
    {
        return '4lapy_sms_log';
    }

    /**
     * @inheritdoc
     */
    public static function getMap()
    {
        return [
            'id' => new IntegerField(
                'id',
                [
                    'primary' => true,
                    'autocomplete' => true,
                ]
            ),
            'phone' => new IntegerField(
                'phone', [
                    'required' => true,
                    'title' => 'Телефон',
                ]
            ),
            'date' => new DateTimeField(
                'date',
                [
                    'required' => true,
                    'title' => 'Дата отправки смс',
                ]
            )
        ];
    }


    /**
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getCurrentDayCount() {
        return static::getAllCount((new \DateTime())->modify('midnight'));
    }

    /**
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getCurrentHourCount() {
        return static::getAllCount((new \DateTime())->setTime(date('G'), 0, 0));
    }

    /**
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getAllCount(\DateTime $date) {
        return LogTable::getList([
            'select' => ['CNT'],
            'filter' => [
                '>date' => DateTime::createFromPhp($date),
            ],
            'runtime' => [
                new Entity\ExpressionField('CNT', 'COUNT(*)')
            ]
        ])->fetch()['CNT'];
    }

    public static function getByPhoneCount(int $phone, \DateTime $dateFrom = null) {
        if (is_null($dateFrom)) {
            $dateFrom = (new \DateTime())->modify('-1 minute');
        }

        return LogTable::getList([
            'select' => ['CNT'],
            'filter' => [
                'phone' => $phone,
                '>date' => DateTime::createFromPhp($dateFrom),
            ],
            'runtime' => [
                new Entity\ExpressionField('CNT', 'COUNT(*)')
            ]
        ])->fetch()['CNT'];
    }

    /**
     * @param int $phone
     * @return bool
     * @throws \Exception
     */
    public static function addByPhone(int $phone) {
        LogTable::Add([
            'phone' => $phone,
            'date' => new DateTime(),
        ]);

        return true;
    }

}
