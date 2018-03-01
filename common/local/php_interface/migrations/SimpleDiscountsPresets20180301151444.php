<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\Iblock\PropertyTable;


/**
 * Class SimpleDiscountsPresets20180301151444
 * @package Sprint\Migration
 */
class SimpleDiscountsPresets20180301151444 extends SprintMigrationBase
{

    protected $description = 'Пресет простой скидки и рекламной цены';

    /**
     *
     *
     * @return bool|void
     */
    public function up()
    {
        $skuIblockId = 0;
        $propertyCodeToId =
            [
                'PRICE_ACTION', //Цена по акции 87
                'COND_FOR_ACTION', //Тип цены по акции 88
                'COND_VALUE' //Размер скидки на товар 89
            ];
        $res = CatalogIblockTable::query()
            ->where(['PRODUCT_IBLOCK_ID', '<>', 0])
            ->where(['SKU_PROPERTY_ID', '<>', 0])
            ->addSelect('IBLOCK_ID')
            ->exec();
        while ($elem = $res->fetch()) {
            $skuIblockId = $elem['IBLOCK_ID'];
        }

        $res = PropertyTable::query()
            ->whereIn('CODE',
                $propertyCodeToId
            )
            ->addSelect('ID')
            ->addSelect('CODE')
            ->exec();
        $propertyCodeToId = array_flip($propertyCodeToId);
        while ($elem = $res->fetch()) {
            $propertyCodeToId[$elem['CODE']] = (int)$elem['ID'];
        }

        \CSaleDiscount::Add([
            'LID' => 's1',
            'NAME' => 'Простая скидка',
            'ACTIVE_FROM' => '',
            'ACTIVE_TO' => '',
            'ACTIVE' => 'Y',
            'SORT' => '100',
            'PRIORITY' => '1',
            'LAST_DISCOUNT' => 'N',
            'LAST_LEVEL_DISCOUNT' => 'N',
            'XML_ID' => 'SimpleDiscountPreset',
            'CONDITIONS' =>
                [
                    'CLASS_ID' => 'CondGroup',
                    'DATA' =>
                        [
                            'All' => 'AND',
                            'True' => 'True',
                        ],
                    'CHILDREN' => [],
                ],
            'ACTIONS' =>
                [
                    'CLASS_ID' => 'CondGroup',
                    'DATA' => ['All' => 'AND'],
                    'CHILDREN' =>
                        [

                            [
                                'CLASS_ID' => 'ADV:DiscountFromProperty',
                                'DATA' =>
                                    [
                                        'Source' => 'PROPERTY_' . $propertyCodeToId['COND_VALUE'] . '_VALUE',
                                        'Source_type' => 'percent',
                                        'All' => 'AND',
                                        'True' => 'True',
                                    ],
                                'CHILDREN' =>
                                    [
                                        [
                                            'CLASS_ID' => 'CondIBProp:' . $skuIblockId . ':' . $propertyCodeToId['COND_VALUE'],
                                            'DATA' =>
                                                [
                                                    'logic' => 'Great',
                                                    'value' => 0.0,
                                                ],
                                        ],
                                        [
                                            'CLASS_ID' => 'CondIBProp:' . $skuIblockId . ':' . $propertyCodeToId['COND_FOR_ACTION'],
                                            'DATA' =>
                                                [
                                                    'logic' => 'Equal',
                                                    'value' => 'ZRBT',
                                                ],
                                        ],
                                    ],
                            ],
                        ],
                ],
            'USER_GROUPS' =>
                [2],
        ]);

        \CSaleDiscount::Add([
            'LID' => 's1',
            'NAME' => 'Рекламная цена',
            'ACTIVE_FROM' => '',
            'ACTIVE_TO' => '',
            'ACTIVE' => 'Y',
            'SORT' => '100',
            'PRIORITY' => '1',
            'LAST_DISCOUNT' => 'N',
            'LAST_LEVEL_DISCOUNT' => 'N',
            'XML_ID' => 'PromoPriceDiscountPreset',
            'CONDITIONS' =>
                [
                    'CLASS_ID' => 'CondGroup',
                    'DATA' =>
                        [
                            'All' => 'AND',
                            'True' => 'True',
                        ],
                    'CHILDREN' => [],
                ],
            'ACTIONS' =>
                [
                    'CLASS_ID' => 'CondGroup',
                    'DATA' =>
                        [
                            'All' => 'AND',
                        ],
                    'CHILDREN' =>
                        [
                            [
                                'CLASS_ID' => 'ADV:DiscountFromProperty',
                                'DATA' =>
                                    [
                                        'Source' => 'PROPERTY_' . $propertyCodeToId['PRICE_ACTION'] . '_VALUE',
                                        'Source_type' => 'price',
                                        'All' => 'AND',
                                        'True' => 'True',
                                    ],
                                'CHILDREN' =>
                                    [
                                        [
                                            'CLASS_ID' => 'CondIBProp:' . $skuIblockId . ':' . $propertyCodeToId['PRICE_ACTION'],
                                            'DATA' =>
                                                [
                                                    'logic' => 'Great',
                                                    'value' => 0.0,
                                                ],
                                        ],
                                        [
                                            'CLASS_ID' => 'CondIBProp:' . $skuIblockId . ':' . $propertyCodeToId['COND_FOR_ACTION'],
                                            'DATA' =>
                                                [
                                                    'logic' => 'Equal',
                                                    'value' => 'VKA0',
                                                ],
                                        ],
                                    ],
                            ],
                        ],
                ],
            'USER_GROUPS' => [2],
        ]);
    }

    /**
     *
     *
     * @return bool|void
     */
    public function down()
    {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $res = \CSaleDiscount::GetList(['ID' => 'ASC'], ['XML_ID' => 'SimpleDiscountPreset'], false, false, ['ID']);
        if ($elem = $res->Fetch()) {
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            \CSaleDiscount::Delete($elem['ID']);
        }

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $res = \CSaleDiscount::GetList(['ID' => 'ASC'], ['XML_ID' => 'PromoPriceDiscountPreset'], false, false, ['ID']);
        if ($elem = $res->Fetch()) {
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            \CSaleDiscount::Delete($elem['ID']);
        }
    }
}
