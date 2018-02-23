<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Dpd;

use Bitrix\Main\Loader;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\StoreBundle\Collection\StoreCollection;

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
        $this->locationFrom = (\is_array($locationCode) && isset($locationCode['CODE']))
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
        $this->locationTo = (\is_array($locationCode) && isset($locationCode['CODE']))
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

    public function isPaymentOnDelivery()
    {
        /**
         * У пунктов самовывоза DPD должна быть возможность оплаты на месте
         */
        return true;
    }
}
