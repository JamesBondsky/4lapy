<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
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
                    ],
                ],
            ],
        ];

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeliveryZoneCode(Shipment $shipment)
    {
        if (!$deliveryLocation = $this->getDeliveryLocation($shipment)) {
            return false;
        }

        $deliveryLocationPath = [$deliveryLocation];
        if ($location = $this->locationService->findLocationByCode($deliveryLocation)) {
            if ($location['PATH']) {
                $deliveryLocationPath = array_merge(
                    $deliveryLocationPath,
                    array_column($location['PATH'], 'CODE')
                );
            }
        }

        $availableZones = $this->getAvailableZones();
        foreach ($availableZones as $code => $locations) {
            if (!empty(array_intersect($deliveryLocationPath, $locations))) {
                return $code;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableZones(): array
    {
        $result = [];

        $restrictions = DeliveryLocationTable::getList(
            [
                'filter' => ['DELIVERY_ID' => $this->getId()],
            ]
        );

        $allZones = $this->getAllZones();

        while ($restriction = $restrictions->fetch()) {
            switch ($restriction['LOCATION_TYPE']) {
                case static::LOCATION_RESTRICTION_TYPE_LOCATION:
                    $result[$restriction['LOCATION_CODE']] = [$restriction['LOCATION_CODE']];
                    break;
                case static::LOCATION_RESTRICTION_TYPE_GROUP:
                    if (isset($allZones[$restriction['LOCATION_CODE']])) {
                        $result[$restriction['LOCATION_CODE']] = $allZones[$restriction['LOCATION_CODE']]['LOCATIONS'];
                    }
                    break;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAllZones(): array
    {
        return $this->locationService->getLocationGroups(true);
    }

    /**
     * Получение кода местоположения для доставки
     *
     * @param Shipment $shipment
     *
     * @return null|string
     */
    protected function getDeliveryLocation(Shipment $shipment)
    {
        $order = $shipment->getParentOrder();
        $propertyCollection = $order->getPropertyCollection();
        $locationProp = $propertyCollection->getDeliveryLocation();

        if ($locationProp && $locationProp->getValue()) {
            return $locationProp->getValue();
        }

        return null;
    }

    protected function calculateConcrete(Shipment $shipment)
    {
        $result = new \Bitrix\Sale\Delivery\CalculationResult();

        if (!$this->getDeliveryZoneCode($shipment)) {
            $result->addError(new Error('Не указано местоположение доставки'));
        }

        return $result;
    }
}
