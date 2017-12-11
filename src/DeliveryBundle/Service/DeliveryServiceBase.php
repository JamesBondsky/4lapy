<?php

namespace FourPaws\DeliveryBundle\Service;

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

    /**
     * {@inheritdoc}
     */
    public function getDeliveryZone(string $locationCode = null): string
    {
        if (!$locationCode) {
            $locationCode = $this->userService->getSelectedCity()['CODE'];
        }

        $allZones = $this->getAllAvailableZones();
        foreach ($allZones as $code => $data) {
            if (in_array($locationCode, $data['LOCATIONS'])) {
                return $code;
            }
        }

        return self::ZONE_4;
    }

    public function getAllAvailableZones(): array
    {
        $this->locationService->getLocationGroups(true);
    }

    public function isCompatible(Shipment $shipment)
    {
        if (!in_array($this->getDeliveryZone(), $this->availableZones)) {
            return false;
        }

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
}
