<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;
use FourPaws\App\Application;
use FourPaws\Location\LocationService;
use FourPaws\UserBundle\Service\UserCitySelectInterface;

abstract class DeliveryServiceBase extends Base implements DeliveryServiceInterface
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
     * @var array
     */
    protected $availableZones = [];

    /**
     * @var LocationService $locationService
     */
    protected $locationService;

    /**
     * @var UserCitySelectInterface
     */
    protected $userService;

    public function __construct($initParams)
    {
        $this->locationService = Application::getInstance()->getContainer()->get('location.service');
        $this->userService = Application::getInstance()
                                        ->getContainer()
                                        ->get('FourPaws\UserBundle\Service\UserCitySelectInterface');
        parent::__construct($initParams);
    }

    public function isCompatible(Shipment $shipment)
    {
        return parent::isCompatible($shipment);
    }

    public function isCalculatePriceImmediately()
    {
        return static::$isCalculatePriceImmediately;
    }

    public static function whetherAdminExtraServicesShow()
    {
        return static::$whetherAdminExtraServicesShow;
    }

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
                    ]
                ],
            ],
        ];

        return $result;
    }
}
