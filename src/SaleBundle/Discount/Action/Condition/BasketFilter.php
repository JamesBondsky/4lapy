<?php
/**
 * Created by PhpStorm.
 * Date: 25.01.2018
 * Time: 16:50
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Action\Condition;

use Bitrix\Sale\Discount\Actions;


/**
 * Class BasketFilter
 * @package FourPaws\SaleBundle\Discount
 */
class BasketFilter extends \CSaleCondCtrlBasketGroup
{

    /**
     *
     *
     * @return array
     */
    public static function GetControlID(): array
    {
        $result = [];
        $controls = self::GetControls();
        if (!empty($controls)) {
            $result = array_keys($controls);
        }
        return $result;
    }

    /**
     *
     * @param $arValues
     * @param $arParams - не используется и непонятно зачем нужен впинципе, данные есть и в других массивах.
     * @param $arControl
     * @param $boolShow
     *
     * @return array|bool
     */
    public static function ValidateAtoms($arValues, $arParams, $arControl, $boolShow)
    {
        $result = parent::ValidateAtoms(
            $arValues,
            $arParams,
            $arControl,
            $boolShow
        );
        $validationSkipList = ['list', 'element', 'section', 'iblock', 'user']; // дефолтные валидаторы
        if (
            \is_array($result) && !isset($result['err_cond']) && \is_array($arControl['ATOMS'])
        ) {
            foreach ($arControl['ATOMS'] as $atom) {
                $paramId = $atom['ATOM']['ID'];
                if (!isset($atom['ATOM']['VALIDATE']) || empty($atom['ATOM']['VALIDATE'])) {
                    $result['values'][$paramId] = $arValues[$paramId];
                    continue;
                }
                if (\in_array($atom['ATOM']['VALIDATE'], $validationSkipList, true)) {
                    continue;
                }
                if (
                    \in_array($atom['ATOM']['FIELD_TYPE'], ['double', 'int'], true)
                    && \is_array($atom['ATOM']['VALIDATE'])
                    && \count($atom['ATOM']['VALIDATE']) > 1
                ) {
                    [$operation, $operand] = $atom['ATOM']['VALIDATE'];
                    switch ($operation) {
                        case '>':
                            if ($arValues[$paramId] > $operand) {
                                $result['values'][$paramId] = $arValues[$paramId];
                            } else {
                                unset($result['values'][$paramId]);
                                $messages[] = 'Значение должно быть больше ' . $operand;
                            }
                            break;
                        case '<':
                            if ($arValues[$paramId] < $operand) {
                                $result['values'][$paramId] = $arValues[$paramId];
                            } else {
                                unset($result['values'][$paramId]);
                                $messages[] = 'Значение должно быть меньше ' . $operand;
                            }
                            break;
                        case '>=':
                            if ($arValues[$paramId] >= $operand) {
                                $result['values'][$paramId] = $arValues[$paramId];
                            } else {
                                unset($result['values'][$paramId]);
                                $messages[] = 'Значение должно быть больше или равно ' . $operand;
                            }
                            break;
                        case '<=':
                            if ($arValues[$paramId] <= $operand) {
                                $result['values'][$paramId] = $arValues[$paramId];
                            } else {
                                unset($result['values'][$paramId]);
                                $messages[] = 'Значение должно быть меньше или равно ' . $operand;
                            }
                            break;
                        default:
                            unset($result['values'][$paramId]);
                            $messages[] = 'Неизвестная операция для проверки значения.';
                    }
                }
            }
        }

        return $result;
    }


    /**
     *
     * @param bool|string $strControlID
     *
     * @return array|bool|mixed
     */
    public static function GetControls($strControlID = false)
    {
        $arAtoms = static::GetAtomsEx();

        $arControlList = [
            'ADV:BasketFilterBasePriceRatio' => [
                'ID' => 'ADV:BasketFilterBasePriceRatio',
                'LABEL' => 'Частное от суммы базовой стоимости товаров',
                'PREFIX' => 'Частное от суммы базовой стоимости товаров, удовлетворяющих',
                'SHOW_IN' => [],
                'VISUAL' => self::GetVisual(),
                'ATOMS' => $arAtoms['ADV:BasketFilterBasePriceRatio']
            ],
            'ADV:BasketFilterQuantityMore' => [
                'ID' => 'ADV:BasketFilterQuantityMore',
                'LABEL' => 'Общее количество товаров больше чем N',
                'PREFIX' => 'Суммарное количество товаров, удовлетворяющих',
                'SHOW_IN' => [],
                'VISUAL' => self::GetVisual(),
                'ATOMS' => $arAtoms['ADV:BasketFilterQuantityMore']
            ],
            'ADV:BasketFilterQuantityRatio' => [
                'ID' => 'ADV:BasketFilterQuantityRatio',
                'LABEL' => 'Частное от общего количества товаров',
                'PREFIX' => 'Частное от суммы количества товаров, удовлетворяющих',
                'SHOW_IN' => [],
                'VISUAL' => self::GetVisual(),
                'ATOMS' => $arAtoms['ADV:BasketFilterQuantityRatio']
            ],
        ];

        foreach ($arControlList as $k => $control) {
            $arControlList[$k]['MODULE_ID'] = 'sale';
            $arControlList[$k]['MODULE_ENTITY'] = 'sale';
            $arControlList[$k]['ENTITY'] = 'BASKET';
            $arControlList[$k]['GROUP'] = 'Y';
        }

        $result = false;
        if (!$strControlID) {
            $result = $arControlList;
        } elseif (isset($arControlList[$strControlID])) {
            $result = $arControlList[$strControlID];
        }

        return $result;
    }

    /**
     *
     *
     * @param $arParams
     *
     * @return array|bool
     */
    public static function GetControlShow($arParams)
    {
        $result = false;

        $controls = static::GetControls();
        if (!empty($controls) && \is_array($controls)) {
            $result = [];
            foreach ($controls as $oneControl) {
                $row = [
                    'controlId' => $oneControl['ID'],
                    'group' => true,
                    'label' => $oneControl['LABEL'],
                    'showIn' => $oneControl['SHOW_IN'],
                    'visual' => $oneControl['VISUAL'],
                    'control' => [],
                    'mess' => [
                        'ADD_CONTROL' => 'Добавить условие',
                        'SELECT_CONTROL' => 'Выбрать условие'
                    ],
                ];
                if (isset($oneControl['PREFIX'])) {
                    $row['control'][] = $oneControl['PREFIX'];
                }
                switch ($oneControl['ID']) {
                    case 'ADV:BasketFilterBasePriceRatio':
                        $row['control'][] = $oneControl['ATOMS']['All'];
                        $row['control'][] = 'и';
                        $row['control'][] = $oneControl['ATOMS']['Value'];
                        $row['control'][] = 'как минимум больше еденицы';
                        break;
                    case 'ADV:BasketFilterQuantityMore':
                        $row['control'][] = $oneControl['ATOMS']['All'];
                        $row['control'][] = 'больше';
                        $row['control'][] = $oneControl['ATOMS']['Value'];
                        $row['control'][] = 'как минимум на одну еденицу';
                        break;
                    case 'ADV:BasketFilterQuantityRatio':
                        $row['control'][] = $oneControl['ATOMS']['All'];
                        $row['control'][] = 'и';
                        $row['control'][] = $oneControl['ATOMS']['Value'];
                        $row['control'][] = 'как минимум больше еденицы';
                        break;
                }
                if (!empty($row['control'])) {
                    $result[] = $row;
                }
            }
        }

        return $result;
    }

    /**
     *
     * @param bool $strControlID
     * @param bool $boolEx
     *
     * @return array|bool
     */
    public static function GetAtomsEx($strControlID = false, $boolEx = false)
    {
        $atomList = [
            'ADV:BasketFilterBasePriceRatio' => [
                //Заметки на полях на случай ядерной зимы
                /*
                                'Logic' => [
                                    'JS' => [
                                        'id' => 'Logic',
                                        'name' => 'Logic',
                                        'type' => 'select',
                                        'values' => [
                                            'Equal' => 'равно',
                                            'Not' => 'не равно',
                                            'Great' => 'больше',
                                            'Less' => 'меньше',
                                            'EqGr' => 'больше либо равно',
                                            'EqLs' => 'меньше либо равно',
                                        ],
                                        'defaultText' => 'равно',
                                        'defaultValue' => 'Equal',
                                    ],
                                    'ATOM' => [
                                        'ID' => 'Logic',
                                        'FIELD_TYPE' => 'string',
                                        'FIELD_LENGTH' => 255,
                                        'MULTIPLE' => 'N',
                                        'VALIDATE' => 'list'
                                    ],
                                ],
                */
                'Value' => [
                    'JS' => [
                        'id' => 'Value',
                        'name' => 'value',
                        'type' => 'input'
                    ],
                    'ATOM' => [
                        'ID' => 'Value',
                        'FIELD_TYPE' => 'double',
                        'MULTIPLE' => 'N',
                        'VALIDATE' => ['>', 0]
                    ]
                ],
                'All' => [
                    'JS' => [
                        'id' => 'All',
                        'name' => 'aggregator',
                        'type' => 'select',
                        'values' => [
                            'AND' => 'всем условиям',
                            'OR' => 'любому из условий'
                        ],
                        'defaultText' => '...',
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
                ]
            ],
            'ADV:BasketFilterQuantityMore' => [
                'Value' => [
                    'JS' => [
                        'id' => 'Value',
                        'name' => 'value',
                        'type' => 'input'
                    ],
                    'ATOM' => [
                        'ID' => 'Value',
                        'FIELD_TYPE' => 'double',
                        'MULTIPLE' => 'N',
                        'VALIDATE' => ['>=', 0]
                    ]
                ],
                'All' => [
                    'JS' => [
                        'id' => 'All',
                        'name' => 'aggregator',
                        'type' => 'select',
                        'values' => [
                            'AND' => 'всем условиям',
                            'OR' => 'любому из условий'
                        ],
                        'defaultText' => '...',
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
                ]
            ],
            'ADV:BasketFilterQuantityRatio' => [
                'Value' => [
                    'JS' => [
                        'id' => 'Value',
                        'name' => 'value',
                        'type' => 'input'
                    ],
                    'ATOM' => [
                        'ID' => 'Value',
                        'FIELD_TYPE' => 'double',
                        'MULTIPLE' => 'N',
                        'VALIDATE' => ['>', 0]
                    ]
                ],
                'All' => [
                    'JS' => [
                        'id' => 'All',
                        'name' => 'aggregator',
                        'type' => 'select',
                        'values' => [
                            'AND' => 'всем условиям',
                            'OR' => 'любому из условий'
                        ],
                        'defaultText' => '...',
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
                ]
            ],
        ];

        return static::searchControlAtoms($atomList, $strControlID, $boolEx);
    }

    /**
     *
     * @param $arOneCondition
     * @param $arParams
     * @param $arControl
     * @param array|bool $arSubs
     *
     * @return array|bool|mixed|string
     */
    public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
    {
        $mxResult = '';

        if (\is_string($arControl)) {
            $arControl = static::GetControls($arControl);
        }

        $boolError = !\is_array($arControl) || !\is_array($arSubs);

        $arValues = [];
        if (!$boolError) {
            $arControl['ATOMS'] = static::GetAtomsEx($arControl['ID'], true);
            $arParams['COND_NUM'] = $arParams['FUNC_ID'];
            $arValues = static::CheckAtoms($arOneCondition, $arOneCondition, $arControl, true);
            $boolError = ($arValues === false);
        }

        if (!$boolError) {
            switch ($arControl['ID']) {
                case 'ADV:BasketFilterBasePriceRatio':
                    $mxResult = self::generateFilter($arValues['values'], $arParams, $arSubs, 'BASE_PRICE', 'ratio');
                    break;
                case 'ADV:BasketFilterQuantityMore':
                    $mxResult = self::generateFilter($arValues['values'], $arParams, $arSubs, 'QUANTITY', 'more');
                    break;
                case 'ADV:BasketFilterQuantityRatio':
                    $mxResult = self::generateFilter($arValues['values'], $arParams, $arSubs, 'QUANTITY', 'ratio');
                    break;
            }
        }
        dump($arControl['ID'], $arValues['values'], $mxResult);
        return (!$boolError ? $mxResult : false);
    }

    /**
     *
     *
     * @param $arValues
     * @param $arParams
     * @param $arSubs
     * @param string $field
     * @param string $returnParam
     *
     * @return array|string
     */
    private static function generateFilter($arValues, $arParams, $arSubs, string $field, string $returnParam)
    {
        $result = '';
        if (
            null !== $field && !empty($field)
            && isset($arValues['Value']) && \in_array($returnParam, ['more', 'ratio'], true)
        ) {
            if (!empty($arSubs) and \is_array($arSubs)) {
                $strLogic = ('AND' === $arValues['All'] ? PHP_EOL . ' && ' . PHP_EOL : PHP_EOL . ' || ' . PHP_EOL);
                $strFunc = PHP_EOL . 'function($row){' . PHP_EOL;
                $strFunc .= '   return (' . implode(') ' . $strLogic . ' (', $arSubs) . ');' . PHP_EOL;
                $strFunc .= '}' . PHP_EOL;
            } else {
                $strFunc = 'function($row){return true;}';
            }

            $result =
                PHP_EOL . self::class . '::filter(' . $arParams['ORDER'] . ', \'' . $field . '\', ' . $strFunc . ', '
                . var_export($arValues['Value'], true) . ', \'' . $returnParam . '\');';
        }

        return $result;
    }

    /**
     * Изменяет массив с корзиной с учетом фильтра и возвращает количество выполнений условия
     *
     * @param array $order
     * @param string $field
     * @param callable $filter
     * @param float $limitValue
     * @param string $returnParam
     *
     * @return int
     */
    public static function filter(
        array &$order,
        string $field,
        callable $filter,
        float $limitValue,
        string $returnParam
    ): int {
        $sum = 0.0;
        $clearBasket = [];
        if (!empty($order['BASKET_ITEMS']) && \is_array($order['BASKET_ITEMS'])) {
            reset($order['BASKET_ITEMS']);
            $basket = (\is_callable($filter) ? array_filter($order['BASKET_ITEMS'], $filter) : $order['BASKET_ITEMS']);
            if (!empty($basket)) {
                $clearBasket = array_filter($basket, '\CSaleBasketFilter::ClearBasket');
                $clearBasket = array_filter($clearBasket, [Actions::class, 'filterBasketForAction']);
                if (!empty($clearBasket)) {
                    foreach ($clearBasket as $row) {
                        if ($field === 'QUANTITY') {
                            $sum += (float)$row['QUANTITY'];
                        } else {
                            $sum += (float)$row[$field] * (float)$row['QUANTITY'];
                        }
                    }
                }
            }
            $order['BASKET_ITEMS'] = $clearBasket;
        }
        switch ($returnParam) {
            case 'more':
                // На сколько сумма больше введенного значения. Если оно меньше, то ни на сколько не больше.
                $result = (int)round((($sum > $limitValue) ? ($sum - $limitValue) : 0));
                break;
            case 'ratio':
                //Сколько раз выполнилось условие
                $result = intdiv($sum, (int)$limitValue);
                break;
            default:
                $result = 0;
        }
        return $result;
    }
}