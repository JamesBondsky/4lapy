<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Handler;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;
use Doctrine\Common\Collections\ArrayCollection;
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
     * Получает коллекцию офферов и проставляет им наличие
     *
     * @param string $locationCode
     * @param BasketBase $basket
     *
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @return null|ArrayCollection
     */
    public static function getOffers(string $locationCode, BasketBase $basket): ?ArrayCollection
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

        $offers = new ArrayCollection();

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var StoreService $storeService */
        $storeService = Application::getInstance()->getContainer()->get('store.service');
        $stores = $storeService->getStoresByLocation($locationCode);
        if ($stores->isEmpty()) {
            return null;
        }


        foreach ($offerIds as $offerId) {
            if ($offer = OfferQuery::getById($offerId)) {
                $offers[$offerId] = $offer;
            }
        }

        return $offers;
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param BasketBase $basket
     * @param ArrayCollection $offers
     * @param StoreCollection $storesAvailable
     *
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @return StockResultCollection
     */
    public static function getStocks(
        BasketBase $basket,
        ArrayCollection $offers,
        StoreCollection $storesAvailable
    ): StockResultCollection {
        $stockResultCollection = new StockResultCollection();

        foreach ($basket as $item) {
            $basketItem = null;

            /** @var Offer $offer */
            foreach ($offers as $offer) {
            /** @var BasketItem $item */
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
     * @param StoreCollection $stores
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
        StoreCollection $stores,
        StockResultCollection $stockResultCollection = null
    ): StockResultCollection {
        if (null === $stockResultCollection) {
            $stockResultCollection = new StockResultCollection();
        }

        /** @var Store $store */
        foreach ($stores->getIterator() as $store) {
            $amount = $neededAmount;
            $stockResult = new StockResult();
            $stockResult->setAmount($amount)
                ->setOffer($offer)
                ->setStore($store)
                ->setPrice($price);

            $stocks = $offer->getAllStocks();
            if ($availableAmount = $stocks->filterByStore($store)->getTotalAmount()) {
                if ($availableAmount < $amount) {
                    $stockResult->setAmount($availableAmount);
                    $amount -= $availableAmount;
                } else {
                    $amount = 0;
                }
                $stockResultCollection->add($stockResult);
            }

            /**
             * Товар в наличии не полностью. Часть будет отложена
             */
            if ($amount) {
                $storesDelay = $offer->getAllStocks()->getStores()->excludeStore($store);
                if ($delayedAmount = $stocks->filterByStores($storesDelay)->getTotalAmount()) {
                    $delayedStockResult = clone $stockResult;
                    $delayedStockResult->setType(StockResult::TYPE_DELAYED)
                        ->setAmount($delayedAmount >= $amount ? $amount : $delayedAmount);
                    $stockResultCollection->add($delayedStockResult);

                    $amount = ($delayedAmount >= $amount) ? 0 : $amount - $delayedAmount;
                }

                /**
                 * Часть товара (или все количество) не в наличии
                 */
                if ($amount) {
                    $unavailableStockResult = clone $stockResult;
                    $unavailableStockResult->setType(StockResult::TYPE_UNAVAILABLE)
                        ->setAmount($amount);
                    $stockResultCollection->add($unavailableStockResult);
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
                $result = $storeService->getStoresByLocation(
                    $locationCode,
                    StoreService::TYPE_STORE
                );
                break;
            case DeliveryService::INNER_PICKUP_CODE:
                $result = $storeService->getStoresByLocation(
                    $locationCode,
                    StoreService::TYPE_SHOP
                );
                break;
            case DeliveryService::INNER_DELIVERY_CODE:
                switch ($deliveryZone) {
                    case DeliveryService::ZONE_1:
                        /**
                         * условие доставки в эту зону - наличие на складе
                         */
                        $result = $storeService->getStoresByLocation($locationCode, StoreService::TYPE_STORE);
                        break;
                    case DeliveryService::ZONE_2:
                        /**
                         * условие доставки в эту зону - наличие в базовом магазине
                         */
                        $result = $storeService->getRegionalStores($locationCode, StoreService::TYPE_ALL)
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
