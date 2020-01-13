<?php

namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\OrderTable;
use CIBlockElement;
use CIBlockSection;
use Exception;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\SaleBundle\Enum\OrderStatus;
use WebArch\BitrixCache\BitrixCache;

class Chance2Service extends ChanceService
{
    protected const HL_BLOCK_NAME = 'JanuaryUserChance';

    protected const CACHE_TAG = 'january:user.chance';

    protected const FEED_DEFAULT_CODES = [
        'royal-canin',
        'hills',
        'khills',
        'monge',
//        'fresh-step', под вопросом
//        'ever-clean',
//        'trainer',
//        'padovan',
//        'adresnik',
    ];

    protected const FEED_BRAND_CODES = [
        'korm-koshki' => [
            'grandin',
            'mealfeel',
            'murmix',
            'unocat',
            'wellkiss',
            'yummy',
            'avva',
        ],
        'lakomstva-vitaminy-dobavki' => [
            'chatell',
            'chewell',
            'murmix',
            'avva',
            'nagrada',
        ],
        'korm-sobaki' => [
            'grandin',
            'mealfeel',
            'murmix',
            'unocat',
            'wellkiss',
            'yummy',
            'avva',
        ],
        'lakomstva-i-vitaminy-sobaki' => [
            'chatell',
            'chewell',
            'murmix',
            'avva',
            'nagrada',
        ],
    ];

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
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function getUserPeriodChance($userId, $period): int
    {
        $res = OrderTable::query()
            ->setFilter([
                'USER_ID' => $userId,
                'PAYED' => 'Y',
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
        $totalChance += $this->getFeedBasketItemsChance($basketItems);
        $totalChance += (2 * $this->getBasketItemsChanceWithFilter($basketItems, $this->getClotherProductIds()));

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
            if (!empty($allowProductIds) && !in_array($basketItem['productId'], $allowProductIds, true)) {
                continue;
            }

            $sum += $basketItem['price'];
        }

        return (int)floor($sum / self::CHANCE_RATE);
    }

    /**
     * @param $basketItems
     * @return int
     * @throws SystemException
     */
    public function getFeedBasketItemsChance($basketItems): int
    {
        $sum = 0;

        foreach ($basketItems as $basketItem) {
            if ($this->checkFeedProduct($basketItem['productId'])) {
                $sum += $basketItem['price'];
            }
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
                    'price' => $basketItem->getPrice() * $basketItem->getQuantity(),
                ];
            }
        }

        return $items;
    }

    /**
     * @param $productId
     * @return bool
     * @throws SystemException
     */
    public function checkFeedProduct($productId): bool
    {
        $offer = OfferQuery::getById($productId);

        if ($offer === null) {
            return false;
        }

        $brandCode = $offer->getProduct()->getBrand()->getCode();

        if (in_array($brandCode, static::FEED_DEFAULT_CODES, true)) {
            return true;
        }

        $brandSectionMap = $this->getSectionBrandMap();
        foreach ($offer->getProduct()->getSectionsIdList() as $section) {
            if (($brandList = $brandSectionMap[$section]) && in_array($brandCode, $brandList, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getClotherProductIds(): array
    {
        $doGetAllVariants = static function () {
            if (!$arSection = CIBlockSection::GetList(false, ['CODE' => 'odezhda-i-obuv'])->Fetch()) {
                return [];
            }

            $sectionIds = [
                $arSection['ID']
            ];

            $rsSection = CIBlockSection::GetList(false, ['SECTION_ID' => $arSection['ID']]);

            while ($arSection = $rsSection->Fetch()) {
                $sectionIds[] = $arSection['ID'];
            }

            $productIds = [];

            $rsProduct = CIBlockElement::GetList(false, [
                'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
                'SECTION_ID' => $sectionIds,
            ], false, false, ['ID', 'IBLOCK_ID']);

            while ($arProduct = $rsProduct->Fetch()) {
                $productIds[] = $arProduct['ID'];
            }

            $rsOffer = CIBlockElement::GetList(false, [
                'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS),
                'PROPERTY_CML2_LINK' => $productIds,
            ], false, false, ['ID', 'IBLOCK_ID', 'PROPERTY_CML2_LINK']);

            $offerIds = [];
            while ($arOffer = $rsOffer->Fetch()) {
                $offerIds[] = $arOffer['ID'];
            }

            return $offerIds;
        };

        try {
            return (new BitrixCache())
                ->withId(__METHOD__ . 'chance.clother.products')
                ->withTime(36000)
                ->withTag('chance.clother.products')
                ->resultOf($doGetAllVariants);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @return array
     */
    public function getSectionBrandMap(): array
    {
        $doGetAllVariants = static function () {
            $rsSection = CIBlockSection::GetList(false, [
                'CODE' => array_keys(self::FEED_BRAND_CODES),
            ], false, ['ID', 'CODE']);

            $sections = [];
            while ($arSection = $rsSection->Fetch()) {
                $sections[$arSection['CODE']] = [$arSection['ID']];

                $rsChildSection = CIBlockSection::GetList(false, [
                    'SECTION_ID' => $arSection['ID'],
                    'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
                ], false, ['ID', 'CODE']);

                while ($arChildSection = $rsChildSection->Fetch()) {
                    $sections[$arSection['CODE']][] = $arChildSection['ID'];
                }
            }

            $result = [];
            foreach (self::FEED_BRAND_CODES as $sectionCode => $brands) {
                foreach ($sections[$sectionCode] as $sectionId) {
                    $result[$sectionId] = $brands;
                }
            }

            return $result;
        };

        try {
            return (new BitrixCache())
                ->withId(__METHOD__ . 'chance.feed.products')
                ->withTime(36000)
                ->withTag('chance.feed.products')
                ->resultOf($doGetAllVariants);
        } catch (Exception $e) {
            return [];
        }
    }
}
