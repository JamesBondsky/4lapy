<?php

namespace FourPaws\SapBundle\Dto\In\ConfirmPayment;

use Doctrine\Common\Collections\Collection;

/**
 * Class Order
 * @package FourPaws\SapBundle\Dto\In
 */
class Order
{
    /**
     * Номер заказа SAP
     *
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Order_SAP")
     * @Serializer\XmlAttribute()
     *
     * @var int
     */
    protected $sapOrderId = 0;

    /**
     * Номер заказа Сайт
     *
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Order_BITRIX")
     * @Serializer\XmlAttribute()
     *
     * @var int
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
     * Остатки
     *
     * @Serializer\XmlList(inline=true, entry="Item")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\ConfirmPayment\Item>")
     *
     * @var Collection|Item[]
     */
    protected $items;

    /**
     * @return int
     */
    public function getSapOrderId(): int
    {
        return $this->sapOrderId;
    }

    /**
     * @param int $sapOrderId
     * @return $this
     */
    public function setSapOrderId(int $sapOrderId): Order
    {
        $this->sapOrderId = $sapOrderId;
        return $this;
    }

    /**
     * @return int
     */
    public function getBitrixOrderId(): int
    {
        return $this->bitrixOrderId;
    }

    /**
     * @param int $bitrixOrderId
     * @return Order
     */
    public function setBitrixOrderId(int $bitrixOrderId): Order
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
