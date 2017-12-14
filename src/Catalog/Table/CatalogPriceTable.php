<?php

namespace FourPaws\Catalog\Table;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\FloatField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;

class CatalogPriceTable extends DataManager
{
    /**
     * @inheritdoc
     */
    public static function getTableName()
    {
        return '4lp_catalog_price';
    }

    /**
     * @inheritdoc
     */
    public static function getMap()
    {
        return [
            'ID' => new IntegerField(
                'ID',
                [
                    'primary'      => true,
                    'autocomplete' => true,
                ]
            ),

            'ELEMENT_ID' => new IntegerField(
                'ELEMENT_ID',
                [
                    'required' => true,
                    'title'    => 'ID торгового предложения',
                ]
            ),

            'REGION_ID' => new StringField(
                'REGION_ID', [
                               'required' => true,
                               'size'     => 6,
                               'title'    => 'Символьный код региона',
                           ]
            ),

            'PRICE' => new FloatField(
                'PRICE', [
                           'required' => true,
                           'title'    => 'Цена без скидки',
                       ]
            ),

            'CURRENCY' => new StringField(
                'CURRENCY', [
                              'required' => true,
                              'title'    => 'Валюта',
                          ]
            ),

        ];
    }
}
