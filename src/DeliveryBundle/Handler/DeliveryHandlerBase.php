<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Handler;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\Services\Base;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Service\IntervalService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\Migrator\Provider\Delivery;
use FourPaws\StoreBundle\Collection\StoreCollection;
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
     * @var IntervalService
     */
    protected $intervalService;

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
        $this->userService = $serviceContainer->get(UserCitySelectInterface::class);
        $this->intervalService = $serviceContainer->get(IntervalService::class);
        parent::__construct($initParams);
    }

    /**
     * Получает коллекцию офферов и проставляет им наличие
     *
     * @param string $locationCode
     * @param BasketBase $basket
     *
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @return null|OfferCollection
     */
    public static function getOffers(string $locationCode, BasketBase $basket): ?OfferCollection
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
            $offerIds[] = $offerId;
        }

        if (empty($offerIds)) {
            return null;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var StoreService $storeService */
        $storeService = Application::getInstance()->getContainer()->get('store.service');
        $stores = $storeService->getByLocation($locationCode);
        if ($stores->isEmpty()) {
            return null;
        }

        /** @var OfferCollection $offers */
        $offers = (new OfferQuery())->withFilterParameter('ID', $offerIds)->exec();
        if ($offers->isEmpty()) {
            return null;
        }

        /** @var Offer $offer */
        foreach ($offers as $offer) {
            if (!$offer->isByRequest()) {
                $offer->withStocks($offer->getAllStocks()->filterByStores($stores));
            }
        }

        return $offers;
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param BasketBase $basket
     * @param OfferCollection $offers
     * @param StoreCollection $storesAvailable
     * @param StockResultCollection|null $stockResultCollection
     *
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @return StockResultCollection
     */
    public static function getStocks(
        BasketBase $basket,
        OfferCollection $offers,
        StoreCollection $storesAvailable,
        StockResultCollection $stockResultCollection = null
    ): StockResultCollection {
        if (!$stockResultCollection) {
            $stockResultCollection = new StockResultCollection();
        }

        /** @var Offer $offer */
        foreach ($offers as $offer) {
            $basketItem = null;
            /** @var BasketItem $item */
            foreach ($basket as $item) {
                if ((int)$item->getProductId() === $offer->getId()) {
                    $basketItem = $item;
                    break;
                }
            }
            if (!$basketItem) {
                continue;
            }

            static::getStocksForItem(
                $offer,
                $basketItem->getQuantity(),
                $basketItem->getPrice(),
                $storesAvailable,
                $stockResultCollection
            );
        }

        return $stockResultCollection;
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param Offer $offer
     * @param int $neededAmount
     * @param float $price
     * @param StoreCollection $storesAvailable
     * @param StockResultCollection|null $stockResultCollection
     *
     * @return StockResultCollection
     * @throws ApplicationCreateException
     * @throws NotFoundException
     */
    public static function getStocksForItem(
        Offer $offer,
        int $neededAmount,
        float $price,
        StoreCollection $storesAvailable,
        StockResultCollection $stockResultCollection = null
    ) {
        if (null === $stockResultCollection) {
            $stockResultCollection = new StockResultCollection();
        }

        $stockResult = new StockResult();
        $stockResult->setAmount($neededAmount)
            ->setOffer($offer)
            ->setStores($storesAvailable)
            ->setPrice($price);

        $stocks = $offer->getAllStocks();
        if ($availableAmount = $stocks->filterByStores($storesAvailable)->getTotalAmount()) {
            if ($availableAmount < $neededAmount) {
                $stockResult->setAmount($availableAmount);
                $neededAmount -= $availableAmount;
            } else {
                $neededAmount = 0;
            }
            $stockResultCollection->add($stockResult);
        }
        /**
         * Товар в наличии не полностью. Часть будет отложена
         */
        if ($neededAmount) {
            $storesDelay = $offer->getAllStocks()->getStores()->excludeStores($storesAvailable);
            if ($delayedAmount = $stocks->filterByStores($storesDelay)->getTotalAmount()) {
                $delayedStockResult = clone $stockResult;
                $delayedStockResult->setType(StockResult::TYPE_DELAYED)
                    ->setAmount($delayedAmount >= $neededAmount ? $neededAmount : $delayedAmount);
                $stockResultCollection->add($delayedStockResult);

                $neededAmount = ($delayedAmount >= $neededAmount) ? 0 : $neededAmount - $delayedAmount;
            }

            /**
             * Часть товара (или все количество) не в наличии
             */
            if ($neededAmount) {
                $unavailableStockResult = clone $stockResult;
                $unavailableStockResult->setType(StockResult::TYPE_UNAVAILABLE)
                    ->setAmount($neededAmount);
                $stockResultCollection->add($unavailableStockResult);
            }
        }

        return $stockResultCollection;
    }

    /**
     * @param string $deliveryCode
     * @param string $deliveryZone
     * @param string $locationCode

     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @return StoreCollection
     */
    public static function getAvailableStores(
        string $deliveryCode,
        string $deliveryZone,
        string $locationCode = ''
    ): StoreCollection {
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
                $result = $storeService->getByLocation(
                    $locationCode,
                    StoreService::TYPE_STORE
                );
                break;
            case DeliveryService::INNER_PICKUP_CODE:
                $result = $storeService->getByLocation(
                    $locationCode,
                    StoreService::TYPE_SHOP,
                    true
                );
                break;
            case DeliveryService::INNER_DELIVERY_CODE:
                switch ($deliveryZone) {
                    case DeliveryService::ZONE_1:
                        /**
                         * условие доставки в эту зону - наличие на складе
                         */
                        $result = $storeService->getByLocation($locationCode, StoreService::TYPE_STORE);
                        break;
                    case DeliveryService::ZONE_2:
                        /**
                         * условие доставки в эту зону - наличие в базовом магазине
                         */
                        $result = $storeService->getByLocation($locationCode, StoreService::TYPE_ALL)
                            ->getBaseShops();
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
                'TITLE' => Loc::getMessage('SALE_DLVR_HANDL_SMPL_TAB_MAIN'),
                'DESCRIPTION' => Loc::getMessage('SALE_DLVR_HANDL_SMPL_TAB_MAIN_DESCR'),
                'ITEMS' => [
                    'CURRENCY' => [
                        'TYPE' => 'DELIVERY_READ_ONLY',
                        'NAME' => Loc::getMessage('SALE_DLVR_HANDL_SMPL_CURRENCY'),
                        'VALUE' => $this->currency,
                        'VALUE_VIEW' => $currency,
                    ],
                ],
            ],
        ];

        return $result;
    }
}
