<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use Bitrix\Sale\Delivery\CalculationResult;

class FourPawsCityDeliveryInfoComponent extends \CBitrixComponent
{

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
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        /** @var \FourPaws\Location\LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');
        /** @var \FourPaws\UserBundle\Service\UserService $userService */
        $userService = Application::getInstance()
                                  ->getContainer()
                                  ->get('FourPaws\UserBundle\Service\UserCitySelectInterface');

        $defaultCity = $locationService->getDefaultLocation();
        $currentCity = $userService->getSelectedCity();

        /** todo добавить код доставки DPD */
        /** @var CalculationResult $defaultResult */
        $defaultResult = reset(
            $deliveryService->getByLocation($defaultCity['CODE'], [DeliveryService::INNER_DELIVERY_CODE])
        );

        if ($defaultCity['CODE'] == $currentCity['CODE']) {
            $currentResult = $defaultResult;
        } else {
            /** @var CalculationResult $currentResult */
            $currentResult = reset(
                $deliveryService->getByLocation($currentCity['CODE'], [DeliveryService::INNER_DELIVERY_CODE])
            );
        }

        if (empty($currentResult)) {
            $this->abortResultCache();

            return $this;
        }

        $this->arResult = [
            'CURRENT' => [
                'CITY_CODE' => $currentCity['CODE'],
                'CITY_NAME' => $currentCity['NAME'],
                'PRICE'     => $currentResult->getPrice(),
                'FREE_FROM' => 2000,
            ],
            'DEFAULT' => [
                'CITY_CODE' => $defaultCity['CODE'],
                'CITY_NAME' => $defaultCity['NAME'],
                'PRICE'     => $defaultResult->getPrice(),
                'FREE_FROM' => 2000,
            ],
        ];

        return $this;
    }
}
