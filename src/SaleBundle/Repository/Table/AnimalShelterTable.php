<?php

namespace FourPaws\SaleBundle\Repository\Table;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\FloatField;

class AnimalShelterTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return '4lapy_animal_shelters';
    }

    /**
     * @return array
     */
    public static function getMap(): array
    {
        return [
            new IntegerField('id', [
                'primary'      => true,
                'autocomplete' => true,
            ]),
            new StringField('name', [
                'required' => true,
            ]),
            new StringField('description', [
                'required' => true,
            ]),
            new StringField('city', [
                'required' => true,
            ])
        ];
    }
}