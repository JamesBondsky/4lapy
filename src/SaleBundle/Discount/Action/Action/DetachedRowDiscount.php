<?php
/**
 * Created by PhpStorm.
 * Date: 06.03.2018
 * Time: 20:43
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Action\Action;


use Bitrix\Sale\Discount\Actions;
use Bitrix\Sale\OrderDiscountManager;
use CSaleActionCtrlAction;
use FourPaws\SaleBundle\Discount\Utils\SortByKeyTrait;
use FourPaws\SaleBundle\Discount\Utils\ValidateAtoms;

/**
 * Class DetachedRowDiscount
 * @package FourPaws\SaleBundle\Discount\Action\Action
 */
class DetachedRowDiscount extends CSaleActionCtrlAction
{
    use ValidateAtoms;
    use SortByKeyTrait;

    /**
     *
     *
     * @return array|string
     */
    public static function GetControlID(): string
    {
        return 'ADV:DetachedRowDiscount';
    }

    /**
     *
     *
     * @return array
     */
    public static function GetControlDescr(): array
    {
        $controlDescr = parent::GetControlDescr();
        $controlDescr['FORCED_SHOW_LIST'] = [
            'ADV:BasketFilterBasePriceRatio',
            'ADV:BasketFilterQuantityMore',
            'ADV:BasketFilterQuantityRatio',
        ];
        $controlDescr['SORT'] = 302;
        return $controlDescr;
    }

    /**
     *
     * @param $arControls
     *
     * @return array
     */
    public static function GetShowIn($arControls): array
    {
        return ['CondGroup'];
    }

    /**
     *
     * @param $arParams
     *
     * @return array
     */
    public static function GetControlShow($arParams): array
    {
        //todo Ключ 'visual' походу отвечает за оформление зеленой штучки с надписью "и"/"или" между группами

        $arAtoms = static::GetAtomsEx();
        $description = parent::GetControlShow($arParams);
        $description['label'] = 'Предоставить скидку на определенное количество товара';
        $description['containsOneAction'] = false;
        $description['mess'] = [
            'ADD_CONTROL' => 'Добавить условие',
            'SELECT_CONTROL' => 'Выбрать условие'
        ];
        $description['control'] = [
            'Предоставить скидку',
            $arAtoms['Value'],
            $arAtoms['Type'],
            'результаты фильтрации',
            $arAtoms['Filtration_operator'],
            'количество применений',
            $arAtoms['Count_operator'],
            $arAtoms['All'],
            'выполнены. доп JSON',
            $arAtoms['Additional_JSON'],
        ];

        return $description;
    }

    /**
     *
     * @param $arParams
     *
     * @return array|string
     */
    public static function GetConditionShow($arParams)
    {
        $result = false;

        if (isset($arParams['ID']) && $arParams['ID'] === static::GetControlID()) {
            $arControl = [
                'ID' => $arParams['ID'],
                'ATOMS' => static::GetAtomsEx(false, true)
            ];
            $result = static::CheckAtoms($arParams['DATA'], $arParams, $arControl, true);
        }

        return $result;
    }

    /**
     *
     * @param bool $strControlID
     * @param bool $boolEx
     *
     * @return array
     */
    public static function GetAtomsEx($strControlID = false, $boolEx = false): array
    {
        $boolEx = (true === $boolEx);

        $arAtomList = [
            'Value' => [
                'JS' => [
                    'id' => 'Value',
                    'name' => 'Value',
                    'type' => 'input'
                ],
                'ATOM' => [
                    'ID' => 'Value',
                    'FIELD_TYPE' => 'double',
                    'MULTIPLE' => 'N',
                    'VALIDATE' => ['>', 0]
                ]
            ],
            'Type' => [
                'JS' => [
                    'id' => 'Type',
                    'name' => 'Type',
                    'type' => 'select',
                    'values' => [
                        'percent' => '%',
                        'absolute' => 'RUB',
                    ],
                    'defaultText' => '%',
                    'defaultValue' => 'percent',
                    'first_option' => '...'
                ],
                'ATOM' => [
                    'ID' => 'Type',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                ]
            ],
            'Filtration_operator' => [
                'JS' => [
                    'id' => 'Filtration_operator',
                    'name' => 'Filtration_operator',
                    'type' => 'select',
                    'values' => [
                        'separate' => 'обработать по отдельности',
                        'union' => 'объединение',
                        //'intersect' => 'пересечение', //todo
                    ],
                    'defaultText' => 'обработать по отдельности',
                    'defaultValue' => 'separate',
                    'first_option' => '...'
                ],
                'ATOM' => [
                    'ID' => 'Filtration_operator',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list'
                ]
            ],
            'Count_operator' => [
                'JS' => [
                    'id' => 'Count_operator',
                    'name' => 'Count_operator',
                    'type' => 'select',
                    'values' => [
                        'min' => 'минимальное значение',
                        'max' => 'максимальное значение',
                        //'sum' => 'сумма', //todo
                        'single' => 'одно',
                    ],
                    'defaultText' => 'минимальное значение',
                    'defaultValue' => 'min',
                    'first_option' => '...'
                ],
                'ATOM' => [
                    'ID' => 'Count_operator',
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
            'Additional_JSON' => [
                'JS' => [
                    'id' => 'Additional_JSON',
                    'name' => 'Additional_JSON',
                    'type' => 'input'
                ],
                'ATOM' => [
                    'ID' => 'Additional_JSON',
                    'FIELD_TYPE' => 'string',
                    'MULTIPLE' => 'N',
                    'VALIDATE' => ''
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
     * @return array
     */
    public static function GetAtoms(): array
    {
        return static::GetAtomsEx();
    }

    /**
     *
     * @param $arOneCondition
     * @param $arParams
     * @param $arControl
     * @param array|bool $arSubs
     *
     * @return string
     */

    public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false): string
    {
        $result = '';

        /**
         * @todo обработать все варианты параметров
         */
        if (
            \in_array($arOneCondition['Filtration_operator'], ['separate', 'union'], true)
            && \in_array($arOneCondition['Count_operator'], ['min', 'max', 'single'], true)
            && \is_array($arSubs) && \count($arSubs) >= 1
        ) {
            if ($arOneCondition['Count_operator'] === 'single') {
                $arOneCondition['Count_operator'] = '(int)(bool)min';
            }
            $result = '$counts = []; $i = 0; $originalOrder = ' . $arParams['ORDER'] . ';' . PHP_EOL;
            foreach ($arSubs as $sub) {
                $result .= '$counts[$i][\'cnt\'] = ' . $sub . PHP_EOL;
                $result .= '$counts[$i++][\'res\'] = ' . $arParams['ORDER'] . ';' . PHP_EOL;
                $result .= $arParams['ORDER'] . ' = $originalOrder;' . PHP_EOL;
            }
            $result .= '$applyCount = ' . $arOneCondition['Count_operator'] . '(array_column($counts, \'cnt\'));' . PHP_EOL;

            if ($arOneCondition['All'] === 'AND') {
                $result .= '$minCount = min(array_column($counts, \'cnt\'));' . PHP_EOL;
                $result .= '$applyCount = $minCount > 0 ? $applyCount : 0;' . PHP_EOL;
            }

            if ($arOneCondition['Filtration_operator'] === 'union') {
                $result .= static::class . '::unionFilterResults($counts);' . PHP_EOL;
            }
            $result .= 'foreach($counts as $k => $elem) {' . PHP_EOL;
            $result .= '    ' . $arParams['ORDER'] . ' = $elem[\'res\'];' . PHP_EOL;
            $result .= '    ' . static::class . '::apply(' . $arParams['ORDER'] . ', ';
            $result .= var_export($arOneCondition['Value'], true) . ', ';
            $result .= var_export($arOneCondition['Type'] === 'percent', true) . ', ';
            $result .= '$applyCount);' . PHP_EOL;
            $result .= '}' . PHP_EOL;
            $result .= $arParams['ORDER'] . ' = $originalOrder;' . PHP_EOL;
        }

        return $result;
    }

    /**
     * @internal
     *
     * @param array $counts
     *
     */
    public static function unionFilterResults(array &$counts)
    {
        $result = [];
        foreach ($counts as $elem) {
            if (empty($result)) {
                $result = $elem;
            } else {
                $result['res']['BASKET_ITEMS'] += $elem['res']['BASKET_ITEMS'];
            }
        }
        $counts = [$result];
    }

    /**
     *
     *
     * @param array $order
     * @param float $discountValue
     * @param bool $percent
     * @param int $applyCount
     */
    public static function apply(array $order, float $discountValue, bool $percent, int $applyCount)
    {

        $applyBasket = null;
        $actionDescription = null;
        if (!empty($order['BASKET_ITEMS']) && \is_array($order['BASKET_ITEMS']) && $applyCount > 0) {

            $actionDescription = [
                'ACTION_TYPE' => OrderDiscountManager::DESCR_TYPE_SIMPLE,
                'ACTION_DESCRIPTION' => json_encode([
                    'discountType' => 'DETACH',
                    'params' => [
                        'discount_value' => $discountValue,
                        'percent' => $percent,
                        'apply_count' => $applyCount
                    ]
                ]),
            ];
            Actions::increaseApplyCounter();
            Actions::setActionDescription(Actions::RESULT_ENTITY_BASKET, $actionDescription);

            /** @var array $applyBasket */
            $applyBasket = array_filter($order['BASKET_ITEMS'], [Actions::class, 'filterBasketForAction']);
        }

        if (!$applyBasket || !$actionDescription) {
            return;
        }
        self::sortByKey($applyBasket, 'PRICE');
        foreach ($applyBasket as $basketCode => $basketRow) {
            $quantity = (int)$basketRow['QUANTITY'];
            if ($quantity > $applyCount) {
                $detachQuantity = $applyCount;
                $applyCount = 0;
            } else {
                $detachQuantity = $quantity;
                $applyCount -= $quantity;
            }
            $rowActionDescription = [
                'ACTION_TYPE' => OrderDiscountManager::DESCR_TYPE_SIMPLE,
                'ACTION_DESCRIPTION' => json_encode([
                    'discountType' => 'DETACH',
                    'params' => [
                        'discount_value' => $discountValue,
                        'percent' => $percent,
                        'apply_count' => $detachQuantity
                    ]
                ]),
                'BASKET_CODE' => $basketRow['ID'],
            ];
            Actions::setActionResult(Actions::RESULT_ENTITY_BASKET, $rowActionDescription);
        }
    }
}