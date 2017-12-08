<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;

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
     * {@inheritdoc}
     */
    public function getDeliveryZone(string $locationCode = null)
    {
        /* @todo определение зоны по местоположению */

        return self::ZONE_4;
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
