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
use FourPaws\SaleBundle\Discount\Utils\DiscountDisjunction;
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
    use DiscountDisjunction;

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
            'множитель',
            $arAtoms['Multiplier'],
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
            'Multiplier' => [
                'JS' => [
                    'id' => 'Multiplier',
                    'name' => 'Multiplier',
                    'type' => 'input',
                    'defaultText' => 1,
                    'defaultValue' => 1,
                ],
                'ATOM' => [
                    'ID' => 'Multiplier',
                    'FIELD_TYPE' => 'int',
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
                        'only_first' => 'только первый',
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
                        'array_sum' => 'сумма',
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
     * Черт ногу сломит. Есть мнение что стоит генерировать что-то типа self::check($arOneCondition)
     *
     * @param $parameters
     * @param $arParams
     * @param $arControl
     * @param array|bool $arSubs
     *
     * @return string
     */
    public static function Generate($parameters, $arParams, $arControl, $arSubs = false): string
    {
        $result = '';

        /**
         * @todo обработать все варианты параметров
         */
        if (
            \in_array($parameters['Filtration_operator'], ['separate', 'union', 'only_first'], true)
            && \in_array($parameters['Count_operator'], ['min', 'max', 'single', 'array_sum'], true)
            && \is_array($arSubs) && \count($arSubs) >= 1
        ) {
            if ($parameters['Count_operator'] === 'single') {
                $countOperator = '(int)(bool)array_sum';
                if ($parameters['All'] === 'AND') {
                    $countOperator = '(int)(bool)min';
                }
            } else {
                $countOperator = $parameters['Count_operator'];
            }
            $orderVar = $arParams['ORDER'];
            $result = '$counts = []; $i = 0; $originalOrder = ' . $orderVar . ';' . PHP_EOL;
            foreach ($arSubs as $sub) {
                $result .= '$counts[$i][\'cnt\'] = ' . $sub . PHP_EOL;
                $result .= '$counts[$i++][\'res\'] = ' . $orderVar . ';' . PHP_EOL;
                $result .= $orderVar . ' = $originalOrder;' . PHP_EOL;
            }
            $result .= '$applyCount = ' . $countOperator . '(array_column($counts, \'cnt\'));' . PHP_EOL;

            if ($parameters['All'] === 'AND') {
                $result .= '$minCount = min(array_column($counts, \'cnt\'));' . PHP_EOL;
                $result .= '$applyCount = $minCount > 0 ? $applyCount : 0;' . PHP_EOL;
            }

            if ($parameters['Filtration_operator'] === 'union') {
                $result .= static::class . '::unionFilterResults($counts);' . PHP_EOL;
            }

            $result .= '$premises = ' . static::class . '::calcPremises($counts, \'' . $parameters['All'] . '\', $applyCount);' . PHP_EOL;

            $result .= 'foreach($counts as $k => $elem) {' . PHP_EOL;
            $result .= '    ' . $orderVar . ' = $elem[\'res\'];' . PHP_EOL;
            $result .= '    ' . static::class . '::apply(' . $orderVar . ', ';
            $result .= var_export($parameters['Value'], true) . ', ';
            $result .= var_export($parameters['Type'] === 'percent', true) . ', ';
            $result .= '$applyCount * (int)' . var_export($parameters['Multiplier'], true) . ', $premises);' . PHP_EOL;
            if ($parameters['Filtration_operator'] === 'only_first') {
                $result .= 'break;' . PHP_EOL;
            }
            $result .= '}' . PHP_EOL;
            $result .= $orderVar . ' = $originalOrder;' . PHP_EOL;
        }

        return $result;
    }

    /**
     *
     *
     * @param array $results
     * @param string $operator
     * @param int $applyCount
     *
     * @return array
     */
    public static function calcPremises(array $results, string $operator, int $applyCount): array
    {
        $previousItems = $premises = [];
        if ($operator === 'OR') {
            foreach ($results as &$res) {
                $previousItems = self::discountDisjunction($previousItems, $res['res']['BASKET_ITEMS']);
                $res['res']['BASKET_ITEMS'] = $previousItems;
            }
        }
        unset($res);
        foreach ($results as $res) {
            foreach ($res['res']['BASKET_ITEMS'] as $basketCode => $basketItem) {
                foreach ($basketItem['DISCOUNT_GROUPS'] as $groupId => $p) {
                    if ($groupId > $applyCount) {
                        break;
                    }
                    $premises[$basketItem['PRODUCT_ID']] += $p;
                }
            }
        }
        return $premises;
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
     * @param array $premises
     */
    public static function apply(array $order, float $discountValue, bool $percent, int $applyCount, array $premises)
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
                        'apply_count' => $applyCount,
                        'premises' => $premises,
                    ]
                ]),
            ];
            /**
             * @todo подобные фильтры должны быть в фильтре
             */
            /** @var array $applyBasket */
            $applyBasket = array_filter($order['BASKET_ITEMS'], [Actions::class, 'filterBasketForAction']);
        }

        if (!$applyBasket || !$actionDescription) {
            return;
        }

        Actions::increaseApplyCounter();
        Actions::setActionDescription(Actions::RESULT_ENTITY_BASKET, $actionDescription);

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
            if($detachQuantity <= 0) {
                break;
            };
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
                'BASKET_CODE' => $basketCode,
            ];
            Actions::setActionResult(Actions::RESULT_ENTITY_BASKET, $rowActionDescription);
        }
    }
}