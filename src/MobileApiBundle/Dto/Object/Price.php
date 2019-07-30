<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Price
 *
 * @package FourPaws\MobileApiBundle\Dto\Object
 *
 * ОбъектЦена
 */
class Price
{
    /**
     * @var double
     * @Serializer\Type("float")
     * @Serializer\SerializedName("actual")
     */
    protected $actual = 0.0;

    /**
     * @todo if old > actual показывать пустую строку
     * @var double
     * @Serializer\Type("float")
     * @Serializer\SerializedName("old")
     */
    protected $old = 0.0;

    /**
     * @var double
     * @Serializer\Type("float")
     * @Serializer\SerializedName("courierPrice")
     */
    protected $courierPrice = 0.0;

    /**
     * Цена по подписке на доставку
     * @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("subscribe")
     */
    protected $subscribe = 0.0;

    /**
     * @return float
     */
    public function getActual(): float
    {
        return $this->actual;
    }

    /**
     * @param float $actual
     *
     * @return Price
     */
    public function setActual(float $actual): Price
    {
        $this->actual = $actual;
        return $this;
    }

    /**
     * @return float
     */
    public function getOld(): float
    {
        return $this->old;
    }

    /**
     * @param float $old
     *
     * @return Price
     */
    public function setOld(float $old): Price
    {
        if ($this->actual != $old) {
            $this->old = $old;
        }
        return $this;
    }

    /**
     * @param float $price
     * @return Price
     */
    public function setCourierPrice(float $price): Price
    {
        $this->courierPrice = $price;
        return $this;
    }

    /**
     * @param float $subscribe
     * @return Price
     */
    public function setSubscribe(float $subscribe): Price
    {
        $this->subscribe = $subscribe;
        return $this;
    }

    /**
     * @return float
     */
    public function getSubscribe(): float
    {
        return $this->subscribe;
    }
}
