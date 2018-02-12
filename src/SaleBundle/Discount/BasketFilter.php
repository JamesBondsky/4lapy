<?php
/**
 * Created by PhpStorm.
 * Date: 25.01.2018
 * Time: 16:50
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount;

use Bitrix\Sale\Discount\Actions;


/**
 * Class BasketFilter
 * @package FourPaws\SaleBundle\Discount
 */
class BasketFilter extends \CSaleCondCtrlBasketGroup
{
    /**
     *
     * @param bool|string $strControlID
     *
     * @return array|bool|mixed
     */
    public static function GetControls($strControlID = false)
    {
        $controls = parent::GetControls($strControlID);
        foreach ($controls as $k => $elem) {
            if (\is_array($elem['SHOW_IN'])) {
                $controls[$k]['SHOW_IN'] = array_diff($elem['SHOW_IN'], ['CondGroup']);
            }
        }
        return $controls;
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
                case 'CondBsktAmtBaseGroup':
                    $mxResult = self::generateSumFilter($arValues['values'], $arParams, $arSubs, 'BASE_PRICE');
                    break;
            }
        }

//        dump($arOneCondition, $arParams, $arControl, $arSubs, $mxResult);
        return (!$boolError ? $mxResult : false);
    }

    /**
     *
     *
     * @param $arValues
     * @param $arParams
     * @param $arSubs
     * @param string $field
     *
     * @return array|mixed|string
     */
    private static function generateSumFilter($arValues, $arParams, $arSubs, string $field)
    {
        $result = '';
        if (!(null === $field || empty($field) || !isset($arValues['Value']) || $arValues['Value'] <= 0)) {
            if (!empty($arSubs) and \is_array($arSubs)) {
                $strLogic = ('AND' === $arValues['All'] ? '&&' : '||');
                $strFunc = 'function($row){';
                $strFunc .= 'return (' . implode(') ' . $strLogic . ' (', $arSubs) . ');';
                $strFunc .= '}';
            } else {
                $strFunc = 'function($row){return true;}';
            }

            $result =
                self::class . '::sumFilter(' . $arParams['ORDER'] . ', \'' . $field . '\', ' . $strFunc . ', '
                . var_export($arValues['Value'], true) . ');';
        }

//        dump($arValues, $arParams, $arSubs, $strFunc, $result);

        return $result;
    }

    /**
     * Изменяет массив с корзиной с учетом фильтра и возвращает количество выполнений условия
     *
     * @param array $order
     * @param string $field
     * @param callable $filter
     * @param float $limitValue
     *
     * @return float
     */
    public static function sumFilter(array &$order, string $field, callable $filter, float $limitValue): float
    {
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
        return intdiv($sum, $limitValue);
    }
}