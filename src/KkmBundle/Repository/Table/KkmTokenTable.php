<?php

namespace FourPaws\KkmBundle\Repository\Table;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;

class KkmTokenTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return '4lapy_kkm_token';
    }

    /**
     * @return array
     */
    public static function getMap(): array
    {
        return [
            new IntegerField('id', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new StringField('token', [
                'required' => true,
            ])
        ];
    }
}