<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Service\IntervalService;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsCityDeliveryInfoComponent extends FourPawsComponent
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
     * @var IntervalService
     */
    protected $intervalService;

    /**
     * FourPawsCityDeliveryInfoComponent constructor.
     *
     * @param CBitrixComponent|null $component
     *
     * @throws LogicException
     * @throws SystemException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);

        $serviceContainer = Application::getInstance()->getContainer();
        $this->userCitySelect = $serviceContainer->get(UserCitySelectInterface::class);
        $this->locationService = $serviceContainer->get('location.service');
        $this->deliveryService = $serviceContainer->get('delivery.service');
        $this->intervalService = $serviceContainer->get(IntervalService::class);
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

        $params['return_result'] = $params['return_result'] ?? true;

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws CityNotFoundException
     * @throws DeliveryNotFoundException
     * @throws NotFoundException
     * @throws ObjectPropertyException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws LogicException
     */
    public function prepareResult(): void
    {
        if (!$this->arParams['LOCATION_CODE']) {
            $this->abortResultCache();
            throw new InvalidArgumentException('Location code not defined');
        }

        $location = $this->locationService->findLocationByCode($this->arParams['LOCATION_CODE']);

        $allDeliveryCodes = array_merge(DeliveryService::DELIVERY_CODES, DeliveryService::PICKUP_CODES);
        if (!empty($this->arParams['DELIVERY_CODES'])) {
            $allDeliveryCodes = array_intersect($allDeliveryCodes, $this->arParams['DELIVERY_CODES']);
        }

        /** @var CalculationResultInterface[] $deliveries */
        $deliveries = $this->getDeliveries($location['CODE'], $allDeliveryCodes);
        $deliveryResult = $this->getDelivery($deliveries);
        $pickupResult = $this->getPickup($deliveries);

        $defaultCity = $this->locationService->getDefaultCity();
        $currentCity = $this->locationService->getCurrentCity();

        if (!$defaultCity || !$currentCity) {
            $this->abortResultCache();
            throw new CityNotFoundException('Default city not found');
        }

        $delivery = null;
        $pickup = null;
        if ($deliveryResult || $pickupResult) {

            if (null !== $deliveryResult) {
                $delivery = [
                    'PRICE'         => $deliveryResult->getPrice(),
                    'FREE_FROM'     => $deliveryResult->getFreeFrom(),
                    'INTERVALS'     => $deliveryResult->getIntervals(),
                    'INTERVAL_DAYS' => $this->getIntervalDays($deliveryResult),
                    'PERIOD_FROM'   => $deliveryResult->getPeriodFrom(),
                    'PERIOD_TYPE'   => $deliveryResult->getPeriodType() ?? BaseResult::PERIOD_TYPE_DAY,
                    'DELIVERY_DATE' => $deliveryResult->getDeliveryDate(),
                    'CODE'          => $deliveryResult->getDeliveryCode(),
                    'ZONE'          => $deliveryResult->getDeliveryZone(),
                    'WEEK_DAYS'     => $deliveryResult->getWeekDays(),
                    'RESULT'        => $deliveryResult,
                ];
            }

            if (null !== $pickupResult) {
                $pickup = [
                    'PRICE'         => $pickupResult->getPrice(),
                    'CODE'          => $pickupResult->getDeliveryCode(),
                    'PERIOD_FROM'   => $pickupResult->getPeriodFrom(),
                    'PERIOD_TYPE'   => $pickupResult->getPeriodType() ?? BaseResult::PERIOD_TYPE_DAY,
                    'DELIVERY_DATE' => $pickupResult->getDeliveryDate(),
                    'RESULT'        => $pickupResult,
                ];
            }

            $this->arResult = [
                'LOCATION'   => $location,
                'ZONE'       => $this->deliveryService->getDeliveryZoneByLocation($location['CODE']),
                'CITY'       => [
                    'NAME'  => $currentCity ? $currentCity->getName() : $defaultCity->getName(),
                    'PHONE' => PhoneHelper::formatPhone(
                        $currentCity ? $currentCity->getPhone() : $defaultCity->getPhone()
                    ),
                ],
                'DELIVERY'   => $delivery,
                'PICKUP'     => $pickup,
                'DELIVERIES' => \array_filter([
                    $delivery,
                    $pickup,
                ]),
            ];
        } else {
            $this->abortResultCache();
        }
    }

    /**
     * @param string $locationCode
     * @param array  $possibleDeliveryCodes
     *
     * @return CalculationResultInterface[]
     * @throws RuntimeException
     */
    protected function getDeliveries(string $locationCode, array $possibleDeliveryCodes = [])
    {
        return $this->deliveryService->getByLocation($locationCode, $possibleDeliveryCodes);
    }

    /**
     * @param CalculationResultInterface[] $deliveries
     *
     * @return null|DeliveryResultInterface
     */
    protected function getDelivery($deliveries): ?DeliveryResultInterface
    {
        $filtered = array_filter(
            $deliveries,
            function (CalculationResultInterface $delivery) {
                return $this->deliveryService->isDelivery($delivery);
            }
        );

        return reset($filtered) ?: null;
    }

    /**
     * @param CalculationResultInterface[] $deliveries
     *
     * @return null|PickupResultInterface
     */
    protected function getPickup($deliveries): ?PickupResultInterface
    {
        $filtered = array_filter(
            $deliveries,
            function (CalculationResultInterface $delivery) {
                return $this->deliveryService->isPickup($delivery);
            }
        );

        return reset($filtered) ?: null;
    }

    /**
     * @param DeliveryResultInterface $delivery
     *
     * @param DateTime|null           $currentDate
     * @return ArrayCollection
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws DeliveryNotFoundException
     * @throws NotFoundException
     */
    public function getIntervalDays(DeliveryResultInterface $delivery, \DateTime $currentDate = null): ArrayCollection
    {
        return $this->intervalService->getIntervalDays($delivery, $currentDate);
    }
}
