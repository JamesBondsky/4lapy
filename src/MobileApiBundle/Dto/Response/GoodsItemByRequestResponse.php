<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Banner;
use FourPaws\MobileApiBundle\Dto\Object\Order;
use JMS\Serializer\Annotation as Serializer;

class GoodsItemByRequestResponse
{

    /**
     * ID
     *
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     */
    protected $id;

    /**
     * ID
     *
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("isByRequest")
     */
    protected $isByRequest;


    /**
     * Наличие (для товара под заказ)
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("availability")
     * @Serializer\SkipWhenEmpty()
     * @var string
     */
    protected $availability = 'Нет в налчии';

    /**
     * Информация по доставке (для товара под заказ)
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("delivery")
     * @Serializer\SkipWhenEmpty()
     * @var string
     */
    protected $delivery;

    /**
     * Информация по самовывозу (для товара под заказ)
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("pickup")
     * @Serializer\SkipWhenEmpty()
     * @var string
     */
    protected $pickup;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return GoodsItemByRequestResponse
     */
    public function setId(int $id): GoodsItemByRequestResponse
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsByRequest(): bool
    {
        return $this->isByRequest;
    }

    /**
     * @param bool $isByRequest
     *
     * @return GoodsItemByRequestResponse
     */
    public function setIsByRequest(bool $isByRequest): GoodsItemByRequestResponse
    {
        $this->isByRequest = $isByRequest;
        return $this;
    }

    /**
     * @return string
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * @param string $availability
     * @return GoodsItemByRequestResponse
     */
    public function setAvailability(string $availability): GoodsItemByRequestResponse
    {
        $this->availability = $availability;
        return $this;
    }

    /**
     * @return string
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * @param string $delivery
     * @return GoodsItemByRequestResponse
     */
    public function setDelivery(string $delivery): GoodsItemByRequestResponse
    {
        $this->delivery = $delivery;
        return $this;
    }

    /**
     * @return string
     */
    public function getPickup()
    {
        return $this->pickup;
    }

    /**
     * @param string $pickup
     * @return GoodsItemByRequestResponse
     */
    public function setPickup(string $pickup): GoodsItemByRequestResponse
    {
        $this->pickup = $pickup;
        return $this;
    }
}
