<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\Location\Model\City;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use Bitrix\Sale\Delivery\CalculationResult;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsCityDeliveryInfoComponent extends \CBitrixComponent
{
    const PICKUP_CODES = [
        DeliveryService::INNER_PICKUP_CODE,
        DeliveryService::DPD_PICKUP_CODE,
    ];

    const DELIVERY_CODES = [
        DeliveryService::INNER_DELIVERY_CODE,
        DeliveryService::DPD_DELIVERY_CODE,
    ];

    /** {@inheritdoc} */
    public function onPrepareComponentParams($params): array
    {
        if (empty($params['LOCATION_CODE'])) {
            /** @var \FourPaws\UserBundle\Service\UserService $userService */
            $userService = Application::getInstance()
                                      ->getContainer()
                                      ->get('FourPaws\UserBundle\Service\UserCitySelectInterface');
            $params['LOCATION_CODE'] = $userService->getSelectedCity()['CODE'];
        }

        return $params;
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            if ($this->startResultCache()) {
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
     * @return $this
     */
    protected function prepareResult()
    {
        /** @var \FourPaws\Location\LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');
        /** @var \FourPaws\UserBundle\Service\UserService $userService */
        $userService = Application::getInstance()
                                  ->getContainer()
                                  ->get('FourPaws\UserBundle\Service\UserCitySelectInterface');

        $defaultLocation = $locationService->getDefaultLocation();
        $currentLocation = $userService->getSelectedCity();

        /** @var CalculationResult $defaultDeliveryResult */
        $defaultDeliveryResult = $this->getDelivery($defaultLocation['CODE'], self::DELIVERY_CODES);
        /** @var CalculationResult $defaultPickupResult */
        $defaultPickupResult = $this->getDelivery($defaultLocation['CODE'], self::PICKUP_CODES);
        /** @var City $defaultCity */
        $defaultCity = $locationService->getDefaultCity();

        if ($defaultLocation['CODE'] === $currentLocation['CODE']) {
            $currentDeliveryResult = $defaultDeliveryResult;
            $currentPickupResult = $defaultPickupResult;
            $currentCity = $defaultCity;
        } else {
            /** @var CalculationResult $currentDeliveryResult */
            $currentDeliveryResult = $this->getDelivery($currentLocation['CODE'], self::DELIVERY_CODES);
            /** @var CalculationResult $currentPickupResult */
            $currentPickupResult = $this->getDelivery($currentLocation['CODE'], self::PICKUP_CODES);
            /** @var City $currentCity */
            $currentCity = $locationService->getCurrentCity();
        }

        if (null === $defaultCity || null === $currentCity || null === $currentDeliveryResult) {
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
                'CODE'        => $currentDeliveryResult->getData()['DELIVERY_CODE'],
            ];
        }

        if ($defaultDeliveryResult) {
            $this->arResult['DEFAULT']['DELIVERY'] = [
                'PRICE'       => $defaultDeliveryResult->getPrice(),
                'FREE_FROM'   => $defaultDeliveryResult->getData()['FREE_FROM'],
                'INTERVALS'   => $defaultDeliveryResult->getData()['INTERVALS'],
                'PERIOD_FROM' => $currentDeliveryResult->getPeriodFrom(),
                'CODE'        => $currentDeliveryResult->getData()['DELIVERY_CODE'],
            ];
        }

        if ($currentPickupResult) {
            $this->arResult['CURRENT']['PICKUP'] = [
                'PRICE'       => $currentPickupResult->getPrice(),
                'CODE'        => $currentPickupResult->getData()['DELIVERY_CODE'],
                'PERIOD_FROM' => $currentPickupResult->getPeriodFrom(),
            ];
        }

        if ($defaultPickupResult) {
            $this->arResult['DEFAULT']['PICKUP'] = [
                'PRICE' => $defaultPickupResult->getPrice(),
                'CODE'  => $defaultPickupResult->getData()['DELIVERY_CODE'],
            ];
        }

        return $this;
    }

    /**
     * @param string $locationCode
     * @param array $possibleDeliveryCodes
     *
     * @return CalculationResult|null
     */
    protected function getDelivery(string $locationCode, array $possibleDeliveryCodes = [])
    {
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        $deliveries = $deliveryService->getByLocation($locationCode, $possibleDeliveryCodes);

        /** @var CalculationResult $defaultResult */
        return reset($deliveries);
    }
}
