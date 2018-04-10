<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\LocationBundle\Model\City;
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

    /**
     * FourPawsCityDeliveryInfoComponent constructor.
     *
     * @param CBitrixComponent|null $component
     *
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
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
        if (empty($params['LOCATION_CODE'])) {
            $params['LOCATION_CODE'] = $this->userCitySelect->getSelectedCity()['CODE'];
        }

        if (!isset($params['DELIVERY_CODES'])) {
            $params['DELIVERY_CODES'] = [];
        }

        return parent::onPrepareComponentParams($params);
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            parent::executeComponent();
            $this->prepareResult();

            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }

        return \array_filter([$this->arResult['CURRENT']['DELIVERY'], $this->arResult['CURRENT']['PICKUP']]);
    }

    /**
     * @return $this
     * @throws CityNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    protected function prepareResult()
    {
        $defaultLocation = $this->locationService->getDefaultLocation();
        $currentLocation = $this->locationService->findLocationByCode($this->arParams['LOCATION_CODE']);

        $allDeliveryCodes = array_merge(DeliveryService::DELIVERY_CODES, DeliveryService::PICKUP_CODES);
        if (!empty($this->arParams['DELIVERY_CODES'])) {
            $allDeliveryCodes = array_intersect($allDeliveryCodes, $this->arParams['DELIVERY_CODES']);
        }

        /** @var CalculationResultInterface[] $defaultDeliveryResult */
        $defaultResult = $this->getDeliveries($defaultLocation['CODE'], $allDeliveryCodes);
        $defaultDeliveryResult = $this->getDelivery($defaultResult);
        $defaultPickupResult = $this->getPickup($defaultResult);
        /** @var City $defaultCity */
        $defaultCity = $this->locationService->getDefaultCity();

        if ($this->isDefaultLocation($currentLocation['CODE'])) {
            $currentDeliveryResult = $defaultDeliveryResult;
            $currentPickupResult = $defaultPickupResult;
            $currentCity = $defaultCity;
        } else {
            /** @var CalculationResultInterface[] $currentDeliveryResult */
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
                'CITY' => [
                    'NAME' => $currentCity->getName(),
                    'PHONE' => PhoneHelper::formatPhone($currentCity->getPhone()),
                ],
            ],
            'DEFAULT' => [
                'LOCATION' => $defaultLocation,
                'CITY' => [
                    'NAME' => $defaultCity->getName(),
                    'PHONE' => PhoneHelper::formatPhone($defaultCity->getPhone()),
                ],
            ],
        ];

        if ($currentDeliveryResult) {
            $this->arResult['CURRENT']['DELIVERY'] = [
                'PRICE' => $currentDeliveryResult->getPrice(),
                'FREE_FROM' => $currentDeliveryResult->getFreeFrom(),
                'INTERVALS' => $currentDeliveryResult->getIntervals(),
                'PERIOD_FROM' => $currentDeliveryResult->getPeriodFrom(),
                'PERIOD_TYPE' => $currentDeliveryResult->getPeriodType() ?? BaseResult::PERIOD_TYPE_DAY,
                'DELIVERY_DATE' => $currentDeliveryResult->getDeliveryDate(),
                'CODE' => $currentDeliveryResult->getDeliveryCode(),
                'RESULT' => $currentDeliveryResult
            ];
        }

        if ($defaultDeliveryResult) {
            $this->arResult['DEFAULT']['DELIVERY'] = [
                'PRICE' => $defaultDeliveryResult->getPrice(),
                'FREE_FROM' => $defaultDeliveryResult->getFreeFrom(),
                'INTERVALS' => $defaultDeliveryResult->getIntervals(),
                'PERIOD_FROM' => $defaultDeliveryResult->getPeriodFrom(),
                'PERIOD_TYPE' => $defaultDeliveryResult->getPeriodType() ?? BaseResult::PERIOD_TYPE_DAY,
                'DELIVERY_DATE' => $defaultDeliveryResult->getDeliveryDate(),
                'CODE' => $defaultDeliveryResult->getDeliveryCode(),
                'RESULT' => $defaultDeliveryResult
            ];
        }

        if ($currentPickupResult) {
            $this->arResult['CURRENT']['PICKUP'] = [
                'PRICE' => $currentPickupResult->getPrice(),
                'CODE' => $currentPickupResult->getDeliveryCode(),
                'PERIOD_FROM' => $currentPickupResult->getPeriodFrom(),
                'PERIOD_TYPE' => $currentPickupResult->getPeriodType() ?? BaseResult::PERIOD_TYPE_DAY,
                'DELIVERY_DATE' => $currentPickupResult->getDeliveryDate(),
                'RESULT' => $currentPickupResult
            ];
        }

        if ($defaultPickupResult) {
            $this->arResult['DEFAULT']['PICKUP'] = [
                'PRICE' => $defaultPickupResult->getPrice(),
                'CODE' => $defaultPickupResult->getDeliveryCode(),
                'PERIOD_FROM' => $defaultPickupResult->getPeriodFrom(),
                'PERIOD_TYPE' => $defaultPickupResult->getPeriodType() ?? BaseResult::PERIOD_TYPE_DAY,
                'DELIVERY_DATE' => $defaultPickupResult->getDeliveryDate(),
                'RESULT' => $defaultPickupResult
            ];
        }

        return $this;
    }

    /**
     * @param string $locationCode
     * @param array $possibleDeliveryCodes
     *
     * @return null|CalculationResultInterface[]
     */
    protected function getDeliveries(string $locationCode, array $possibleDeliveryCodes = [])
    {
        return $this->deliveryService->getByLocation($locationCode, $possibleDeliveryCodes);
    }

    /**
     * @param CalculationResultInterface[] $deliveries
     *
     * @return null|CalculationResultInterface
     */
    protected function getDelivery($deliveries)
    {
        if (empty($deliveries)) {
            return null;
        }

        $filtered = array_filter(
            $deliveries,
            function (CalculationResultInterface $delivery) {
                return $this->deliveryService->isDelivery($delivery);
            }
        );

        return reset($filtered);
    }

    /**
     * @param CalculationResultInterface[] $deliveries
     *
     * @return null|CalculationResultInterface
     */
    protected function getPickup($deliveries)
    {
        if (empty($deliveries)) {
            return null;
        }

        $filtered = array_filter(
            $deliveries,
            function (CalculationResultInterface $delivery) {
                return $this->deliveryService->isPickup($delivery);
            }
        );

        return reset($filtered);
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    protected function isDefaultLocation(string $code): bool
    {
        return $code === $this->locationService->getDefaultLocation()['CODE'];
    }
}
