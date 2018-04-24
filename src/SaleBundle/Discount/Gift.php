<?php

namespace FourPaws\SaleBundle\Discount;

use Bitrix\Sale\Discount;
use Bitrix\Sale\Discount\Actions;
use Bitrix\Sale\Order;
use Bitrix\Sale\OrderDiscountManager;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;

/**
 * Class Gift
 * @package FourPaws\SaleBundle\Discount
 * @todo    переместить в соотвествующую папку и неймспейс
 */
class Gift extends \CSaleActionCtrlAction
{
    /**
     * @return array
     */
    public static function GetControlDescr(): array
    {
        $controlDescr = parent::GetControlDescr();
        $controlDescr['FORCED_SHOW_LIST'] = [
            'GifterElement',
            'ADV:BasketFilterBasePriceRatio',
        ];
        $controlDescr['SORT'] = 300;
        return $controlDescr;
    }

    /**
     * @return array|string
     */
    public static function GetControlID(): string
    {
        return 'ADV:Gift';
    }

    /**
     * @param $arParams
     *
     * @return array
     */
    public static function GetControlShow($arParams): array
    {
        $arAtoms = static::GetAtomsEx();
        $description = parent::GetControlShow($arParams);
        $description['label'] = 'Предоставить выбор подарка';
        $description['containsOneAction'] = false;
        $description['mess'] = [
            'ADD_CONTROL'    => 'Добавить условие',
            'SELECT_CONTROL' => 'Выбрать условие',
        ];
        $description['control'] = [
            'предоставить подарок',
            $arAtoms['Count_operator'],
        ];
        return $description;
    }

    /**
     * @param            $arOneCondition
     * @param            $arParams
     * @param            $arControl
     * @param array|bool $arSubs
     *
     * @return array|bool|string
     */
    public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
    {
        $result = '$applyCount = 0; $originalOrder = $arOrder;';


        if (null !== $arSubs && \is_array($arSubs) && !empty($arSubs) && implode('', $arSubs)) {
            $subs = [];
            foreach ($arSubs as $elem) {
                $giftGroupSettings = \json_decode($elem, true);
                if (\is_array($giftGroupSettings)) {
                    $subs[] = $giftGroupSettings;
                } else {
                    // функция для фильтрации корзины и определения сколько подарков выдавать.
                    // todo если функций несколько, то вычислить пересечение результатов фильтрации и минимальное количество выполений фильтра (но пока она одна)
                    $countOperator = '';
                    if ($arOneCondition['Count_operator'] === 'once') {
                        $countOperator = '(int)(bool)';
                    }
                    $result .= '$applyCount = ' . $countOperator . $elem;
                }
            }
            $subs = \json_encode($subs);
            $result .= static::class . '::applyGift(' . $arParams['ORDER'] . ', \'' . $subs . '\', (isset($this) ? $this : null), $applyCount);$arOrder = $originalOrder;';
        }

        return $result;
    }


    /**
     * @param               $order
     * @param               $params
     * @param Discount|null $callerObject
     * @param int           $applyCount
     *
     */
    public static function applyGift(
        array $order,
        $params,
        /** @noinspection PhpUnusedParameterInspection */
        Discount $callerObject = null,
        int $applyCount
    )
    {
        $applyBasket = null;
        $actionDescription = null;
        if (!empty($order['BASKET_ITEMS']) && \is_array($order['BASKET_ITEMS']) && $applyCount) {
            if (!empty($params) && ($params = json_decode($params, true)) && \is_array($params)) {
                foreach ($params as &$param) {
                    $param['count'] *= $applyCount;
                }
                unset($param);
                $params['discountType'] = 'GIFT';
            }
            $actionDescription = [
                'ACTION_TYPE'        => OrderDiscountManager::DESCR_TYPE_SIMPLE,
                'ACTION_DESCRIPTION' => \json_encode($params),
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

    /**
     * @param $arParams
     *
     * @return array|string
     */
    public static function GetConditionShow($arParams)
    {
        $result = false;
        if (isset($arParams['ID']) && $arParams['ID'] === static::GetControlID()) {
            $arControl = [
                'ID'    => $arParams['ID'],
                'ATOMS' => static::GetAtomsEx(false, true),
            ];
            $result = static::CheckAtoms($arParams['DATA'], $arParams, $arControl, true);
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function GetAtoms(): array
    {
        return static::GetAtomsEx();
    }

    /**
     * @param bool $strControlID
     * @param bool $boolEx
     *
     * @return array
     */
    public static function GetAtomsEx($strControlID = false, $boolEx = false): array
    {
        $boolEx = (true === $boolEx);

        $arAtomList = [
            'Count_operator' => [
                'JS'   => [
                    'id'           => 'Count_operator',
                    'name'         => 'Count_operator',
                    'type'         => 'select',
                    'values'       => [
                        'condition_count' => 'столько, сколько выполняется условие',
                        'once'            => 'один раз',
                    ],
                    'defaultText'  => 'столько, сколько выполняется условие',
                    'defaultValue' => 'condition_count',
                    'first_option' => '...',
                ],
                'ATOM' => [
                    'ID'           => 'Count_operator',
                    'FIELD_TYPE'   => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE'     => 'N',
                    'VALIDATE'     => 'list',
                ],
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
     * @param Order|null $order
     * @param int|null   $discountId
     *
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     *
     * @return array
     */
    public static function getPossibleGiftGroups(Order $order = null, int $discountId = null): array
    {
        if ($order instanceof Order) {
            /** @var Discount $discount */
            $discount = $order->getDiscount();
            $result = self::parseApplyResult($discount->getApplyResult(true));

            if ($discountId && isset($result[$discountId])) {
                $result = [$discountId => $result[$discountId]];
            } elseif ($discountId) {
                $result = [];
            }
        } else {
            throw new InvalidArgumentException('Не передан заказ');
        }

        return $result;
    }

    /**
     * @param Order|null $order
     * @param int|null   $discountId
     *
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     *
     * @return array
     */
    public static function getPossibleGifts(Order $order = null, int $discountId = null): array
    {
        $ids = [];
        if ($giftGroups = self::getPossibleGiftGroups($order, $discountId)) {
            foreach ($giftGroups as $group) {
                if (\is_array($group)) {
                    foreach ($group as $elem) {
                        if ($elem['list'] && \is_array($elem['list']) && ((!$discountId) xor $elem['discountId'] === $discountId)) {
                            $ids += $elem['list'];
                        }
                    }
                }
            }
            $ids = \array_flip(\array_flip(\array_filter($ids)));
        }

        return $ids;
    }

    /**
     * @param array|null $applyResult
     *
     * @return array
     */
    private static function parseApplyResult(array $applyResult = null): array
    {
        $result = [];
        if (\is_array($applyResult) && $applyResult && \is_array($applyResult['DISCOUNT_LIST'])) {
            foreach ($applyResult['DISCOUNT_LIST'] as $discount) {
                if (
                    ($data = \json_decode($discount['ACTIONS_DESCR']['BASKET'], true))
                    && \is_array($data)
                    && isset($data['discountType'])
                    && $data['discountType'] === 'GIFT'
                ) {
                    foreach ($data as $k => $elem) {
                        if (\is_int($k) && isset($elem['count']) && $elem['count'] > 0) {
                            $elem['discountId'] = (int)$discount['REAL_DISCOUNT_ID'];
                            $elem['name'] = $discount['NAME'];
                            $result[$discount['REAL_DISCOUNT_ID']][] = $elem;
                        }
                    }
                }
            }
        }

        return $result;
    }
}
