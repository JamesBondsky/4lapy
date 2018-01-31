<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;
use Doctrine\Common\Collections\ArrayCollection;
use Bitrix\Sale\Delivery\CalculationResult;
use Doctrine\Common\Collections\Collection;
use FourPaws\App\Application;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\Location\LocationService;
use FourPaws\StoreBundle\Collection\StockCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Service\UserCitySelectInterface;

abstract class DeliveryServiceHandlerBase extends Base implements DeliveryServiceInterface
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

    public function __construct($initParams)
    {
        $this->locationService = Application::getInstance()->getContainer()->get('location.service');
        $this->storeService = Application::getInstance()->getContainer()->get('store.service');
        $this->deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        $this->userService = Application::getInstance()
                                        ->getContainer()
                                        ->get(UserCitySelectInterface::class);
        parent::__construct($initParams);
    }

    /**
     * @return bool
     */
    public static function whetherAdminExtraServicesShow()
    {
        return static::$whetherAdminExtraServicesShow;
    }

    /**
     * Получает коллекцию офферов и проставляет им наличие
     *
     * @param string $locationCode
     * @param BasketBase $basket
     *
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

        $offers->loadStocks($stores);

        return $offers;
    }

    /**
     * @param BasketBase $basket
     * @param OfferCollection $offers
     * @param StoreCollection $storesAvailable склады, где товары считается "в наличии"
     * @param StoreCollection $storesDelay склады, с которых производится поставка на $storesAvailable
     * @param StockResultCollection $stockResultCollection
     *
     * @return StockResultCollection
     */
    public static function getStocks(
        BasketBase $basket,
        OfferCollection $offers,
        StoreCollection $storesAvailable,
        StoreCollection $storesDelay,
        StockResultCollection $stockResultCollection = null
    ): StockResultCollection {
        if (!$stockResultCollection) {
            $stockResultCollection = new StockResultCollection();
        }

        /**
         * Рассчитывается дата доставки в соответствии с графиком работы магазинов/складов
         */
        $pickupDate = new \DateTime();
        $hour = (int)$pickupDate->format('H');
        $totalSchedule = $storesAvailable->getTotalSchedule();
        if ($hour < $totalSchedule['from']) {
            $pickupDate->setTime($totalSchedule['from'] + 1, 0, 0);
        } elseif ($hour > $totalSchedule['to']) {
            $pickupDate->modify('+1 day');
            $pickupDate->setTime($totalSchedule['from'] + 1, 0, 0);
        } else {
            $pickupDate->modify('+1 hour');
        }

        /** @var Offer $offer */
        foreach ($offers as $offer) {
            $basketItem = null;
            /** @var BasketItem $item */
            foreach ($basket as $item) {
                if ($item->getProductId() == $offer->getId()) {
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

            $stockResult->setDeliveryDate($pickupDate);

            if ($offer->isByRequest()) {
                $stockResult->setType(StockResult::TYPE_DELAYED);
                continue;
            }

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
                if ($delayedAmount = $stocks->filterByStores($storesDelay)->getTotalAmount()) {
                    $delayedStockResult = clone $stockResult;
                    $delayedStockResult->setType(StockResult::TYPE_DELAYED)
                                       ->setAmount($delayedAmount >= $neededAmount ? $neededAmount : $delayedAmount)
                                       ->setDelayStores($storesDelay)
                        /* @todo расчет по графику поставок */
                                       ->setDeliveryDate((new \DateTime())->modify('+10 days'));
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
    public function isCalculatePriceImmediately()
    {
        return static::$isCalculatePriceImmediately;
    }

    /**
     * @return array
     */
    protected function getConfigStructure()
    {
        $currency = $this->currency;

        if (Loader::includeModule('currency')) {
            $currencyList = CurrencyManager::getCurrencyList();
            if (isset($currencyList[$this->currency])) {
                $currency = $currencyList[$this->currency];
            }
            unset($currencyList);
        }

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

    protected function calculateConcrete(Shipment $shipment)
    {
        $result = new CalculationResult();

        if (!$this->deliveryService->getDeliveryZoneCode($shipment)) {
            $result->addError(new Error('Не указано местоположение доставки'));
        }

        return $result;
    }
}
