<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Handler;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\Services\Base;
use FourPaws\App\Application;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Service\IntervalService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\StoreBundle\Collection\StoreCollection;
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
     * @param $initParams
     *
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
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
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @return bool|OfferCollection
     */
    public static function getOffers(string $locationCode, BasketBase $basket)
    {
        if ($basket->isEmpty()) {
            return false;
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
            return false;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var StoreService $storeService */
        $storeService = Application::getInstance()->getContainer()->get('store.service');
        $stores = $storeService->getByLocation($locationCode);
        if ($stores->isEmpty()) {
            return false;
        }

        /** @var OfferCollection $offers */
        $offers = (new OfferQuery())->withFilterParameter('ID', $offerIds)->exec();
        if ($offers->isEmpty()) {
            return false;
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
     * @return StockResultCollection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
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
            $neededAmount = $basketItem->getQuantity();

            $stockResult = new StockResult();
            $stockResult->setAmount($neededAmount)
                ->setOffer($offer)
                ->setStores($storesAvailable)
                ->setPrice($basketItem->getPrice());

            $stocks = $offer->getStocks();
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
        }

        return $stockResultCollection;
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
     * @throws \Bitrix\Main\ArgumentException
     */
    protected function getConfigStructure()
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
