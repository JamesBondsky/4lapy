<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Handler;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Collection\PriceForAmountCollection;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\PriceForAmount;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Service\PiggyBankService;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Service\UserCitySelectInterface;

abstract class DeliveryHandlerBase extends Base implements DeliveryHandlerInterface
{
    /**
     * @var bool
     */
    protected static $isCalculatePriceImmediately = true;

    /**
     * @var bool
     */
    protected static $canHasProfiles = true;

    /**
     * @var LocationService $locationService
     */
    protected $locationService;

    /**
     * @var StoreService $storeService
     */
    protected $storeService;

    /**
     * @var UserCitySelectInterface
     */
    protected $userService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var PiggyBankService
     */
    protected $piggyBankService;

    /**
     * DeliveryHandlerBase constructor.
     *
     * @param $initParams
     *
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\SystemException
     * @throws ApplicationCreateException
     */
    public function __construct($initParams)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $serviceContainer = Application::getInstance()->getContainer();
        $this->locationService = $serviceContainer->get('location.service');
        $this->storeService = $serviceContainer->get('store.service');
        $this->deliveryService = $serviceContainer->get('delivery.service');
        $this->piggyBankService = $serviceContainer->get('piggy_bank.service');
        $this->userService = $serviceContainer->get(UserCitySelectInterface::class);
        parent::__construct($initParams);
    }

    /**
     * @param Shipment $shipment
     * @return bool
     * @throws ObjectNotFoundException
     */
    public function isCompatible(Shipment $shipment)
    {
        if (!parent::isCompatible($shipment)) {
            return false;
        }

        return (bool)$this->deliveryService->getDeliveryLocation($shipment);
    }

    /**
     * @param Basket $basket
     *
     * @return null|ArrayCollection
     */
    public static function getOffers(Basket $basket): ?ArrayCollection
    {
        if ($basket->isEmpty()) {
            return null;
        }

        $offerIds = [];
        /** @var BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            $offerId = $basketItem->getProductId();
            $quantity = $basketItem->getQuantity();
            if (!$offerId || !$quantity) {
                continue;
            }
            $offerIds[$offerId] = $offerId;
        }

        if (empty($offerIds)) {
            return null;
        }

        $offers = new ArrayCollection();

        foreach ($offerIds as $offerId) {
            if ($offer = OfferQuery::getById($offerId)) {
                $offers[$offerId] = $offer;
            }
        }

        return $offers;
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param Basket      $basket
     * @param ArrayCollection $offers
     * @param StoreCollection $storesAvailable
     *
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @return StockResultCollection
     */
    public static function getStocks(
        Basket $basket,
        ArrayCollection $offers,
        StoreCollection $storesAvailable
    ): StockResultCollection
    {
        $stockResultCollection = new StockResultCollection();

        $offerData = static::getBasketPrices($basket);

        foreach ($offerData as $offerId => $priceForAmountCollection) {
            /**
             * Если такое произошло, значит оффер был подарком и был удален из корзины
             */
            if (null === $offers[$offerId]) {
                continue;
            }

            static::getStocksForItem(
                $offers[$offerId],
                $priceForAmountCollection,
                $storesAvailable,
                $stockResultCollection
            );
        }

        return $stockResultCollection;
    }

    /**
     * Метод для Достависты
     * Проверяет наличие остатков каждого оффера в каждом магазине Москвы
     * Если все товары есть в наличие, то доставка будет активна
     * @param Basket $basket
     * @param ArrayCollection $offers
     * @param StoreCollection $stores
     * @return StockResultCollection
     */
    public function getStocksForAllAvailableOffers(
        Basket $basket,
        ArrayCollection $offers,
        StoreCollection $stores
    ): StockResultCollection {
        $stockResultCollection = new StockResultCollection();
        $offerData = static::getBasketPrices($basket);
        /** @var array $marksIds */
        $marksIds = $this->piggyBankService->getMarksIds();

        foreach ($offerData as $key => $offer) {
            if (in_array($key, $marksIds)) {
                unset($offerData[$key]);
            }
        }

        if (null === $stockResultCollection) {
            $stockResultCollection = new StockResultCollection();
        }

        /** @var Store $store */
        foreach ($stores->getIterator() as $store) {
            if (!$store->isExpressStore()) {
                continue;
            }
            $allOfferAvaliable = true;
            $stockResultCollectionTmp = new StockResultCollection();
            foreach ($offerData as $offerId => $priceForAmountCollection) {
                /** @var Offer $offer */
                $offer = $offers[$offerId];
                /**
                 * Если такое произошло, значит оффер был подарком и был удален из корзины
                 */
                if (null === $offer) {
                    continue;
                }

                $stockResult = new StockResult();
                $stockResult->setPriceForAmount($priceForAmountCollection)
                    ->setOffer($offer)
                    ->setStore($store);

                $amount = $stockResult->getAmount();
                $stocks = $offer->getAllStocks();
                if ($availableAmount = $stocks->filterByStore($store)->getTotalAmount()) {
                    if ($availableAmount < $amount) {
                        $allOfferAvaliable = false;
                    }
                } else {
                    $allOfferAvaliable = false;
                }
                if (!$allOfferAvaliable) {
                    break;
                }
                $stockResultCollectionTmp->add($stockResult);
            }
            if ($allOfferAvaliable) {
                foreach ($stockResultCollectionTmp as $stockResult) {
                    $stockResultCollection->add($stockResult);
                }
            }
        }

        return $stockResultCollection;
    }

    /**
     * @param Basket          $basket
     *
     * @return PriceForAmountCollection[]
     */
    public static function getBasketPrices(Basket $basket): array
    {
        /** @var BasketService $basketService */
        $basketService = Application::getInstance()->getContainer()->get(BasketService::class);
        /** @var PriceForAmountCollection[] $result */
        $result = [];
        /** @var BasketItem $item */
        foreach ($basket as $item) {
            if (null === $result[$item->getProductId()]) {
                $result[$item->getProductId()] = new PriceForAmountCollection();
            }
            $result[$item->getProductId()]->add((new PriceForAmount())
                ->setPrice($item->getPrice())
                ->setAmount($item->getQuantity())
                ->setBasketCode($item->getBasketCode())
                ->setGift($basketService->isGiftProduct($item))
            );
        }

        return $result;
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param Offer                      $offer
     * @param PriceForAmountCollection   $priceForAmountCollection
     * @param StoreCollection            $stores
     * @param StockResultCollection|null $stockResultCollection
     *
     * @return StockResultCollection
     * @throws ApplicationCreateException
     * @throws NotFoundException
     */
    public static function getStocksForItem(
        Offer $offer,
        PriceForAmountCollection $priceForAmountCollection,
        StoreCollection $stores,
        StockResultCollection $stockResultCollection = null
    ): StockResultCollection
    {
        if (null === $stockResultCollection) {
            $stockResultCollection = new StockResultCollection();
        }

        /** @var Store $store */
        foreach ($stores->getIterator() as $store) {
            $stockResult = new StockResult();
            $stockResult->setPriceForAmount($priceForAmountCollection)
                ->setOffer($offer)
                ->setStore($store);
            $delayedStockResult = null;
            $unavailableStockResult = null;

            $amount = $stockResult->getAmount();
            $stocks = $offer->getAllStocks();
            if ($availableAmount = $stocks->filterByStore($store)->getTotalAmount()) {
                if ($availableAmount < $amount) {
                    $delayedStockResult = $stockResult->splitByAmount($availableAmount);
                    $amount -= $availableAmount;
                } else {
                    $amount = 0;
                }
                $stockResultCollection->add($stockResult);
            } else {
                $delayedStockResult = $stockResult;
            }

            /**
             * Товар в наличии не полностью. Часть будет отложена
             */
            if ($delayedStockResult && $offer->isAvailableForDelay($store)) {
                $storesDelay = $offer->getAllStocks()->getStores()->excludeStore($store);
                if ($delayedAmount = $stocks->filterByStores($storesDelay)->getTotalAmount()) {
                    $delayedStockResult->setType(StockResult::TYPE_DELAYED);
                    if ($delayedAmount < $amount) {
                        $unavailableStockResult = $delayedStockResult->splitByAmount($delayedAmount);
                    }
                    $stockResultCollection->add($delayedStockResult);
                }

                /**
                 * Часть товара (или все количество) не в наличии
                 */
                if ($unavailableStockResult) {
                    $stockResultCollection->add(
                        $unavailableStockResult->setType(StockResult::TYPE_UNAVAILABLE)
                    );
                }
            }
        }

        return $stockResultCollection;
    }

    /**
     * @param string $deliveryCode
     * @param string $deliveryZone
     * @param string $locationCode
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @return StoreCollection
     */
    public static function getAvailableStores(
        string $deliveryCode,
        string $deliveryZone,
        string $locationCode = ''
    ): StoreCollection
    {
        $serviceContainer = Application::getInstance()->getContainer();
        /** @var StoreService $storeService */
        $storeService = $serviceContainer->get('store.service');
        /** @var LocationService $locationService */
        $locationService = $serviceContainer->get('location.service');
        if (!$locationCode) {
            $locationCode = $locationService->getCurrentLocation();
        }

        $result = new StoreCollection();
        switch ($deliveryCode) {
            case DeliveryService::DPD_DELIVERY_CODE:
            case DeliveryService::DPD_PICKUP_CODE:
                $result = $storeService->getStoresByLocation(
                    $locationCode,
                    StoreService::TYPE_STORE
                )->getStores();
                break;
            case DeliveryService::INNER_PICKUP_CODE:
                $result = $storeService->getShopsByLocation($locationCode);
                break;
            case DeliveryService::INNER_DELIVERY_CODE:
                switch ($deliveryZone) {
                    case DeliveryService::ZONE_1:
                    case DeliveryService::ZONE_5:
                    case DeliveryService::ZONE_6:
                    case DeliveryService::ZONE_KALUGA:
                        /**
                         * условие доставки в эти зоны - наличие на складе
                         */
                        $result = $storeService->getStoresByLocation($locationCode, StoreService::TYPE_STORE)->getStores();
                        break;
                    case DeliveryService::ZONE_2:
                    case DeliveryService::ZONE_NIZHNY_NOVGOROD:
                    case DeliveryService::ZONE_NIZHNY_NOVGOROD_REGION:
                    case DeliveryService::ZONE_VLADIMIR:
                    case DeliveryService::ZONE_VLADIMIR_REGION:
                    case DeliveryService::ZONE_VORONEZH:
                    case DeliveryService::ZONE_VORONEZH_REGION:
                    case DeliveryService::ZONE_YAROSLAVL:
                    case DeliveryService::ZONE_YAROSLAVL_REGION:
                    case DeliveryService::ZONE_TULA:
                    case DeliveryService::ZONE_TULA_REGION:
                    case DeliveryService::ZONE_KALUGA_REGION:
                    case DeliveryService::ZONE_IVANOVO:
                    case DeliveryService::ZONE_IVANOVO_REGION:
                        /**
                         * условие доставки в эту зону - наличие в базовом магазине
                         */
                        $result = $storeService->getBaseShops($locationCode);
                        if ($result->isEmpty()) {
                            $result = $storeService
                                ->getStoresByLocation($locationCode, StoreService::TYPE_BASE_SHOP)
                                ->getStores()
                                ->getBaseShops();
                        }
                        break;
                }
                break;
            case DeliveryService::DELIVERY_DOSTAVISTA_CODE:
                switch ($deliveryZone) {
                    case DeliveryService::ZONE_MOSCOW:
                        /**
                         * условие доставки в эту зону - наличие всех офферов в одном магазине
                         */
                        $result = $storeService->getStoresByLocation($locationCode, StoreService::TYPE_SHOP)->getStores();
                        break;
                }
                break;
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isCalculatePriceImmediately(): bool
    {
        return static::$isCalculatePriceImmediately;
    }

    /**
     * @return array
     * @throws ArgumentException
     */
    protected function getConfigStructure(): array
    {
        $currency = $this->currency;

        $currencyList = CurrencyManager::getCurrencyList();
        if (isset($currencyList[$this->currency])) {
            $currency = $currencyList[$this->currency];
        }
        unset($currencyList);

        $result = [
            'MAIN' => [
                'TITLE'       => Loc::getMessage('SALE_DLVR_HANDL_SMPL_TAB_MAIN'),
                'DESCRIPTION' => Loc::getMessage('SALE_DLVR_HANDL_SMPL_TAB_MAIN_DESCR'),
                'ITEMS'       => [
                    'CURRENCY' => [
                        'TYPE'       => 'DELIVERY_READ_ONLY',
                        'NAME'       => Loc::getMessage('SALE_DLVR_HANDL_SMPL_CURRENCY'),
                        'VALUE'      => $this->currency,
                        'VALUE_VIEW' => $currency,
                    ],
                ],
            ],
        ];

        return $result;
    }
}
