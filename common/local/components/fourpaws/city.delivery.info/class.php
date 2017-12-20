<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;

class FourPawsCityDeliveryInfoComponent extends \CBitrixComponent implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);

        $this->setLogger(LoggerFactory::create('component'));
    }

    /**
     * @param $params
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        if (empty($params['LOCATION_CODE'])) {
            /** @var \FourPaws\UserBundle\Service\UserService $userService */
            $userService = Application::getInstance()
                ->getContainer()
                ->get(UserCitySelectInterface::class);
            $params['LOCATION_CODE'] = $userService->getSelectedCity()['CODE'];
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
            $this->log(LogLevel::ERROR, sprintf('Component execute error: %s', $e->getMessage()), $e->getTrace());
        }
    }

    /**
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @return $this
     *
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
            ->get(UserCitySelectInterface::class);

        $defaultCity = $locationService->getDefaultLocation();
        $currentCity = $userService->getSelectedCity();

        /** @var CalculationResult $defaultResult */
        $defaultResult = reset(
            $deliveryService->getByLocation(
                $defaultCity['CODE'],
                [
                    DeliveryService::INNER_DELIVERY_CODE,
                    DeliveryService::DPD_DELIVERY_CODE,
                ]
            )
        );

        if ($defaultCity['CODE'] === $currentCity['CODE']) {
            $currentResult = $defaultResult;
        } else {
            /** @var CalculationResult $currentResult */
            $currentResult = reset(
                $deliveryService->getByLocation(
                    $currentCity['CODE'],
                    [
                        DeliveryService::INNER_DELIVERY_CODE,
                        DeliveryService::DPD_DELIVERY_CODE,
                    ]
                )
            );
        }

        if (!$currentResult) {
            $this->abortResultCache();

            return $this;
        }

        $defaultFreeFrom = $defaultResult->getTmpData()['FREE_FROM'] ?? null;
        $currentFreeFrom = $currentResult->getTmpData()['FREE_FROM'] ?? null;

        $this->arResult = [
            'CURRENT' => [
                'CITY_CODE' => $currentCity['CODE'],
                'CITY_NAME' => $currentCity['NAME'],
                'PRICE'     => $currentResult->getPrice(),
                'FREE_FROM' => $currentFreeFrom,
            ],
            'DEFAULT' => [
                'CITY_CODE' => $defaultCity['CODE'],
                'CITY_NAME' => $defaultCity['NAME'],
                'PRICE'     => $defaultResult->getPrice(),
                'FREE_FROM' => $defaultFreeFrom,
            ],
        ];

        return $this;
    }

    /**
     * @param       $level
     * @param       $message
     * @param array $context
     */
    protected function log($level, $message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}
