<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\ConfirmPayment;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Order
 *
 * @package FourPaws\SapBundle\Dto\In
 */
class Order
{
    /**
     * Номер заказа SAP
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Order_SAP")
     * @Serializer\XmlAttribute()
     *
     * @var string
     */
    protected $sapOrderId = '';

    /**
     * Номер заказа Сайт
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Order_BITRIX")
     * @Serializer\XmlAttribute()
     *
     * @var string
     */
    protected $bitrixOrderId = 0;

    /**
     * Способ оплаты
     * Варианты значений:
     *   01 – Оплата наличными;
     *   02 – Оплата банковской картой курьеру;
     *   05 – Онлайн-оплата.
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Pay_Type")
     * @Serializer\XmlAttribute()
     *
     * @var string
     */
    protected $payType = '';

    /**
     * Название способа оплаты
     * Варианты значений:
     *   Оплата наличными;
     *   Оплата банковской картой курьеру;
     *   Онлайн-оплата.
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Pay_Type_Text")
     * @Serializer\XmlAttribute()
     *
     * @var string
     */
    protected $payTypeText = '';

    /**
     * Сумма заказа
     *
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Sum_Total")
     * @Serializer\XmlAttribute()
     *
     * @var float
     */
    protected $sumTotal = 0;

    /**
     * Сумма оплаты заказа
     * Содержит сумму заказа к оплате, которую нужно списать с банковской карты покупателя.
     * Если значения «Sum_Total» и «Sum_Payed» равны 0, Система не должна обрабатывать этот файл
     *
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Sum_Payed")
     * @Serializer\XmlAttribute()
     *
     * @var float
     */
    protected $sumPayed = 0;

    /**
     * Сумма возврата
     *
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Sum_Returned")
     * @Serializer\XmlAttribute()
     *
     * @var float
     */
    protected $sumReturned = 0;

    /**
     * Остатки
     *
     * @Serializer\XmlList(inline=true, entry="Item")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\ConfirmPayment\Item>")
     *
     * @var Collection|Item[]
     */
    protected $items;

    /**
     * @return string
     */
    public function getSapOrderId(): string
    {
        return $this->sapOrderId;
    }

    /**
     * @param string $sapOrderId
     * @return $this
     */
    public function setSapOrderId(string $sapOrderId): Order
    {
        $this->sapOrderId = $sapOrderId;
        return $this;
    }

    /**
     * @return string
     */
    public function getBitrixOrderId(): string
    {
        return $this->bitrixOrderId;
    }

    /**
     * @param string $bitrixOrderId
     * @return Order
     */
    public function setBitrixOrderId(string $bitrixOrderId): Order
    {
        $this->bitrixOrderId = $bitrixOrderId;
        return $this;
    }

    /**
     * @return string
     */
    public function getPayType(): string
    {
        return $this->payType;
    }

    /**
     * @param string $payType
     * @return Order
     */
    public function setPayType(string $payType): Order
    {
        $this->payType = $payType;
        return $this;
    }

    /**
     * @return string
     */
    public function getPayTypeText(): string
    {
        return $this->payTypeText;
    }

    /**
     * @param string $payTypeText
     * @return Order
     */
    public function setPayTypeText(string $payTypeText): Order
    {
        $this->payTypeText = $payTypeText;
        return $this;
    }

    /**
     * @return float
     */
    public function getSumTotal(): float
    {
        return $this->sumTotal;
    }

    /**
     * @param float $sumTotal
     * @return Order
     */
    public function setSumTotal(float $sumTotal): Order
    {
        $this->sumTotal = $sumTotal;
        return $this;
    }

    /**
     * @return float
     */
    public function getSumPayed(): float
    {
        return $this->sumPayed;
    }

    /**
     * @return float
     */
    public function getSumReturned(): float
    {
        return $this->sumReturned;
    }

    /**
     * @param float $sumReturned
     * @return Order
     */
    public function setSumReturned(float $sumReturned): Order
    {
        $this->sumReturned = $sumReturned;
        return $this;
    }

    /**
     * @param float $sumPayed
     * @return Order
     */
    public function setSumPayed(float $sumPayed): Order
    {
        $this->sumPayed = $sumPayed;
        return $this;
    }

    /**
     * @return Collection|Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Collection|Item[] $items
     * @return Order
     */
    public function setItems(Collection $items): Order
    {
        $this->items = $items;
        return $this;
    }
}
