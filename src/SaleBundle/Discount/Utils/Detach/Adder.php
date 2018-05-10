<?php

/**
 * Created by PhpStorm.
 * Date: 14.03.2018
 * Time: 23:00
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils\Detach;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\BasketItem;
use Exception;
use FourPaws\SaleBundle\Discount\Utils\AdderInterface;
use FourPaws\SaleBundle\Discount\Utils\BaseDiscountPostHandler;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\RuntimeException;

/**
 * Class Adder
 * @package FourPaws\SaleBundle\Discount\Utils\Detach
 */
class Adder extends BaseDiscountPostHandler implements AdderInterface
{
    public static $skippedDiscountsFakeIds = [];


    /**
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws BitrixProxyException
     * @throws ObjectNotFoundException
     * @throws LoaderException
     * @throws ArgumentOutOfRangeException
     * @throws Exception
     */
    public function processOrder(): void
    {
        /**
         * 1. количества и свойства
         * 2. PRICE и DISCOUNT_PRICE
         */
        //todo Вероятно стоит сначала целиком разобрать резалт, а потом действовать
        if (!$discountBase = $this->order->getDiscount()) {
            return;
        }
        $applyResult = $discountBase->getApplyResult(true);
        $lowDiscounts = $this->getLowDiscounts($applyResult['RESULT']['BASKET']);

        if (is_iterable($applyResult['RESULT']['BASKET'])) {
            foreach ($applyResult['RESULT']['BASKET'] as $basketCode => $discounts) {
                if (is_iterable($discounts)) {
                    foreach ($discounts as $discount) {
                        if (
                            ($params = json_decode($discount['DESCR'], true))
                            && \is_array($params)
                            && isset($params['discountType'])
                            && $params['discountType'] === 'DETACH'
                        ) {
                            if (\in_array((int)$discount['DISCOUNT_ID'], $lowDiscounts, true)) {
                                continue;
                            }

                            $applyCount = (int)$params['params']['apply_count'];
                            $percent = (int)$params['params']['discount_value'];

                            /** @var BasketItem $basketItem */
                            if (
                                $applyCount
                                && null !== $basketItem = $this->order->getBasket()->getItemByBasketCode($basketCode)
                            ) {
                                if ((int)$basketItem->getQuantity() > $applyCount) {
                                    //Детачим
                                    $basketItem->setField('QUANTITY', $basketItem->getQuantity() - $applyCount);
                                    $fields = [
                                        'PRICE' => $price = (100 - $percent) * $basketItem->getPrice() / 100,
                                        'DISCOUNT_PRICE' => $basketItem->getBasePrice() - $price,
                                        'CUSTOM_PRICE' => 'Y',
                                        'PROPS' => [
                                            [
                                                'NAME' => 'Отделено от элемента корзины',
                                                'CODE' => 'DETACH_FROM',
                                                'VALUE' => $basketCode,
                                                'SORT' => 100,
                                            ],
                                            [
                                                'NAME' => 'Название скидки',
                                                'CODE' => 'SHARE_NAME',
                                                'VALUE' => $applyResult['DISCOUNT_LIST'][$discount['DISCOUNT_ID']]['NAME'],
                                                'SORT' => 200,
                                            ],
                                        ]
                                    ];
                                    /**
                                     * вызывает то же событие, но у нас обработчик заблокирован пока выполняется
                                     */
                                    $this->basketService->addOfferToBasket(
                                        $basketItem->getProductId(),
                                        $applyCount,
                                        $fields,
                                        false
                                    );
                                } elseif ((int)$basketItem->getQuantity() === (int)$params['params']['apply_count']) {
                                    //Просто проставляем поля
                                    /** @noinspection PhpInternalEntityUsedInspection */
                                    $basketItem->setFieldsNoDemand([
                                        'PRICE' => $price = (100 - $percent) * $basketItem->getPrice() / 100,
                                        'DISCOUNT_PRICE' => $basketItem->getBasePrice() - $price,
                                        'CUSTOM_PRICE' => 'Y'
                                    ]);
                                } else {
                                    throw new RuntimeException('Impossible exception');
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Возвращает массив фэйков айдишников скидок, которые не нужно применять,
     * т.к. они пересакаются с другими и менее выгодны
     *
     * @param array $basketApplyResult
     *
     * @return array
     */
    protected function getLowDiscounts(array $basketApplyResult): array
    {
        $result = [];
        // Найдем конфликтующие скидки
        $conflicts = [];
        /**
         * @var $basketId
         * @var array $discounts
         */
        foreach ($basketApplyResult as $basketId => $discounts) {
            if (\is_iterable($discounts)) {
                $currentStepIds = [];
                foreach ($discounts as $discount) {
                    if (
                        ($params = \json_decode($discount['DESCR'], true))
                        && \is_array($params)
                        && isset($params['discountType'])
                        && $params['discountType'] === 'DETACH'
                    ) {
                        $currentStepIds[$discount['DISCOUNT_ID']] = true;
                    }
                }
                if (\count($currentStepIds) > 1) {
                    foreach ($currentStepIds as $id => $v) {
                        $t = $currentStepIds;
                        unset($t[$id]);
                        if (isset($conflicts[$id])) {
                            /** @noinspection SlowArrayOperationsInLoopInspection */
                            $conflicts[$id] = \array_merge($conflicts[$id], \array_keys($t));
                        } else {
                            $conflicts[$id] = \array_keys($t);
                        }
                    }
                }
            }
        }

        if (!empty($conflicts)) {
            $conflicts = \array_map(
                function ($e) {
                    return \array_flip(\array_flip($e));
                },
                $conflicts
            );
        }

        // теперь узнаем какую скидку в рублях дает каждая из конфликтных акций
        $promoDiscounts = [];
        foreach ($conflicts as $currentId => $refuseIds) {
            foreach ($basketApplyResult as $basketId => $discounts) {
                if (\is_iterable($discounts)) {
                    foreach ($discounts as $discount) {
                        if (
                            ($params = json_decode($discount['DESCR'], true))
                            && \is_array($params)
                            && isset($params['discountType'])
                            && $params['discountType'] === 'DETACH'
                        ) {
                            if (\in_array((int)$discount['DISCOUNT_ID'], $refuseIds, true)) {
                                continue;
                            }
                            $applyCount = (int)$params['params']['apply_count'];
                            $percent = (int)$params['params']['discount_value'];
                            if ($applyCount && null !== $basketItem = $this->order->getBasket()->getItemById($basketId)) {
                                $promoDiscounts[$currentId] += $basketItem->getPrice() / 100 * $percent * $applyCount;
                            }
                        }
                    }
                }
            }
        }

        /**
         * @todo вероятны баги при множественном пересечении. Сейчас мы не применяем акции, которые мешают максимальной,
         * но могут быть же и другие пересекающиеся акции
         */

        if (!empty($promoDiscounts)) {
            $bestDiscount = \array_search(\max($promoDiscounts), $promoDiscounts, true);
            /** @var $bestDiscount int */
            $result = $conflicts[$bestDiscount];
        }
        self::setSkippedDiscountsFakeIds($result);
        return $result;
    }

    /**
     * @return array
     */
    public static function getSkippedDiscountsFakeIds(): array
    {
        return self::$skippedDiscountsFakeIds;
    }

    /**
     * @param array $skippedDiscountsFakeIds
     */
    public static function setSkippedDiscountsFakeIds(array $skippedDiscountsFakeIds): void
    {
        self::$skippedDiscountsFakeIds = array_flip(array_flip(array_merge(
            $skippedDiscountsFakeIds, self::$skippedDiscountsFakeIds
        )));
    }
}
