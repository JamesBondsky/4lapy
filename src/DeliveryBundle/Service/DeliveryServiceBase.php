<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;
use FourPaws\App\Application;
use FourPaws\Location\Exception\CityNotFoundException;
use FourPaws\Location\LocationService;

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

    public function __construct($initParams)
    {
        $this->locationService = Application::getInstance()->getContainer()->get('location.service');

        parent::__construct($initParams);
    }

    /**
     * {@inheritdoc}
     */
    public function getDeliveryZone(string $locationCode = null): string
    {
        /* @todo определение зоны по местоположению */

        return self::ZONE_4;
    }

    public function getAllAvailableZones(): array
    {
        if ($groups = $this->locationService->getLocationGoups(true)) {
            return array_column($groups, ['CODE']);
        }

        return [];
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
