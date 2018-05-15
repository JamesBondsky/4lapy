<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Dpd;

use Bitrix\Main\Loader;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Dpd\Lib\Calculator;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use Ipolh\DPD\API\User;

if (!Loader::includeModule('ipol.dpd')) {
    class Shipment
    {
    }

    return;
}

class Shipment extends \Ipolh\DPD\Shipment
{
    protected $locationTo;

    protected $locationFrom;

    /**
     * Устанавливает местоположение отправителя
     *
     * @param array|string $locationCode код местоположения
     *
     * @return $this
     */
    public function setSender($locationCode)
    {
        $this->locationFrom = \is_array($locationCode)
            ? $locationCode
            : LocationTable::getByLocationCode($locationCode);

        return $this;
    }

    /**
     * Устанавливает местоположение получателя
     *
     * @param array|string $locationCode код местоположения
     *
     * @return $this
     */
    public function setReceiver($locationCode)
    {
        $this->locationTo = \is_array($locationCode)
            ? $locationCode
            : LocationTable::getByLocationCode($locationCode);

        return $this;
    }

    /**
     * Проверяет возможность осуществления в терминал доставки
     *
     * @return  bool
     */
    public function isPossibileSelfDelivery($isPaymentOnDelivery = null)
    {
        if (!$this->isPossibileDelivery()) {
            return false;
        }

        return $this->getDpdTerminals($isPaymentOnDelivery)->count() > 0;
    }

    public function getDpdTerminals($isPaymentOnDelivery = null): StoreCollection
    {
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        $isPaymentOnDelivery = null === $isPaymentOnDelivery ? $this->isPaymentOnDelivery() : $isPaymentOnDelivery;

        return $deliveryService->getDpdTerminalsByLocation(
            $this->locationTo['CODE'],
            $isPaymentOnDelivery,
            $this->getPrice()
        );
    }

    /**
     * Возвращает объем отправки, м3
     *
     * @return float
     */
    public function getVolume()
    {
        $volume = $this->dimensions['WIDTH'] * $this->dimensions['HEIGHT'] * $this->dimensions['LENGTH'];

        return round($volume / 1000000, 3) ?: 0.001;
    }

    public function isPaymentOnDelivery()
    {
        /**
         * У пунктов самовывоза DPD должна быть возможность оплаты на месте
         */
        return true;
    }

    public function calculator()
    {
        return new Calculator($this, $this->api);
    }
}
