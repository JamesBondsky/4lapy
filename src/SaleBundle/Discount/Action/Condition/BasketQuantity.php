<?php
/**
 * Created by PhpStorm.
 * Date: 16.02.2018
 * Time: 15:30
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Action\Condition;

/**
 * Class BasketQuantity
 * @package FourPaws\SaleBundle\Discount\Action\Condition
 */
class BasketQuantity extends \CCatalogCondCtrlIBlockProps
{
    /**
     *
     * @param $arOneCondition
     *
     * @return array|bool|string
     */
    public static function Parse($arOneCondition)
    {
        $arOneCondition['value'] = 'QUANTITY';
        return parent::Parse($arOneCondition);
    }

    /**
     *
     *
     * @param $arControls
     *
     * @return array
     */
    public static function getShowIn($arControls): array
    {
        return [\CSaleActionCtrlBasketGroup::GetControlID()];
    }

    /**
     *
     * @param bool|string $strControlID
     *
     * @return array|bool|mixed
     */
    public static function GetControls($strControlID = false)
    {
        $parentResult = parent::GetControls();
        $result = [];
        $iblock = 0;
        $separatorLabel = '';
        foreach ($parentResult as $k => $elem) {
            if ($elem['SEP_LABEL']) {
                $separatorLabel = $elem['SEP_LABEL'] . ' и количество товара';
            }
            if ($elem['FIELD_TYPE'] === 'double') {
                $k = str_replace('CondIBProp', 'BasketQuantity', $k);
                $elem['ID'] = $k;
                if ($iblock !== $elem['IBLOCK_ID']) {
                    $iblock = $elem['IBLOCK_ID'];
                    $elem['SEP'] = 'Y';
                    $elem['SEP_LABEL'] = $separatorLabel;
                }
                $elem['FIELD_TYPE'] = 'text';
                $elem['MULTIPLE'] = 'N';
                $result[$k] = $elem;
            }
        }
        return static::searchControl($result, $strControlID);
    }

    /**
     *
     * @param $arParams
     *
     * @return array
     */
    public static function GetControlShow($arParams): array
    {
        $res = parent::GetControlShow($arParams);
        foreach ($res as $i => $group) {
            if (isset($group['children']) && \is_array($group['children'])) {
                foreach ($group['children'] as $j => $elem) {
                    $elem['control'][2] = 'Полное количество товара в корзине ';
                    $res[$i]['children'][$j]['control'] = array_reverse($elem['control']);
                }
            }
        }
        return $res;
    }

    /**
     *
     * @param $arOneCondition
     * @param $arParams
     * @param $arControl
     * @param bool $arSubs
     *
     * @return bool|mixed|string
     */
    public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
    {
        $arControl = static::GetControls($arControl);
        $arLogic = static::SearchLogic($arOneCondition['logic'], $arControl['LOGIC']);
        $operator = $arLogic['OP'][$arControl['MULTIPLE']];
        $operator = str_replace(['#FIELD#', '#VALUE#'], '', $operator);
        $strProp = '$row[\'CATALOG\'][\'' . $arControl['FIELD'] . '\']';
        $res = PHP_EOL . 'isset(' . $strProp . ') &&' . PHP_EOL
            . '(' . PHP_EOL
            . self::class . '::getBasketQuantity(' . PHP_EOL
            . '$row[\'PRODUCT_ID\'],' . PHP_EOL
            . '(isset($this) ? $this->orderData[\'BASKET_ITEMS\'] : null)' . PHP_EOL . ')'
            . $operator . PHP_EOL
            . '(is_array(' . $strProp . ')' . PHP_EOL
            . '? (int)current(' . $strProp . ')' . PHP_EOL
            . ': (int)' . $strProp . ')' . PHP_EOL
            . PHP_EOL . ')' . PHP_EOL;
        return $res;
    }

    /**
     *
     *
     * @param $productId
     * @param array|null $basket
     *
     * @return int
     */
    public static function getBasketQuantity($productId, array $basket = null): int
    {
        $quantity = 0;
        if (\is_array($basket)) {
            foreach ($basket as $k => $row) {
                if ((int)$row['PRODUCT_ID'] === (int)$productId) {
                    $quantity += (int)$row['QUANTITY'];
                }
            }
        }
        return $quantity;
    }
}