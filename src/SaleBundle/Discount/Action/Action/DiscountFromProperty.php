<?php
/**
 * Created by PhpStorm.
 * Date: 20.02.2018
 * Time: 14:04
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Action\Action;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Entity\ReferenceField;
use CSaleActionCtrlGroup;
use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\Iblock\IblockTable;


/**
 * Class DiscountFromProperty
 * @package FourPaws\SaleBundle\Discount\Action\Action
 */
class DiscountFromProperty extends \CSaleActionCtrlAction
{
    /** @var int */
    public static $idForFilter;

    /**
     *
     * @param $arOneCondition
     * @param $arParams
     * @param $arControl
     * @param bool $arSubs
     *
     * @return string
     */
    public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false): string
    {
        $condition = parent::Generate($arOneCondition, $arParams, $arControl, $arSubs);
        $result = '';
        if ($arOneCondition['Source_type'] === 'price') {
            $template = <<<'TEMPL'
            
    foreach ($arOrder['BASKET_ITEMS'] as $k => $row) {
        if (
            #CONDITION#
        ) {
            \#CLASS_FQN#::setIdForFilter($row['ID'] ?? $row['PRODUCT_ID']);
            if (is_array($row['CATALOG']['#SOURCE#'])) {
                $price = (float)current($row['CATALOG']['#SOURCE#']);
            } else {
                $price = (float)$row['CATALOG']['#SOURCE#'];
            }
            $params = [
                'VALUE' => $price - (float)$row['BASE_PRICE'],
                'UNIT' => 'F',
                'LIMIT_VALUE' => 0,
            ];
            \Bitrix\Sale\Discount\Actions::applyToBasket(
                $arOrder, $params, '\#CLASS_FQN#::filter'
            );
        }
    }
    
TEMPL;
            $result = str_replace(
                ['#CONDITION#', '#CLASS_FQN#', '#SOURCE#'],
                [$condition, self::class, $arOneCondition['Source']],
                $template
            );
        } elseif ($arOneCondition['Source_type'] === 'percent') {
            $template = <<<'TEMPL'
            
    foreach ($arOrder['BASKET_ITEMS'] as $k => $row) {
        if (
            #CONDITION#
        ) {
            \#CLASS_FQN#::setIdForFilter($row['ID'] ?? $row['PRODUCT_ID']);
            if (is_array($row['CATALOG']['#SOURCE#'])) {
                $discount = (float)current($row['CATALOG']['#SOURCE#']);
            } else {
                $discount = (float)$row['CATALOG']['#SOURCE#'];
            }
            $params = [
                'VALUE' => 0 - $discount,
                'UNIT' => 'P',
                'LIMIT_VALUE' => 0,
            ];
            \Bitrix\Sale\Discount\Actions::applyToBasket(
                $arOrder, $params, '\#CLASS_FQN#::filter'
            );
        }
    }
    
TEMPL;
            $result = str_replace(
                ['#CONDITION#', '#CLASS_FQN#', '#SOURCE#'],
                [$condition, self::class, $arOneCondition['Source']],
                $template
            );
        }
        return $result;
    }

    /**
     *
     *
     * @return array
     */
    public static function GetControlDescr(): array
    {
        $description = parent::GetControlDescr();
        ++$description['SORT'];
        return $description;
    }

    /**
     *
     *
     * @param $arControls
     *
     * @return array
     */
    public static function GetShowIn($arControls): array
    {
        return [CSaleActionCtrlGroup::GetControlID()];
    }

    /**
     *
     *
     * @return array|string
     */
    public static function GetControlID()
    {
        return 'ADV:DiscountFromProperty';
    }

    /**
     *
     *
     * @param $arParams
     *
     * @throws \Bitrix\Main\ArgumentException
     * @return array
     */
    public static function GetControlShow($arParams): array
    {
        $arAtoms = static::GetAtomsEx();
        return [
            'controlId' => static::GetControlID(),
            'group' => true,
            'label' => 'Установить цену из свойства',
            'defaultText' => 'BT_SALE_ACT_GROUP_BASKET_DEF_TEXT',
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'visual' => static::GetVisual(),
            'control' => [
                'Использовать свойство',
                $arAtoms['Source'],
                'содержащее',
                $arAtoms['Source_type'],
                'для товаров у которых',
                $arAtoms['All'],
                $arAtoms['True'],
                '(обязательно добавить то же свойство в условие)',
            ],
            'mess' => [
                'ADD_CONTROL' => 'Добавить условие',
                'SELECT_CONTROL' => 'Выберете условие'
            ]
        ];
    }

    /**
     *
     *
     * @throws \Bitrix\Main\ArgumentException
     * @return array
     */
    public static function GetAtoms(): array
    {
        return static::GetAtomsEx();
    }

    /**
     *
     *
     * @param bool $strControlID
     * @param bool $boolEx
     *
     * @throws \Bitrix\Main\ArgumentException
     * @return array
     */
    public static function GetAtomsEx($strControlID = false, $boolEx = false): array
    {
        $boolEx = (true === $boolEx);

        $res = PropertyTable::getList([
            'filter' => ['=PROPERTY_TYPE' => 'N', '=MULTIPLE' => 'N'],
            'select' => [
                'IBLOCK_ID',
                'IBLOCK_NAME' => 'IB.NAME',
                'PROPERTY_ID' => 'ID',
                'PROPERTY_NAME' => 'NAME',
            ],
            'order' => [
                'IBLOCK_ID' => 'ASC'
            ],
            'runtime' => [
                new ReferenceField(
                    'IB',
                    IblockTable::class,
                    ['=this.IBLOCK_ID' => 'ref.ID']
                ),
                new ReferenceField(
                    'CATALOG_IB',
                    CatalogIblockTable::class,
                    ['=this.IBLOCK_ID' => 'ref.IBLOCK_ID'],
                    ['join_type' => 'INNER']
                ),
            ],
        ]);
        $props = [];
        while ($elem = $res->fetch()) {
            // такой ключ для удобстава и для того чтобы сортировка в js не слетала
            $props['PROPERTY_' . $elem['PROPERTY_ID'] . '_VALUE'] = $elem['PROPERTY_NAME'] . "({$elem['IBLOCK_NAME']})";
        }

        $arAtomList = [
            'Source' => [
                'JS' => [
                    'id' => 'Source',
                    'name' => 'source',
                    'type' => 'select',
                    'values' => $props,
                    'defaultText' => 'выбрать',
                    'first_option' => '...'
                ],
                'ATOM' => [
                    'ID' => 'Source',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                ]
            ],
            'Source_type' => [
                'JS' => [
                    'id' => 'Source_type',
                    'name' => 'source_type',
                    'type' => 'select',
                    'values' => [
                        'price' => 'новую цену',
                        'percent' => 'скидку в процентах',
                    ],
                    'defaultText' => 'новую цену',
                    'defaultValue' => 'price',
                    'first_option' => '...'
                ],
                'ATOM' => [
                    'ID' => 'Source_type',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                ]
            ],
            'All' => [
                'JS' => [
                    'id' => 'All',
                    'name' => 'aggregator',
                    'type' => 'select',
                    'values' => [
                        'AND' => 'все условия',
                        'OR' => 'любое из условий'
                    ],
                    'defaultText' => 'все условия',
                    'defaultValue' => 'AND',
                    'first_option' => '...'
                ],
                'ATOM' => [
                    'ID' => 'All',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                ]
            ],
            'True' => [
                'JS' => [
                    'id' => 'True',
                    'name' => 'value',
                    'type' => 'select',
                    'values' => [
                        'True' => 'выполнено(ы)',
                        'False' => 'не выполнено(ы)'
                    ],
                    'defaultText' => 'выполнено(ы)',
                    'defaultValue' => 'True',
                    'first_option' => '...'
                ],
                'ATOM' => [
                    'ID' => 'True',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                ]
            ],
        ];

        if (!$boolEx) {
            foreach ($arAtomList as &$arOneAtom) {
                $arOneAtom = $arOneAtom['JS'];
            }
            unset($arOneAtom);
        }

        return $arAtomList;
    }

    /**
     *
     *
     * @param array $basketRow
     *
     * @return bool
     */
    public static function filter(array $basketRow): bool
    {
        /** avoiding 'use' */
        return (string)($basketRow['ID'] ?? $basketRow['PRODUCT_ID']) === self::getIdForFilter() && null !== self::getIdForFilter();
    }

    /**
     * @return string
     */
    public static function getIdForFilter(): string
    {
        return self::$idForFilter;
    }

    /**
     * @param string $idForFilter
     */
    public static function setIdForFilter(string $idForFilter)
    {
        self::$idForFilter = $idForFilter;
    }
}