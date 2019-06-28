<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 24.06.2019
 * Time: 13:23
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class PostOrderSubscribeParamsRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * Номер подписки на доставку
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     * @var int
     */
    protected $orderSubscribeId;

    /**
     * Id службы доставки
     * @Serializer\Type("int")
     * @Serializer\SerializedName("deliveryId")
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     * @var int
     */
    protected $deliveryId;

    /**
     * Адрес места доставки (код магазина, если самовывоз)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("deliveryPlace")
     * @Assert\NotBlank()
     * @var string
     */
    protected $deliveryPlace;

    /**
     * Время доставки
     * @Serializer\Type("string")
     * @Serializer\SerializedName("deliveryTime")
     * @Assert\NotBlank()
     * @var string
     */
    protected $deliveryTime;

    /**
     * Дата следующей доставки по подписке
     * @Serializer\Type("string")
     * @Serializer\SerializedName("deliveryPlace")
     * @Assert\NotBlank()
     * @var string
     */
    protected $deliveryDate;

    /**
     * Частота доставки
     * @Serializer\Type("int")
     * @Serializer\SerializedName("frequency")
     * @Assert\NotBlank()
     * @var int
     */
    protected $frequency;

    /**
     * Частота доставки
     * @Serializer\Type("int")
     * @Serializer\SerializedName("payWithBonus")
     * @Assert\NotBlank()
     * @var int
     */
    protected $payWithBonus;

    /**
     * Активность (при возобновлении)
     * @Serializer\Type("int")
     * @Serializer\SerializedName("active")
     * @Assert\NotBlank()
     * @var int
     */
    protected $active;

    /**
     * @return int
     */
    public function getOrderSubscribeId(): int
    {
        return $this->orderSubscribeId;
    }

    /**
     * @param int $orderSubscribeId
     * @return PostOrderSubscribeParamsRequest
     */
    public function setOrderSubscribeId(int $orderSubscribeId): PostOrderSubscribeParamsRequest
    {
        $this->orderSubscribeId = $orderSubscribeId;
        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryId(): int
    {
        return $this->deliveryId;
    }

    /**
     * @param int $deliveryId
     * @return PostOrderSubscribeParamsRequest
     */
    public function setDeliveryId(int $deliveryId): PostOrderSubscribeParamsRequest
    {
        $this->deliveryId = $deliveryId;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryPlace(): string
    {
        return $this->deliveryPlace;
    }

    /**
     * @param string $deliveryPlace
     * @return PostOrderSubscribeParamsRequest
     */
    public function setDeliveryPlace(string $deliveryPlace): PostOrderSubscribeParamsRequest
    {
        $this->deliveryPlace = $deliveryPlace;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryDate(): string
    {
        return $this->deliveryDate;
    }

    /**
     * @param string $deliveryDate
     * @return PostOrderSubscribeParamsRequest
     */
    public function setDeliveryDate(string $deliveryDate): PostOrderSubscribeParamsRequest
    {
        $this->deliveryDate = $deliveryDate;
        return $this;
    }

    /**
     * @param int $frequency
     * @return PostOrderSubscribeParamsRequest
     */
    public function setFrequency(int $frequency): PostOrderSubscribeParamsRequest
    {
        $this->frequency = $frequency;
        return $this;
    }

    /**
     * @return int
     */
    public function getFrequency(): int
    {
        return $this->frequency;
    }

    /**
     * @param string $deliveryTime
     * @return PostOrderSubscribeParamsRequest
     */
    public function setDeliveryTime(string $deliveryTime): PostOrderSubscribeParamsRequest
    {
        $this->deliveryTime = $deliveryTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryTime(): string
    {
        return $this->deliveryTime;
    }

    /**
     * @param int $payWithBonus
     * @return PostOrderSubscribeParamsRequest
     */
    public function setPayWithBonus(int $payWithBonus): PostOrderSubscribeParamsRequest
    {
        $this->payWithBonus = $payWithBonus;
        return $this;
    }

    /**
     * @return int
     */
    public function getPayWithBonus(): int
    {
        return $this->payWithBonus;
    }

    /**
     * @param int $active
     * @return PostOrderSubscribeParamsRequest
     */
    public function setActive(int $active): PostOrderSubscribeParamsRequest
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return int
     */
    public function getActive(): int
    {
        return $this->active;
    }

}