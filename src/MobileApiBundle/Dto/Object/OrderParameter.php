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
     * @Assert\Choice({"courier", "pickup"})
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
     * Дата доставки в текстовом виде (только для вывода)
     * @Serializer\SerializedName("deliveryDateText")
     * @Serializer\Type("string")
     * @var string
     */
    protected $deliveryDateText;

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
     * @Serializer\Type("string")
     * @Serializer\SerializedName("secondDeliveryDate")
     * @var string
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
    public function getDeliveryDateText()
    {
        return $this->deliveryDateText;
    }

    /**
     * @param string $deliveryDateText
     * @return OrderParameter
     */
    public function setDeliveryDateText(string $deliveryDateText): OrderParameter
    {
        $this->deliveryDateText = $deliveryDateText;
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
}
