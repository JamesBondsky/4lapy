<?php

namespace FourPaws\PersonalBundle\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\OrderTable;
use FourPaws\SaleBundle\Enum\OrderStatus;

class Chance2Service extends ChanceService
{
    protected const HL_BLOCK_NAME = 'JanuaryUserChance';

    protected const CACHE_TAG = 'january:user.chance';

    public const PERIODS = [
        [
            'from' => '01.01.2020 00:00:00',
            'to' => '13.01.2020 23:59:59',
        ],
        [
            'from' => '14.01.2020 00:00:00',
            'to' => '20.01.2020 23:59:59',
        ],
        [
            'from' => '21.01.2020 00:00:00',
            'to' => '27.01.2020 23:59:59',
        ],
        [
            'from' => '28.01.2020 00:00:00',
            'to' => '03.02.2020 23:59:59',
        ],
    ];

    /**
     * @param $userId
     * @param $period
     * @return int
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getUserPeriodChance($userId, $period): int
    {
        $res = OrderTable::query()
            ->setFilter([
                'USER_ID' => $userId,
                '>=DATE_INSERT' => static::PERIODS[$period]['from'],
                '<=DATE_INSERT' => static::PERIODS[$period]['to'],
                'STATUS_ID' => [
                    OrderStatus::STATUS_DELIVERED,
                    OrderStatus::STATUS_FINISHED,
                ],
            ])
            ->setSelect(['ID', 'PRICE'])
            ->exec();

        $orders = [];

        while ($order = $res->fetch()) {
            $orders[] = Order::load($order['ID']);
        }

        $basketItems = $this->getAllBasketItems($orders);

        $totalChance = $this->getBasketItemsChanceWithFilter($basketItems, []);
        $totalChance += (2 * $this->getBasketItemsChanceWithFilter($basketItems, $this->getFeedProductIds()));
        $totalChance += (3 * $this->getBasketItemsChanceWithFilter($basketItems, $this->getClotherProductIds()));

        return $totalChance;
    }

    /**
     * @param $basketItems
     * @param $allowProductIds
     * @return int
     */
    protected function getBasketItemsChanceWithFilter($basketItems, $allowProductIds): int
    {
        $sum = 0;
        foreach ($basketItems as $basketItem) {
            if (!empty($allowProductIds) && in_array($basketItems['productId'], $allowProductIds, true)) {
                continue;
            }

            $sum += $basketItem['price'];
        }

        return (int)floor($sum / self::CHANCE_RATE);
    }

    /**
     * @param Order[] $orders
     * @return array
     * @throws ArgumentNullException
     */
    protected function getAllBasketItems($orders): array
    {
        $items = [];

        foreach ($orders as $order) {
            /** @var BasketItem $basketItem */
            foreach ($order->getBasket()->getBasketItems() as $basketItem) {
                $items[] = [
                    'productId' => $basketItem->getProductId(),
                    'price' => $basketItem->getPrice(),
                ];
            }
        }

        return $items;
    }

    /**
     * @return array
     */
    protected function getFeedProductIds(): array
    {
        return [1, 2, 3, 4, 5, 45, 32, 4234234, 5, 234344, 34342, 5654634, 4234, 34534, 7655, 54];
    }

    /**
     * @return array
     */
    protected function getClotherProductIds(): array
    {
        return [1, 2, 3, 4, 5, 45, 32, 4234234, 5, 234344, 34342, 5654634, 4234, 34534, 7655, 54];
    }
}
