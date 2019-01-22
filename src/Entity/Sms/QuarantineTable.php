<?php

namespace FourPaws\Entity\Sms;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Type\DateTime;

class QuarantineTable extends DataManager
{

    /**
     * @inheritdoc
     */
    public static function getTableName()
    {
        return '4lapy_sms_quarantine';
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
            'dateFrom' => new DateTimeField(
                'date_from',
                [
                    'required' => true,
                    'title' => 'Дата входа в карантин',
                ]
            ),
            'dateTo' => new DateTimeField(
                'date_to',
                [
                    'required' => true,
                    'title' => 'Дата выхода из карантина',
                ]
            )
        ];
    }

    /**
     * @param int $phone
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function isInQuarantine(int $phone) {
        $isInQuarantine = false;

        $arQuarantine = QuarantineTable::getList([
            'select' => [
                'phone',
                'dateTo',
            ],
            'filter' => [
                '>=dateTo' => new DateTime(),
                'phone' => $phone,
            ],
            'limit' => 1,
        ])->fetch();

        if ($arQuarantine) {
            $isInQuarantine = true;
        }

        return $isInQuarantine;
    }

    /**
     * @param int $phone
     * @param \DateTime|null $dateTo
     * @return bool
     * @throws \Exception
     */
    public static function addToQuarantine(int $phone, \DateTime $dateTo = null) {
        if (is_null($dateTo)) {
            $dateTo = (new \DateTime)->modify('+5 minutes');
        }

        QuarantineTable::add([
            'phone' => $phone,
            'dateFrom' => new DateTime(),
            'dateTo' => DateTime::createFromPhp($dateTo),
        ]);

        return true;
    }
}
