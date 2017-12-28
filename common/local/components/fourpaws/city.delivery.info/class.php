<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Location\Exception\CityNotFoundException;
use FourPaws\Location\LocationService;
use FourPaws\Location\Model\City;
use FourPaws\UserBundle\Service\UserCitySelectInterface;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsCityDeliveryInfoComponent extends \CBitrixComponent
{
    /**
     * @var UserCitySelectInterface
     */
    protected $userCitySelect;

    /**
     * @var LocationService
     */
    protected $locationService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);

        $this->userCitySelect = Application::getInstance()->getContainer()->get(UserCitySelectInterface::class);
        $this->locationService = Application::getInstance()->getContainer()->get('location.service');
        $this->deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
    }

    /** {@inheritdoc} */
    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 36000000;
        }

        if (empty($params['LOCATION_CODE'])) {
            $params['LOCATION_CODE'] = $this->userCitySelect->getSelectedCity()['CODE'];
        }

        return parent::onPrepareComponentParams($params);
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            if ($this->startResultCache()) {
                parent::executeComponent();
                $this->prepareResult();

                $this->includeComponentTemplate();
            }
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }

    /**
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws CityNotFoundException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @return $this
     */
    protected function prepareResult()
    {
        $defaultLocation = $this->locationService->getDefaultLocation();
        $currentLocation = $this->locationService->findLocationByCode($this->arParams['LOCATION_CODE']);

        $allDeliveryCodes = array_merge(DeliveryService::DELIVERY_CODES, DeliveryService::PICKUP_CODES);

        /** @var CalculationResult[] $defaultDeliveryResult */
        $defaultResult = $this->getDeliveries($defaultLocation['CODE'], $allDeliveryCodes);
        $defaultDeliveryResult = $this->getDelivery($defaultResult);
        $defaultPickupResult = $this->getPickup($defaultResult);
        /** @var City $defaultCity */
        $defaultCity = $this->locationService->getDefaultCity();

        if ($defaultLocation['CODE'] === $currentLocation['CODE']) {
            $currentDeliveryResult = $defaultDeliveryResult;
            $currentPickupResult = $defaultPickupResult;
            $currentCity = $defaultCity;
        } else {
            /** @var CalculationResult[] $currentDeliveryResult */
            $currentResult = $this->getDeliveries($currentLocation['CODE'], $allDeliveryCodes);
            $currentDeliveryResult = $this->getDelivery($currentResult);
            $currentPickupResult = $this->getPickup($currentResult);
            /** @var City $currentCity */
            $currentCity = $this->locationService->getCurrentCity();
        }

        if (!$defaultCity || !$currentCity) {
            $this->abortResultCache();
            throw new CityNotFoundException('Default city not found');
        }

        if (!$currentDeliveryResult && !$currentPickupResult) {
            $this->abortResultCache();

            return $this;
        }

        $this->arResult = [
            'CURRENT' => [
                'LOCATION' => $currentLocation,
                'CITY'     => [
                    'NAME'  => $currentCity->getName(),
                    'PHONE' => PhoneHelper::formatPhone($currentCity->getPhone()),
                ],
            ],
            'DEFAULT' => [
                'LOCATION' => $defaultLocation,
                'CITY'     => [
                    'NAME'  => $defaultCity->getName(),
                    'PHONE' => PhoneHelper::formatPhone($defaultCity->getPhone()),
                ],
            ],
        ];

        if ($currentDeliveryResult) {
            $this->arResult['CURRENT']['DELIVERY'] = [
                'PRICE'       => $currentDeliveryResult->getPrice(),
                'FREE_FROM'   => $currentDeliveryResult->getData()['FREE_FROM'],
                'INTERVALS'   => $currentDeliveryResult->getData()['INTERVALS'],
                'PERIOD_FROM' => $currentDeliveryResult->getPeriodFrom(),
                'PERIOD_TYPE' => $currentDeliveryResult->getPeriodType() ?? CalculationResult::PERIOD_TYPE_DAY,
                'CODE'        => $currentDeliveryResult->getData()['DELIVERY_CODE'],
            ];
        }

        if ($defaultDeliveryResult) {
            $this->arResult['DEFAULT']['DELIVERY'] = [
                'PRICE'       => $defaultDeliveryResult->getPrice(),
                'FREE_FROM'   => $defaultDeliveryResult->getData()['FREE_FROM'],
                'INTERVALS'   => $defaultDeliveryResult->getData()['INTERVALS'],
                'PERIOD_FROM' => $defaultDeliveryResult->getPeriodFrom(),
                'PERIOD_TYPE' => $defaultDeliveryResult->getPeriodType() ?? CalculationResult::PERIOD_TYPE_DAY,
                'CODE'        => $defaultDeliveryResult->getData()['DELIVERY_CODE'],
            ];
        }

        if ($currentPickupResult) {
            $this->arResult['CURRENT']['PICKUP'] = [
                'PRICE'       => $currentPickupResult->getPrice(),
                'CODE'        => $currentPickupResult->getData()['DELIVERY_CODE'],
                'PERIOD_FROM' => $currentPickupResult->getPeriodFrom(),
                'PERIOD_TYPE' => $currentPickupResult->getPeriodType() ?? CalculationResult::PERIOD_TYPE_DAY,
            ];
        }

        if ($defaultPickupResult) {
            $this->arResult['DEFAULT']['PICKUP'] = [
                'PRICE'       => $defaultPickupResult->getPrice(),
                'CODE'        => $defaultPickupResult->getData()['DELIVERY_CODE'],
                'PERIOD_FROM' => $defaultPickupResult->getPeriodFrom(),
                'PERIOD_TYPE' => $defaultPickupResult->getPeriodType() ?? CalculationResult::PERIOD_TYPE_DAY,
            ];
        }

        return $this;
    }

    /**
     * @param string $locationCode
     * @param array $possibleDeliveryCodes
     *
     * @return null|CalculationResult[]
     */
    protected function getDeliveries(string $locationCode, array $possibleDeliveryCodes = [])
    {
        return $this->deliveryService->getByLocation($locationCode, $possibleDeliveryCodes);
    }

    /**
     * @param CalculationResult[] $deliveries
     *
     * @return null|CalculationResult
     */
    protected function getDelivery($deliveries)
    {
        if (empty($deliveries)) {
            return null;
        }
        $deliveryCodes = DeliveryService::DELIVERY_CODES;
        $filtered = array_filter(
            $deliveries,
            function (CalculationResult $delivery) use ($deliveryCodes) {
                return in_array($delivery->getData()['DELIVERY_CODE'], $deliveryCodes);
            }
        );

        return reset($filtered);
    }

    /**
     * @param CalculationResult[] $deliveries
     *
     * @return null|CalculationResult
     */
    protected function getPickup($deliveries)
    {
        if (empty($deliveries)) {
            return null;
        }
        $pickupCodes = DeliveryService::PICKUP_CODES;
        $filtered = array_filter(
            $deliveries,
            function (CalculationResult $delivery) use ($pickupCodes) {
                return in_array($delivery->getData()['DELIVERY_CODE'], $pickupCodes);
            }
        );

        return reset($filtered);
    }
}
