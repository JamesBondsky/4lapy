<?php
/**
 * Created by PhpStorm.
 * Date: 14.03.2018
 * Time: 23:00
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils\Detach;

use Bitrix\Sale\BasketItem;
use FourPaws\SaleBundle\Discount\Utils\AdderInterface;
use FourPaws\SaleBundle\Discount\Utils\BaseDiscountPostHandler;
use FourPaws\SaleBundle\Exception\RuntimeException;

/**
 * Class Adder
 * @package FourPaws\SaleBundle\Discount\Utils\Detach
 */
class Adder extends BaseDiscountPostHandler implements AdderInterface
{
    /**
     *
     *
     * @throws \FourPaws\SaleBundle\Exception\RuntimeException
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Exception
     */
    public function processOrder(): void
    {
        /**
         * 1. количества и свойства
         * 2. PRICE и DISCOUNT_PRICE
         */
        //todo Вероятно стоит сначала целиком разобрать резалт, а потом действовать
        if (!$discount = $this->order->getDiscount()) {
            return;
        }
        $applyResult = $discount->getApplyResult(true);
        if (is_iterable($applyResult['RESULT']['BASKET'])) {
            foreach ($applyResult['RESULT']['BASKET'] as $basketId => $discounts) {
                if (is_iterable($discounts)) {
                    foreach ($discounts as $discount) {
                        if (
                            ($params = json_decode($discount['DESCR'], true))
                            && \is_array($params)
                            && isset($params['discountType'])
                            && $params['discountType'] === 'DETACH'
                        ) {

                            $applyCount = (int)$params['params']['apply_count'];
                            $percent = (int)$params['params']['discount_value'];
                            /** @var BasketItem $basketItem */
                            if ($applyCount && null !== $basketItem = $this->order->getBasket()->getItemById($basketId)) {
                                if ((int)$basketItem->getQuantity() > $applyCount) {
                                    //Детачим
                                    $basketItem->setField('QUANTITY', $basketItem->getQuantity() - $applyCount);
                                    $fields = [
                                        'PROPS' => [
                                            [
                                                'NAME' => 'Отделено от элемента корзины',
                                                'CODE' => 'DETACH_FROM',
                                                'VALUE' => $basketId,
                                                'SORT' => 100,
                                            ],
                                        ]
                                    ];
                                    /**
                                     * вызывает то же событие, но у нас обработчик заблокирован пока выполняется
                                     */
                                    $newBasketItem = $this->basketService->addOfferToBasket(
                                        $basketItem->getProductId(),
                                        $applyCount,
                                        $fields,
                                        false,
                                        $this->order->getBasket(),
                                        false
                                    );
                                    /** @noinspection PhpInternalEntityUsedInspection */
                                    $newBasketItem->setFieldsNoDemand([
                                        'PRICE' => $price = (100 - $percent) * $basketItem->getPrice() / 100,
                                        'DISCOUNT_PRICE' => $basketItem->getBasePrice() - $price,
                                    ]);
                                } elseif ((int)$basketItem->getQuantity() === (int)$params['params']['apply_count']) {
                                    //Просто проставляем поля
                                    /** @noinspection PhpInternalEntityUsedInspection */
                                    $basketItem->setFieldsNoDemand([
                                        'PRICE' => $price = (100 - $percent) * $basketItem->getPrice() / 100,
                                        'DISCOUNT_PRICE' => $basketItem->getBasePrice() - $price,
                                    ]);
                                } else {
                                    // todo ситуация может возникать когда на одну позицию действует несколько детач акций, пока опустим этот момент
                                    throw new RuntimeException('TODO');
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
