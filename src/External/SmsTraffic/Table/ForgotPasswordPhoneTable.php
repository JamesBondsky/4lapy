<?php

namespace FourPaws\Search\Table;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;

class FourPawsSmsProtectorTable extends DataManager
{
    /**
     * @inheritdoc
     */
    public static function getTableName()
    {
        return '4lapy_sms_protector';
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
            'phone' => new StringField(
                'phone', [
                    'required' => true,
                    'title' => 'Телефон',
                ]
            ),
            'date' => new DateTimeField(
                'date',
                [
                    'required' => true,
                    'title' => 'Дата последней отправки смс на регистрацию',
                ]
            )
        ];
    }

    /**
     * @param int $phone
     * @param null $currentTime
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function canSend(int $phone, $currentTime = null): bool
    {
        if (is_null($currentTime)) {
            $currentTime = new \DateTime();
        }
        $canSend = true;
        $row = FourPawsSmsProtectorTable::GetList(
            [
                'filter' => [
                    [
                        'phone' => $phone
                    ]
                ],
                'limit' => 1
            ]
        )->fetch();
        if (!empty($row)) {
            if ($row['date']->modify('+1 minutes') <= $currentTime) {
                $canSend = false;
            } else {
                FourPawsSmsProtectorTable::Add(
                    [
                        'phone' => $phone,
                        'date' => $currentTime
                    ]
                );
            }
        } else {
            FourPawsSmsProtectorTable::Update(
                $row, [
                    'date' => $currentTime
                ]
            );
        }

        return $canSend;
    }
}
