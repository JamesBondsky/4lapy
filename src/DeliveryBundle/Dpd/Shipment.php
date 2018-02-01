<?php

namespace FourPaws\DeliveryBundle\Dpd;

use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Loader;
use Ipolh\DPD\DB\Terminal\Table;
use WebArch\BitrixCache\BitrixCache;

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

        return count($this->getDpdTerminals($isPaymentOnDelivery)) > 0;
    }

    public function getDpdTerminals($isPaymentOnDelivery = null)
    {
        $locationId = $this->locationTo['ID'];
        $getTerminals = function () use ($locationId) {
            return Table::getList(
                [
                    'select' => ['*'],
                    'filter' => [
                        'LOCATION_ID' => $locationId,
                    ],
                ]
            )->fetch();
        };

        $terminals = (new BitrixCache())
            ->withId(__METHOD__ . $locationId)
            ->resultOf($getTerminals);
        $orderPrice = $this->getPrice();
        $isPaymentOnDelivery = null === $isPaymentOnDelivery ? $this->isPaymentOnDelivery() : $isPaymentOnDelivery;
        if ($isPaymentOnDelivery) {
            $terminals = array_filter(
                $terminals,
                function ($item) use ($orderPrice) {
                    return ($item['NPP_AVAILABLE'] === 'Y') && ($item['NPP_AMOUNT'] >= $orderPrice);
                }
            );
        }

        return $terminals;
    }
}
