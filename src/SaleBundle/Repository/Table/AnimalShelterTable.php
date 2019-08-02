<?php

namespace FourPaws\SaleBundle\Repository\Table;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\FloatField;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

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
            new StringField('barcode', [
                'required' => true,
            ]),
            new StringField('city', [
                'required' => true,
            ])
        ];
    }

    /**
     * @param $barcode
     *
     * @return array|null
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getByBarcode($barcode): ?array
    {
        $shelter = static::getList(
            [
                'filter' => ['barcode' => $barcode]
            ]
        )->fetch();

        return $shelter ?: null;
    }
}