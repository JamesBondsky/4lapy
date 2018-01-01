<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;
use FourPaws\App\Application;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Location\LocationService;
use FourPaws\StoreBundle\Collection\StockCollection;
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
    protected static $whetherAdminExtraServicesShow = false;

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
                                        ->get('FourPaws\UserBundle\Service\UserCitySelectInterface');
        parent::__construct($initParams);
    }

    /**
     * @param Shipment $shipment
     *
     * @return bool
     */
    public function isCompatible(Shipment $shipment)
    {
        return parent::isCompatible($shipment);
    }

    /**
     * @return bool
     */
    public function isCalculatePriceImmediately()
    {
        return static::$isCalculatePriceImmediately;
    }

    /**
     * @return bool
     */
    public static function whetherAdminExtraServicesShow()
    {
        return static::$whetherAdminExtraServicesShow;
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
            "MAIN" => [
                "TITLE"       => Loc::getMessage("SALE_DLVR_HANDL_SMPL_TAB_MAIN"),
                "DESCRIPTION" => Loc::getMessage("SALE_DLVR_HANDL_SMPL_TAB_MAIN_DESCR"),
                "ITEMS"       => [
                    "CURRENCY" => [
                        "TYPE"       => "DELIVERY_READ_ONLY",
                        "NAME"       => Loc::getMessage("SALE_DLVR_HANDL_SMPL_CURRENCY"),
                        "VALUE"      => $this->currency,
                        "VALUE_VIEW" => $currency,
                    ],
                ],
            ],
        ];

        return $result;
    }

    protected function calculateConcrete(Shipment $shipment)
    {
        $result = new \Bitrix\Sale\Delivery\CalculationResult();

        if (!$this->deliveryService->getDeliveryZoneCode($shipment)) {
            $result->addError(new Error('Не указано местоположение доставки'));
        }

        return $result;
    }

    public static function getStocks($locationCode, $offerData)
    {
        /* @todo нужно как-то оптимизировать метод, т.к. он выполняется для каждого профиля доставки, который подходит по зоне */
        if (empty($offerData)) {
            return [];
        }
        /** @var StoreService $storeService */
        $storeService = Application::getInstance()->getContainer()->get('store.service');
        $stores = $storeService->getByLocation($locationCode);
        if ($stores->isEmpty()) {
            return [];
        }

        $offers = (new OfferQuery())->withFilterParameter('ID', array_keys($offerData))->exec();
        if ($offers->isEmpty()) {
            return [];
        }

        $offersByRequest = $offers->filter(function (Offer $offer) {
            return $offer->isByRequest();
        });
        $availableOffers = $offers->filter(function (Offer $offer) {
            return !$offer->isByRequest();
        });

        if (!$availableOffers->isEmpty()) {
            $offerIds = [];
            foreach ($availableOffers as $offer) {
                $offerIds[] = $offer->getId();
            }
            $stocks = $storeService->getStocks($offerIds, $stores);
        } else {
            $stocks = new StockCollection();
        }

        /* @todo получение графиков поставок */
        $deliverySchedules = [];
        if (!$offersByRequest->isEmpty()) {
            $deliverySchedules = [];
        }

        return [
            'AVAILABLE_OFFERS'   => $availableOffers,
            'OFFERS_BY_REQUEST'   => $offersByRequest,
            'STOCKS'             => $stocks,
            'DELIVERY_SCHEDULES' => $deliverySchedules,
        ];
    }
}
