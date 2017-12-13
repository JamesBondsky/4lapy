<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Location\LocationTable;
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
    public function getDeliveryZoneCode(Shipment $shipment, $skipLocations = true)
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
        foreach ($availableZones as $code => $zone) {
            if ($skipLocations && $zone['TYPE'] == static::LOCATION_RESTRICTION_TYPE_LOCATION) {
                continue;
            }
            if (!empty(array_intersect($deliveryLocationPath, $zone['LOCATIONS']))) {
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

        $locationCodes = [];
        while ($restriction = $restrictions->fetch()) {
            switch ($restriction['LOCATION_TYPE']) {
                case static::LOCATION_RESTRICTION_TYPE_LOCATION:
                    $locationCodes[] = $restriction['LOCATION_CODE'];
                    break;
                case static::LOCATION_RESTRICTION_TYPE_GROUP:
                    if (isset($allZones[$restriction['LOCATION_CODE']])) {
                        $item = $allZones[$restriction['LOCATION_CODE']];
                        $item['TYPE'] = static::LOCATION_RESTRICTION_TYPE_GROUP;
                        $result[$restriction['LOCATION_CODE']] = $item;
                    }
                    break;
            }
        }

        if (!empty($locationCodes)) {
            $locations = LocationTable::getList(
                [
                    'filter' => ['CODE' => $locationCodes],
                    'select' => ['ID', 'CODE', 'NAME.NAME'],
                ]
            );

            while ($location = $locations->Fetch()) {
                // сделано, чтобы отдельные местоположения были впереди групп,
                // т.к. группы могут их включать
                $result = [
                        $location['CODE'] => [
                            'CODE'      => $location['CODE'],
                            'NAME'      => $location['SALE_LOCATION_LOCATION_NAME_NAME'],
                            'ID'        => $location['ID'],
                            'LOCATIONS' => [$location['CODE']],
                            'TYPE'      => static::LOCATION_RESTRICTION_TYPE_LOCATION,
                        ],
                    ] + $result;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getAllZones(): array
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
