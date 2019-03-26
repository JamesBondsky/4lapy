<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class UserCartCalcRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Assert\GreaterThanOrEqual(0)
     * @Serializer\SerializedName("bonusSub")
     * @Serializer\Type("float")
     * @var float
     */
    protected $bonusSubtractAmount = 0;

    /**
     * Тип доставки
     * @Serializer\SerializedName("deliveryType")
     * @Serializer\Type("string")
     * @Assert\Choice({"courier", "pickup"})
     * @var string
     */
    protected $deliveryType = '';

    /**
     * @Serializer\SerializedName("onlyAvailableGoods")
     * @Serializer\Type("bool")
     * @var bool
     */
    public $onlyAvailableGoods;

    /**
     * @Serializer\SerializedName("shopCode")
     * @Serializer\Type("string")
     * @var string
     */
    public $shopCode;

    /**
     * @return float
     */
    public function getBonusSubtractAmount(): float
    {
        return $this->bonusSubtractAmount;
    }

    /**
     * @param float $bonusSubtractAmount
     *
     * @return UserCartCalcRequest
     */
    public function setBonusSubtractAmount(float $bonusSubtractAmount): UserCartCalcRequest
    {
        $this->bonusSubtractAmount = $bonusSubtractAmount;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryType(): string
    {
        return $this->deliveryType;
    }

    /**
     * @param string $deliveryType
     *
     * @return UserCartCalcRequest
     */
    public function setDeliveryType(string $deliveryType): UserCartCalcRequest
    {
        $this->deliveryType = $deliveryType;
        return $this;
    }
}
