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
use FourPaws\SaleBundle\Discount\Utils\ValidateAtoms;

/**
 * Class DetachedRowDiscount
 * @package FourPaws\SaleBundle\Discount\Action\Action
 */
class DetachedRowDiscount extends CSaleActionCtrlAction
{
    use ValidateAtoms;

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
            'blah-blah',
            $arAtoms['Value'],
            '% blah-blah',
            $arAtoms['Filtration_operator'],
            'blah-blah',
            $arAtoms['Count_operator'],
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
            'Filtration_operator' => [
                'JS' => [
                    'id' => 'Filtration_operator',
                    'name' => 'Filtration_operator',
                    'type' => 'select',
                    'values' => [
                        'separate' => 'обработать по отдельности', //+
                        'union' => 'объединение',
                        'intersect' => 'пересечение',
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
                        'min' => 'минимальное значение', //+
                        'max' => 'максимальное значение',
                        'sum' => 'сумма',
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
     *
     * @return array|bool|string
     */
    public static function Parse($arOneCondition)
    {
        return parent::Parse($arOneCondition);
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
        //dump($arOneCondition, $arParams, $arControl, $arSubs);
        $result = '';
        if (
            ////// todo Нормально сделай
            $arOneCondition['Filtration_operator'] === 'separate'
            && $arOneCondition['Count_operator'] === 'min'
            && \is_array($arSubs) && \count($arSubs) === 1
        ) {
            $result = '$originalOrder = ' . $arParams['ORDER'] . ';' . PHP_EOL;
            $result .= '$applyCount = ' . current($arSubs) . PHP_EOL;
            $result .= static::class . '::apply(' . $arParams['ORDER'] . ', ';
            $result .= var_export($arOneCondition['Value'],true) . ',$applyCount);'. PHP_EOL;
            $result .= $arParams['ORDER'] . ' = $originalOrder;' . PHP_EOL;
        }
        return $result;
    }

    /**
     *
     *
     * @param array $order
     * @param float $discountValue
     * @param int $applyCount
     *
     */
    public static function apply(array $order, float $discountValue, int $applyCount)
    {
        $applyBasket = null;
        $actionDescription = null;
        if (!empty($order['BASKET_ITEMS']) and \is_array($order['BASKET_ITEMS'])) {

            $actionDescription = [
                'ACTION_TYPE' => OrderDiscountManager::DESCR_TYPE_SIMPLE,
                'ACTION_DESCRIPTION' => json_encode(['discountType' => 'DETACH', 'params' => ['discount_value' => $discountValue, 'apply_count' => $applyCount]]),
            ];
            Actions::increaseApplyCounter();
            Actions::setActionDescription(Actions::RESULT_ENTITY_BASKET, $actionDescription);

            /** @var array $applyBasket */
            $applyBasket = array_filter($order['BASKET_ITEMS'], [Actions::class, 'filterBasketForAction']);
        }

        if (!$applyBasket || !$actionDescription) {
            return;
        }

        foreach ($applyBasket as $basketCode => $basketRow) {
            $rowActionDescription = $actionDescription;
            $rowActionDescription['BASKET_CODE'] = $basketRow['ID'];
            Actions::setActionResult(Actions::RESULT_ENTITY_BASKET, $rowActionDescription);
        }
    }
}