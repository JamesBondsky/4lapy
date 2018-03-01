<?php
/**
 * Created by PhpStorm.
 * Date: 18.01.2018
 * Time: 21:08
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount;


use Bitrix\Sale\Discount;
use Bitrix\Sale\Discount\Actions;
use Bitrix\Sale\Order;
use Bitrix\Sale\OrderDiscountManager;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;


/**
 * Class Gift
 * @package FourPaws\SaleBundle\Discount
 * @todo переместить в соотвествующую папку и неймспейс
 */
class Gift extends \CSaleActionGiftCtrlGroup
{
    /**
     *
     *
     * @return array
     */
    public static function GetControlDescr(): array
    {
        $controlDescr = parent::GetControlDescr();
        $controlDescr['FORCED_SHOW_LIST'] = [
            'GifterElement',
            'CondBsktAmtBaseGroup'
        ];
        $controlDescr['SORT'] = 300;
//        $controlDescr['ENTITY'] = 'ELEMENT_PROPERTY';
//        $controlDescr['FIELD'] = 'PROPERTY_6_VALUE';
//        $controlDescr['FIELD_TABLE'] = '3:6';
//        $controlDescr['MODULE_ENTITY'] = 'catalog';

        return $controlDescr;
    }

    /**
     *
     *
     * @return array|string
     */
    public static function GetControlID(): string
    {
        return 'ADV:Gift';
    }

    /**
     *
     *
     * @param $arParams
     *
     * @return array
     */
    public static function GetControlShow($arParams): array
    {
        $description = parent::GetControlShow($arParams);
//        $arAtoms = static::GetAtomsEx();
//        dump($description, $arAtoms);
        $description['label'] = 'Предоставить выбор подарка';
        $description['containsOneAction'] = false;
        $description['mess'] = [
            'ADD_CONTROL' => 'Добавить условие',
            'SELECT_CONTROL' => 'Выбрать условие'
        ];
//        $description['defaultText'] = 'Список подарков';
//        $description['control'] = [
//            'Предоствить',
//            $arAtoms['count'],
//            'подарков из списка',
//            $arAtoms['list'],
//            $arAtoms['All'],
//            $arAtoms['True']
//        ];
//        $description['group'] = 'Y' === static::IsGroup();
//        dump($description);
        return $description;
    }

    /**
     *
     *
     * @param $arOneCondition
     * @param $arParams
     * @param $arControl
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
                    $result .= '$applyCount = ' . $elem;
                }
            }
            $subs = \json_encode($subs);
            $result .= static::class . '::applyGift(' . $arParams['ORDER'] . ', \'' . $subs . '\', $this, $applyCount);$arOrder = $originalOrder;';
        }

        return $result;
    }


    /**
     *
     *
     * @param $order
     * @param $params
     * @param Discount|null $callerObject
     * @param int $applyCount
     *
     */
    public static function applyGift(
        array $order,
        $params,
        /** @noinspection PhpUnusedParameterInspection */
        Discount $callerObject = null,
        int $applyCount
    ) {
        $applyBasket = null;
        $actionDescription = null;
        if (!empty($order['BASKET_ITEMS']) and \is_array($order['BASKET_ITEMS'])) {
            if (!empty($params) && ($params = json_decode($params, true)) && \is_array($params)) {
                foreach ($params as &$param) {
                    $param['count'] *= $applyCount;
                }
                unset($param);
                $params['discountType'] = 'GIFT';
            }
            $actionDescription = [
                'ACTION_TYPE' => OrderDiscountManager::DESCR_TYPE_SIMPLE,
                'ACTION_DESCRIPTION' => json_encode($params),
            ];
            Actions::increaseApplyCounter();
            Actions::setActionDescription(Actions::RESULT_ENTITY_BASKET, $actionDescription);

            /** @var array $applyBasket */
            $applyBasket = array_filter($order['BASKET_ITEMS'], [Actions::class, 'filterBasketForAction']);
        }

        if (!$applyBasket and !$actionDescription) {
            return;
        }

        foreach ($applyBasket as $basketCode => $basketRow) {
            $rowActionDescription = $actionDescription;
            $rowActionDescription['BASKET_CODE'] = $basketRow['ID'];
            Actions::setActionResult(Actions::RESULT_ENTITY_BASKET, $rowActionDescription);
//            /** @var BasketItem $ifItem */
//            if($basket && $ifItem = $basket->getItemByBasketCode($basketRow['ID'])) {
//                dump($ifItem->getField('NAME'), $ifItem->getQuantity());
//            }
        }

        // пытаемся получить корзину, чтобы добавить подарков
        /** @noinspection NullPointerExceptionInspection */
//        if (
//            \is_object($callerObject)
//            && $callerObject instanceof Discount
//            && $basket = $callerObject->getOrder()->getBasket() && false
//        ) {
//
//        }
    }

    /**
     *
     *
     * @param bool $strControlID
     * @param bool $boolEx
     *
     * @return array
     */
    public static function GetAtomsEx($strControlID = false, $boolEx = false): array
    {
//        $res = parent::GetAtomsEx($strControlID, $boolEx);
//        dump($res);
        $boolEx = (bool)$boolEx;
        $arAtomList = [
            'count' => [
                'JS' => [
                    'id' => 'count',
                    'name' => 'count',
                    'type' => 'input',
                    'defaultValue' => 1,
                ],
                'ATOM' => [
                    'ID' => 'count',
                    'FIELD_TYPE' => 'int',
                    'MULTIPLE' => 'N',
                    'VALIDATE' => ''
                ]
            ],
            'list' => [
                'JS' => [
                    'id' => 'list',
                    'name' => 'list',
                    'type' => 'input',
                    'defaultValue' => '',
                ],
                'ATOM' => [
                    'ID' => 'list',
                    'FIELD_TYPE' => 'string',
                    'MULTIPLE' => 'N',
                    'VALIDATE' => ''
                ]
            ],
            // todo Добавить настройку "сколько раз применять акцию"
        ];

        if (!$boolEx) {
            foreach ($arAtomList as &$arOneAtom) {
                $arOneAtom = $arOneAtom['JS'];
            }
            unset($arOneAtom);
        }

        return parent::GetAtomsEx($strControlID, $boolEx);
    }


    /**
     *
     *
     * @param Order|null $order
     * @param int|null $discountId
     *
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     *
     * @return array
     */
    public static function getPossibleGiftGroups(Order $order = null, int $discountId = null): array
    {
        if ($order instanceof Order) {
            /** @var \Bitrix\Sale\Discount $discount */
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
     *
     *
     * @param Order|null $order
     * @param int|null $discountId
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
            $ids = array_flip(array_flip(array_filter($ids)));
        }

        return $ids;
    }

    /**
     *
     *
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
                    ($data = json_decode($discount['ACTIONS_DESCR']['BASKET'], true))
                    //&& \is_iterable($data)
                    && \is_array($data)
                    && isset($data['discountType'])
                    && $data['discountType'] === 'GIFT'
                ) {
                    foreach ($data as $k => $elem) {
                        if (\is_int($k) && isset($elem['count']) && $elem['count'] > 0) {
                            $elem['discountId'] = (int)$discount['REAL_DISCOUNT_ID'];
                            $result[$discount['REAL_DISCOUNT_ID']][] = $elem;
                        }
                    }
                }
            }
        }
        return $result;
    }
}