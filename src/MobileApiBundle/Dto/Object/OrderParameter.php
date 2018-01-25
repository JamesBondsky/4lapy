<?php


namespace FourPaws\MobileApiBundle\Dto\Object;

use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use JMS\Serializer\Annotation as Serializer;

/**
 * ОбъектПараметрЗаказа
 * Class OrderParameter
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class OrderParameter
{
    /**
     * Массив объектов ОбъектКорзины.Товар
     * (в случае, когда ОбъектПараметрЗаказа присылается с сервера, например, при получении корзины)
     * @Serializer\Groups("response")
     * @Serializer\SerializedName("goods")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Basket\Product>")
     * @var Product[]
     */
    protected $products = [];

    /**
     * Массив объектов ОбъектКоличествоТовара
     * (в случае, когда ОбъектПараметрЗаказа отправляется на сервер в качестве параметра, например, при оформлении заказа)
     * @Serializer\Groups("request")
     * @Serializer\SerializedName("goods")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\ProductQuantity>")
     * @var ProductQuantity[]
     */
    protected $productsQuantity = [];

    /**
     * Номер карты
     * @Serializer\SerializedName("card")
     * @Serializer\Type("string")
     * @var string
     */
    protected $card = '';

    /**
     * Выбранное пользователем количество бонусов для списания с карты в пользу текущего заказа
     * @Serializer\SerializedName("card_used")
     * @Serializer\Type("string")
     * @var string
     */
    protected $cardBonusUsed = '';

    /**
     * Тип доставки
     * @Serializer\SerializedName("delivery_type")
     * @Serializer\Type("string")
     * @var string
     */
    protected $deliveryType = '';

    /**
     * ОбъектАдресДоставки. Место доставки
     * @Serializer\SerializedName("delivery_place")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress")
     * @var DeliveryAddress
     */
    protected $deliveryPlace;

    /**
     * id времени доставки
     * @Serializer\SerializedName("delivery_range_id")
     * @Serializer\Type("string")
     * @var string
     */
    protected $deliveryRangeId = '';

    /**
     * Дата времени доставки
     * @Serializer\SerializedName("delivery_range_date")
     * @Serializer\Type("DateTime<d.m.Y>")
     * @var \DateTime
     */
    protected $deliveryRangeDate;

    /**
     * id магазина для забора товара
     * @todo По факту является адрессом
     * @Serializer\SerializedName("pickup_place")
     * @Serializer\Type("string")
     * @var string
     */
    protected $pickupPlace = '';

    /**
     * Комментарий к заказу
     * @Serializer\Type("string")
     * @Serializer\SerializedName("comment")
     * @var string
     */
    protected $comment = '';

    /**
     * Телефон, который указал пользователь при оформлении
     * @Serializer\Type("string")
     * @Serializer\SerializedName("user_phone")
     * @var string
     */
    protected $userPhone = '';

    /**
     * Предпочтительный способ оплаты. [cash|cashless|applepay]
     * @Serializer\SerializedName("payment_type")
     * @Serializer\Type("string")
     * @var string
     */
    protected $paymentType = '';

    /**
     * Дата, когда заказ доступен для самовывоза
     * @Serializer\Type("DateTime<d.m.Y>")
     * @Serializer\SerializedName("availability_date")
     * @var \DateTime
     */
    protected $availabilityDate;

    /**
     * push | tel
     * @Serializer\Type("string")
     * @Serializer\SerializedName("communication_type")
     * @var string
     */
    protected $communicationType = '';

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("promocode")
     * @var string
     */
    protected $promoCode = '';

    /**
     * Доп.телефон. Опция.
     * @Serializer\Type("string")
     * @Serializer\SerializedName("extra_phone")
     * @var string
     */
    protected $extraPhone = '';

    /**
     * @return Product[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @param Product[] $products
     *
     * @return OrderParameter
     */
    public function setProducts(array $products): OrderParameter
    {
        $this->products = $products;
        return $this;
    }

    /**
     * @return ProductQuantity[]
     */
    public function getProductsQuantity(): array
    {
        return $this->productsQuantity;
    }

    /**
     * @param ProductQuantity[] $productsQuantity
     *
     * @return OrderParameter
     */
    public function setProductsQuantity(array $productsQuantity): OrderParameter
    {
        $this->productsQuantity = $productsQuantity;
        return $this;
    }

    /**
     * @return string
     */
    public function getCard(): string
    {
        return $this->card;
    }

    /**
     * @param string $card
     *
     * @return OrderParameter
     */
    public function setCard(string $card): OrderParameter
    {
        $this->card = $card;
        return $this;
    }

    /**
     * @return string
     */
    public function getCardBonusUsed(): string
    {
        return $this->cardBonusUsed;
    }

    /**
     * @param string $cardBonusUsed
     *
     * @return OrderParameter
     */
    public function setCardBonusUsed(string $cardBonusUsed): OrderParameter
    {
        $this->cardBonusUsed = $cardBonusUsed;
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
     * @return OrderParameter
     */
    public function setDeliveryType(string $deliveryType): OrderParameter
    {
        $this->deliveryType = $deliveryType;
        return $this;
    }

    /**
     * @return DeliveryAddress
     */
    public function getDeliveryPlace(): DeliveryAddress
    {
        return $this->deliveryPlace;
    }

    /**
     * @param DeliveryAddress $deliveryPlace
     *
     * @return OrderParameter
     */
    public function setDeliveryPlace(DeliveryAddress $deliveryPlace): OrderParameter
    {
        $this->deliveryPlace = $deliveryPlace;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryRangeId(): string
    {
        return $this->deliveryRangeId;
    }

    /**
     * @param string $deliveryRangeId
     *
     * @return OrderParameter
     */
    public function setDeliveryRangeId(string $deliveryRangeId): OrderParameter
    {
        $this->deliveryRangeId = $deliveryRangeId;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDeliveryRangeDate(): \DateTime
    {
        return $this->deliveryRangeDate;
    }

    /**
     * @param \DateTime $deliveryRangeDate
     *
     * @return OrderParameter
     */
    public function setDeliveryRangeDate(\DateTime $deliveryRangeDate): OrderParameter
    {
        $this->deliveryRangeDate = $deliveryRangeDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getPickupPlace(): string
    {
        return $this->pickupPlace;
    }

    /**
     * @param string $pickupPlace
     *
     * @return OrderParameter
     */
    public function setPickupPlace(string $pickupPlace): OrderParameter
    {
        $this->pickupPlace = $pickupPlace;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return OrderParameter
     */
    public function setComment(string $comment): OrderParameter
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserPhone(): string
    {
        return $this->userPhone;
    }

    /**
     * @param string $userPhone
     *
     * @return OrderParameter
     */
    public function setUserPhone(string $userPhone): OrderParameter
    {
        $this->userPhone = $userPhone;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentType(): string
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     *
     * @return OrderParameter
     */
    public function setPaymentType(string $paymentType): OrderParameter
    {
        $this->paymentType = $paymentType;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getAvailabilityDate(): \DateTime
    {
        return $this->availabilityDate;
    }

    /**
     * @param \DateTime $availabilityDate
     *
     * @return OrderParameter
     */
    public function setAvailabilityDate(\DateTime $availabilityDate): OrderParameter
    {
        $this->availabilityDate = $availabilityDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommunicationType(): string
    {
        return $this->communicationType;
    }

    /**
     * @param string $communicationType
     *
     * @return OrderParameter
     */
    public function setCommunicationType(string $communicationType): OrderParameter
    {
        $this->communicationType = $communicationType;
        return $this;
    }

    /**
     * @return string
     */
    public function getPromoCode(): string
    {
        return $this->promoCode;
    }

    /**
     * @param string $promoCode
     *
     * @return OrderParameter
     */
    public function setPromoCode(string $promoCode): OrderParameter
    {
        $this->promoCode = $promoCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getExtraPhone(): string
    {
        return $this->extraPhone;
    }

    /**
     * @param string $extraPhone
     *
     * @return OrderParameter
     */
    public function setExtraPhone(string $extraPhone): OrderParameter
    {
        $this->extraPhone = $extraPhone;
        return $this;
    }
}
