<?php


namespace FourPaws\MobileApiBundle\Dto\Object;

use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

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
     *
     * @Serializer\SerializedName("goods")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Basket\Product>")
     * @var Product[]
     */
    protected $products = [];

    /**
     * Подарки
     *
     * @Serializer\SerializedName("gifts")
     */
    public $gifts = [];

    /**
     * Полное имя покупателя
     * @Serializer\Type("string")
     * @Serializer\SerializedName("name")
     * @var string
     */
    protected $name = '';

    /**
     * Телефон, который указал пользователь при оформлении
     * @Serializer\Type("string")
     * @Serializer\SerializedName("phone")
     * @var string
     */
    protected $phone = '';

    /**
     * Email
     * @Serializer\Type("string")
     * @Serializer\SerializedName("email")
     * @var string
     */
    protected $email = '';

    /**
     * Доп.телефон. Опция.
     * @Serializer\Type("string")
     * @Serializer\SerializedName("altPhone")
     * @var string
     */
    protected $altPhone = '';

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("communicationWay")
     * @Assert\Choice({"01", "02"})
     * @var string
     */
    protected $communicationWay = '';

    /**
     * Тип доставки
     * @Serializer\SerializedName("deliveryType")
     * @Serializer\Type("string")
     * @Assert\Choice({"courier", "pickup", "dostavista", "dobrolap", "express"})
     * @var string
     */
    protected $deliveryType = '';

    /**
     * Нужно ли делить заказ на два
     * @Serializer\Type("int")
     * @Serializer\SerializedName("split")
     * @var int
     */
    protected $split = 0;

    /**
     * ID сохраненного адреса доставки
     * @Serializer\Type("int")
     * @Serializer\SerializedName("addressId")
     * @var int
     */
    protected $addressId;

    /**
     * Строка с текстом адреса доставки или самовывоза (только для вывода)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("addressText")
     * @var string
     */
    protected $addressText;

    /**
     * Строка с текстом вида "6 товаров (10кг) на сумму 1000р" (только для вывода)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("goodsInfo")
     * @var string
     */
    protected $goodsInfo;

    /**
     * @Assert\Valid()
     * @Serializer\SerializedName("city")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\City")
     * @var null|City
     */
    protected $city;

    /**
     * Street (manually typed when user is not authorized)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("street")
     * @var string
     */
    protected $street = '';

    /**
     * House (manually typed when user is not authorized)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("house")
     * @var string
     */
    protected $house = '';

    /**
     * Building (manually typed when user is not authorized)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("building")
     * @var string
     */
    protected $building = '';

    /**
     * Porch (manually typed when user is not authorized)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("porch")
     * @var string
     */
    protected $porch = '';

    /**
     * Floor (manually typed when user is not authorized)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("floor")
     * @var string
     */
    protected $floor = '';

    /**
     * Apartment (manually typed when user is not authorized)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("apartment")
     * @var string
     */
    protected $apartment = '';

    /**
     * Индекс даты доставки
     * @Serializer\SerializedName("deliveryDate")
     * @Serializer\Type("int")
     * @var int
     */
    protected $deliveryDate;

    /**
     * Индекс временного интервала доставки
     * @Serializer\SerializedName("deliveryInterval")
     * @Serializer\Type("int")
     * @var int
     */
    protected $deliveryInterval;

    /**
     * Дата и время доставки в текстовом виде (только для вывода)
     * @Serializer\SerializedName("deliveryDateTimeText")
     * @Serializer\Type("string")
     * @var string
     */
    protected $deliveryDateTimeText;

    /**
     * Comment
     * @Serializer\Type("string")
     * @Serializer\SerializedName("comment")
     * @var string
     */
    protected $comment = '';

    /**
     * @Serializer\SerializedName("deliveryPlaceCode")
     * @Serializer\Type("string")
     * @var string
     */
    protected $deliveryPlaceCode;

    /**
     * Номер карты
     * @Serializer\SerializedName("discountCardNumber")
     * @Serializer\Type("string")
     * @var string
     */
    protected $discountCardNumber = '';

    /**
     * ID платежной системы. [1 - наличными, 3 - оплата картой на сайте]
     * @Serializer\SerializedName("paymentId")
     * @Serializer\Type("int")
     * @var string
     */
    protected $paymentId = '';

    /**
     * Комментарий ко второму заказу
     * @Serializer\Type("string")
     * @Serializer\SerializedName("secondComment")
     * @var string
     */
    protected $secondComment = '';

    /**
     * Индекс даты доставки второго заказа
     * @Serializer\Type("int")
     * @Serializer\SerializedName("secondDeliveryDate")
     * @var int
     */
    protected $secondDeliveryDate;

    /**
     * Индекс интервала доставки второго заказа
     * @Serializer\Type("int")
     * @Serializer\SerializedName("secondDeliveryInterval")
     * @var int
     */
    protected $secondDeliveryInterval;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("promocode")
     * @var string
     */
    protected $promoCode = '';

    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("isSubscribe")
     * @var int
     */
    protected $isSubscribe = false;

    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("subscribeFrequency")
     * @var int
     */
    protected $subscribeFrequency;

    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("payWithBonus")
     * @var int
     */
    protected $payWithBonus = false;

    /**
     * Штрих-код приюта для доставки Добролап
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("shelter")
     * @var string
     */
    protected $shelter = '';

    /**
     * Текст для страницы спасибо
     *
     * @Serializer\Type("array")
     * @Serializer\SerializedName("text")
     * @var array
     */
    protected $text = [];

    /**
     * Флаг отвечает за активность акции добролап
     *
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("activeDobrolap")
     * @var bool
     */
    protected $activeDobrolap = false;

    /**
     * Иконки
     *
     * @Serializer\Type("array")
     * @Serializer\SerializedName("icons")
     * @var array
     */
    protected $icons = [];

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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return OrderParameter
     */
    public function setName(string $name): OrderParameter
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDiscountCardNumber(): string
    {
        return $this->discountCardNumber;
    }

    /**
     * @param string $discountCardNumber
     *
     * @return OrderParameter
     */
    public function setDiscountCardNumber(string $discountCardNumber): OrderParameter
    {
        $this->discountCardNumber = $discountCardNumber;
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
     * @return int
     */
    public function getDeliveryInterval(): int
    {
        return $this->deliveryInterval;
    }

    /**
     * @return int
     */
    public function getDeliveryDate(): int
    {
        return $this->deliveryDate;
    }

    /**
     * @param int $deliveryDate
     *
     * @return OrderParameter
     */
    public function setDeliveryDate(int $deliveryDate): OrderParameter
    {
        $this->deliveryDate = $deliveryDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryDateTimeText()
    {
        return $this->deliveryDateTimeText;
    }

    /**
     * @param string $deliveryDateTimeText
     * @return OrderParameter
     */
    public function setDeliveryDateTimeText(string $deliveryDateTimeText): OrderParameter
    {
        $this->deliveryDateTimeText = $deliveryDateTimeText;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return OrderParameter
     */
    public function setPhone(string $phone): OrderParameter
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getAltPhone(): string
    {
        return $this->altPhone;
    }

    /**
     * @param string $altPhone
     *
     * @return OrderParameter
     */
    public function setAltPhone(string $altPhone): OrderParameter
    {
        $this->altPhone = $altPhone;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommunicationWay(): string
    {
        return $this->communicationWay;
    }

    /**
     * @param string $communicationWay
     *
     * @return OrderParameter
     */
    public function setCommunicationWay(string $communicationWay): OrderParameter
    {
        $this->communicationWay = $communicationWay;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return OrderParameter
     */
    public function setEmail(string $email): OrderParameter
    {
        $this->email = $email;
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
     * @return OrderParameter
     */
    public function setComment(string $comment): OrderParameter
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return null|City
     */
    public function getCity(): ?City
    {
        return $this->city;
    }

    /**
     * @param null|City $city
     * @return OrderParameter
     */
    public function setCity(?City $city): OrderParameter
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     * @return OrderParameter
     */
    public function setStreet(string $street): OrderParameter
    {
        $this->street = $street;
        return $this;
    }

    /**
     * @return string
     */
    public function getHouse(): string
    {
        return $this->house;
    }

    /**
     * @param string $house
     * @return OrderParameter
     */
    public function setHouse(string $house): OrderParameter
    {
        $this->house = $house;
        return $this;
    }

    /**
     * @return string
     */
    public function getBuilding(): string
    {
        return $this->building;
    }

    /**
     * @param string $building
     * @return OrderParameter
     */
    public function setBuilding(string $building): OrderParameter
    {
        $this->building = $building;
        return $this;
    }

    /**
     * @return string
     */
    public function getPorch(): string
    {
        return $this->porch;
    }

    /**
     * @param string $porch
     * @return OrderParameter
     */
    public function setPorch(string $porch): OrderParameter
    {
        $this->porch = $porch;
        return $this;
    }

    /**
     * @return string
     */
    public function getFloor(): string
    {
        return $this->floor;
    }

    /**
     * @param string $floor
     * @return OrderParameter
     */
    public function setFloor(string $floor): OrderParameter
    {
        $this->floor = $floor;
        return $this;
    }

    /**
     * @return string
     */
    public function getApartment(): string
    {
        return $this->apartment;
    }

    /**
     * @param string $apartment
     * @return $this
     */
    public function setApartment(string $apartment): OrderParameter
    {
        $this->apartment = $apartment;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryPlaceCode()
    {
        return $this->deliveryPlaceCode;
    }

    /**
     * @param string $deliveryPlaceCode
     * @return OrderParameter
     */
    public function setDeliveryPlaceCode(string $deliveryPlaceCode): OrderParameter
    {
        $this->deliveryPlaceCode = $deliveryPlaceCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressText(): string
    {
        return $this->addressText;
    }

    /**
     * @param string $addressText
     * @return OrderParameter
     */
    public function setAddressText(string $addressText): OrderParameter
    {
        $this->addressText = $addressText;
        return $this;
    }

    /**
     * @return string
     */
    public function getGoodsInfo(): string
    {
        return $this->goodsInfo;
    }

    /**
     * @param string $goodsInfo
     * @return OrderParameter
     */
    public function setGoodsInfo(string $goodsInfo): OrderParameter
    {
        $this->goodsInfo = $goodsInfo;
        return $this;
    }

    /**
     * @param bool $subscribe
     * @return OrderParameter
     */
    public function setSubscribe(int $subscribe): OrderParameter
    {
        $this->isSubscribe = $subscribe;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSubscribe(): int
    {
        return $this->isSubscribe;
    }

    /**
     * @param bool $payWithBonus
     * @return OrderParameter
     */
    public function setPayWithBonus(int $payWithBonus): OrderParameter
    {
        $this->payWithBonus = $payWithBonus;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPayWithBonus(): int
    {
        return $this->payWithBonus;
    }

    /**
     * @param int $subscribeFrequency
     * @return OrderParameter
     */
    public function setSubscribeFrequency(int $subscribeFrequency): OrderParameter
    {
        $this->subscribeFrequency = $subscribeFrequency;
        return $this;
    }

    /**
     * @return int
     */
    public function getSubscribeFrequency(): int
    {
        return $this->subscribeFrequency;
    }

    /**
     * @return string
     */
    public function getShelter(): string
    {
        return $this->shelter;
    }

    /**
     * @param string $shelter
     *
     * @return OrderParameter
     */
    public function setShelter(string $shelter): OrderParameter
    {
        $this->shelter = $shelter;
        return $this;
    }

    /**
     * @return array
     */
    public function getText(): array
    {
        return $this->text;
    }

    /**
     * @param array $text
     * @return OrderParameter
     */
    public function setText(array $text): OrderParameter
    {
        $this->text = $text;
        return $this;
    }

    public function setActiveDobrolap(bool $flag): OrderParameter
    {
        $this->activeDobrolap = $flag;
        return $this;
    }

    public function getActiveDobrolap(): bool
    {
        return $this->activeDobrolap;
    }

    /**
     * @return array
     */
    public function getIcons(): array
    {
        return $this->icons;
    }

    /**
     * @param array $icons
     * @return OrderParameter
     */
    public function setIcons(array $icons): OrderParameter
    {
        $this->icons = $icons;
        return $this;
    }
}
