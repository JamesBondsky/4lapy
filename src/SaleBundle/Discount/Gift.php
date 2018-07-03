<?php

namespace FourPaws\SaleBundle\Discount;

use Bitrix\Main\ArgumentException;
use Bitrix\Sale\Discount;
use Bitrix\Sale\Discount\Actions;
use Bitrix\Sale\Order;
use Bitrix\Sale\OrderDiscountManager;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\SaleBundle\Discount\Utils\DiscountDisjunction;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class Gift
 * @package FourPaws\SaleBundle\Discount
 * @todo    переместить в соотвествующую папку и неймспейс
 */
class Gift extends \CSaleActionCtrlAction
{
    use DiscountDisjunction;
    /**
     * @return array
     */
    public static function GetControlDescr(): array
    {
        $controlDescr = parent::GetControlDescr();
        $controlDescr['FORCED_SHOW_LIST'] = [
            'ADV:BasketFilterBasePriceRatio',
            'ADV:BasketFilterQuantityRatio',
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
            'ADD_CONTROL' => 'Добавить условие',
            'SELECT_CONTROL' => 'Выбрать условие',
        ];
        $description['control'] = [
            'предоставить подарок',
            $arAtoms['Count_operator'],
            'если выполнены',
            $arAtoms['All'],
            'в количестве',
            $arAtoms['count'],
            'из следующих товаров: ',
            $arAtoms['list'],
        ];
        return $description;
    }

    /**
     * @param            $parameters
     * @param            $variables
     * @param            $arControl
     * @param array|bool $arSubs
     *
     * @return bool|string
     */
    public static function Generate($parameters, $variables, $arControl, $arSubs = false)
    {
        $result = '';
        if (
            \in_array($parameters['All'], ['AND', 'OR'], true)
            &&
            \in_array($parameters['Count_operator'], ['condition_count', 'once'], true)
            &&
            (int)$parameters['count'] >= 1
            &&
            \is_array($parameters['list']) && !empty($parameters['list'])
            &&
            \is_array($arSubs) && \count($arSubs) >= 1
        ) {
            $orderVar = $variables['ORDER'];
            $countOperator = $parameters['Count_operator'] === 'once' ? '(int)(bool)' : '';
            $countOperator .= $parameters['All'] === 'AND' ? 'min' : 'array_sum';
            $legacyJSONSettings = json_encode([
                [ //todo перейти на обычные параметры. Пока сделано так для обратной совместимости
                    'count' => (int)$parameters['count'],
                    'list' => $parameters['list'],
                ]
            ]);

            $result = PHP_EOL . '$counts = []; $filteredOrder = $originalOrder = ' . $orderVar . ';' . PHP_EOL;
            $result .= '$filteredOrder[\'BASKET_ITEMS\'] = [];' . PHP_EOL;
            foreach ($arSubs as $sub) {
                $result .= '$count = ' . $sub . PHP_EOL;
                $result .= 'if ($count) {' . PHP_EOL;
                if ($parameters['All'] === 'OR') {
                    // $arOrder = self::discountDisjunction()
                    $result .= $orderVar . '[\'BASKET_ITEMS\'] = ' . static::class
                        . '::discountDisjunction($filteredOrder[\'BASKET_ITEMS\'], '
                        . $orderVar . '[\'BASKET_ITEMS\']);' . PHP_EOL;
                }
                // !!! ПРОДПОЛАГАЕТСЯ ЧТО ТОВАРЫ В ГРУППАХ ПРЕДПОСЛОК РАЗНЫЕ !!!
                $result .= '$filteredOrder[\'BASKET_ITEMS\'] += ' . $orderVar . '[\'BASKET_ITEMS\'];' . PHP_EOL;
                $result .= '};' . PHP_EOL;
                $result .= '$counts[] = $count;' . PHP_EOL;
                $result .= $orderVar . ' = $originalOrder;' . PHP_EOL;
            }
            $result .= '$applyCount = ' . $countOperator . '($counts);' . PHP_EOL;
            $result .= static::class . '::applyGift($filteredOrder, \'' . $legacyJSONSettings . '\', '
                . '(isset($this) ? $this : null), $applyCount);' . PHP_EOL;
            $result .= '$arOrder = $originalOrder;' . PHP_EOL;
        }
        return $result;
    }

    /**
     * @param               $order
     * @param               $params
     * @param Discount|null $callerObject
     * @param int $applyCount
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ArgumentException
     * @throws ApplicationCreateException
     */
    public static function applyGift(
        array $order,
        $params,
        //todo заюзать и избавиться от Utils
        /** @noinspection PhpUnusedParameterInspection */
        Discount $callerObject = null,
        int $applyCount
    ) {
        $applyBasket = null;
        $actionDescription = true;
        if (!empty($order['BASKET_ITEMS']) && \is_array($order['BASKET_ITEMS']) && $applyCount) {
            $giftsSoldOut = true;
            if (!empty($params) && ($params = json_decode($params, true)) && \is_array($params)) {
                $giftsSoldOut = false;
                foreach ($params as $l => &$param) {
                    $param['count'] *= $applyCount;
                    // проверяем наличие подарков
                    $offerCollection = (new OfferQuery())->withFilterParameter('=ID', $param['list'])->exec();
                    $param['list'] = [];
                    /** @var Offer $offer */
                    foreach ($offerCollection as $k => $offer) {
                        if ($offer->isAvailable()) {
                            $param['list'][] = $k;
                        }
                    }
                    if (!$param['list']) {
                        $giftsSoldOut = true;
                    }
                }
                unset($param);
                $params['discountType'] = 'GIFT';
            }
            if ($giftsSoldOut) {
                $actionDescription = false;
            }
        }
        /**
         * @todo подобные фильтры должны быть в фильтре
         */
        /** @var array $applyBasket */
        $applyBasket = array_filter($order['BASKET_ITEMS'], [Actions::class, 'filterBasketForAction']);

        if (!$applyBasket || !$actionDescription) {
            return;
        }

        $premises = [];
        foreach ($applyBasket as $basketCode => $basketItem) {
            foreach ($basketItem['DISCOUNT_GROUPS'] as $groupId => $p) {
                if($groupId > $applyCount) {
                    break;
                }
                $premises[$basketItem['PRODUCT_ID']] += $p;
            }
        }
        $params['premises'] = $premises;

        $actionDescription = [
            'ACTION_TYPE' => OrderDiscountManager::DESCR_TYPE_SIMPLE,
            'ACTION_DESCRIPTION' => \json_encode($params),
        ];
        Actions::increaseApplyCounter();
        Actions::setActionDescription(Actions::RESULT_ENTITY_BASKET, $actionDescription);


        foreach ($applyBasket as $basketCode => $basketRow) {
            $rowActionDescription = $actionDescription;
            $rowActionDescription['BASKET_CODE'] = $basketCode;
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
                'ID' => $arParams['ID'],
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
                'JS' => [
                    'id' => 'Count_operator',
                    'name' => 'Count_operator',
                    'type' => 'select',
                    'values' => [
                        'condition_count' => 'столько, сколько выполняется условие',
                        'once' => 'один раз',
                    ],
                    'defaultText' => 'столько, сколько выполняется условие',
                    'defaultValue' => 'condition_count',
                    'first_option' => '...',
                ],
                'ATOM' => [
                    'ID' => 'Count_operator',
                    'FIELD_TYPE' => 'string',
                    'FIELD_LENGTH' => 255,
                    'MULTIPLE' => 'N',
                    'VALIDATE' => 'list',
                ],
            ],
            'count' => [
                'JS' => [
                    'id' => 'count',
                    'name' => 'count',
                    'type' => 'input',
                    'defaultText' => 1,
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
                    'type' => 'multiDialog',
                    'popup_url' => '/bitrix/admin/cat_product_search_dialog.php',
                    'popup_params' => [
                        'lang' => LANGUAGE_ID,
                        'caller' => 'discount_rules',
                        'allow_select_parent' => 'Y',
                    ],
                    'param_id' => 'n',
                    'show_value' => 'Y'
                ],
                'ATOM' => [
                    'ID' => 'list',
                    'FIELD_TYPE' => 'int',
                    'MULTIPLE' => 'Y',
                    'VALIDATE' => 'element'
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
