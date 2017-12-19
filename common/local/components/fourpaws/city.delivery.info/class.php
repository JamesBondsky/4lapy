<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\Location\Model\City;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use Bitrix\Sale\Delivery\CalculationResult;

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
     *
     * @throws SystemException
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

        if ($defaultLocation['CODE'] == $currentLocation['CODE']) {
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

        if (empty($currentDeliveryResult) || empty($currentCity)) {
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
                'DELIVERY' => [
                    'PRICE'     => $currentDeliveryResult->getPrice(),
                    'FREE_FROM' => $currentDeliveryResult->getTmpData()['FREE_FROM'],
                    'INTERVALS' => $currentDeliveryResult->getTmpData()['INTERVALS'],
                ],
                'PICKUP'   => [
                    'PRICE'     => $currentPickupResult->getPrice(),
                ],
                'PAYMENTS' => [

                ],
            ],
            'DEFAULT' => [
                'LOCATION' => $defaultLocation,
                'CITY'     => [
                    'NAME'  => $defaultCity->getName(),
                    'PHONE' => PhoneHelper::formatPhone($defaultCity->getPhone()),
                ],
                'DELIVERY' => [
                    'PRICE'     => $defaultDeliveryResult->getPrice(),
                    'FREE_FROM' => $defaultDeliveryResult->getTmpData()['FREE_FROM'],
                    'INTERVALS' => $defaultDeliveryResult->getTmpData()['INTERVALS'],
                ],
                'PICKUP'   => [
                    'PRICE'     => $currentPickupResult->getPrice(),
                ],
                'PAYMENTS' => [

                ],
            ],
        ];

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

        /** @var CalculationResult $defaultResult */
        return reset(
            $deliveryService->getByLocation(
                $locationCode,
                $possibleDeliveryCodes
            /*
            [
                DeliveryService::INNER_DELIVERY_CODE,
                DeliveryService::DPD_DELIVERY_CODE,
            ]
            */
            )
        );
    }
}
