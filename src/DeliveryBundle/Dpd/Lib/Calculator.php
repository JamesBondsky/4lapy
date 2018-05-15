<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Dpd\Lib;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Loader;
use Ipolh\DPD\API\User;
use Ipolh\DPD\Shipment;
use Psr\Log\LoggerAwareInterface;

if (!Loader::includeModule('ipol.dpd')) {
    class Calculator
    {
    }

    return;
}

class Calculator extends \Ipolh\DPD\Calculator implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public function __construct(Shipment $shipment, User $api = null)
    {
        parent::__construct($shipment, $api);
        $this->withLogName(str_replace('\\', '_', static::class));
    }

    /**
     * Расчитывает стоимость доставки
     *
     * @return array Оптимальный тариф доставки
     */
    public function calculate($currency = false)
    {
        if (!$this->getShipment()->isPossibileDelivery()) {
            return false;
        }

        $parms = $this->getServiceParmsArray();

        $tariffs = $this->getListFromService($parms);
        if (empty($tariffs)) {
            $this->log()->error('failed to calculate DPD delivery price', [
                'parameters' => $parms,
                'items' => $this->getShipment()->getItems(),
            ]);

            return false;
        }

        $tariff = $this->getActualTariff($tariffs);
        $tariff = $this->adjustTariffWithCommission($tariff);
        $tariff = $this->convertCurrency($tariff, $currency);

        return self::$lastResult = $tariff;
    }
}
