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
     * @param mixed $locationId ID местоположения
     *
     * @return self
     */
    public function setSender($locationId)
    {
        $this->locationFrom = \is_array($locationId)
            ? $locationId
            : LocationTable::getByLocationId($locationId);

        return $this;
    }

    /**
     * Устанавливает местоположение получателя
     *
     * @param mixed $locationId код местоположения
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

        $isPaymentOnDelivery = null === $isPaymentOnDelivery ? $this->isPaymentOnDelivery() : $isPaymentOnDelivery;
        $locationId = $this->locationTo['ID'];
        $getPickupPointsCount = function () use ($isPaymentOnDelivery, $locationId) {
            return Table::getList(
                [
                    'select' => ['CNT'],

                    'filter' => array_filter(
                        array_merge(
                            [
                                'LOCATION_ID' => $locationId,
                            ],

                            $isPaymentOnDelivery
                                ? ['NPP_AVAILABLE' => 'Y', '>=NPP_AMOUNT' => $this->getPrice()]
                                : []
                        )
                    ),

                    'runtime' => [
                        new ExpressionField('CNT', 'COUNT(*)'),
                    ],
                ]
            )->fetch();
        };

        $result = (new BitrixCache())
            ->withId(__METHOD__ . $locationId)
            ->resultOf($getPickupPointsCount);

        return $result['CNT'] > 0;
    }
}
