<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\ConfirmPayment;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Debit
 *
 * @package FourPaws\SapBundle\Dto\In
 */
class Debit
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
     *
     * @return $this
     */
    public function setSapOrderId(int $sapOrderId): Debit
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
     *
     * @return Debit
     */
    public function setBitrixOrderId(int $bitrixOrderId): Debit
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
     *
     * @return Debit
     */
    public function setPayType(string $payType): Debit
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
     *
     * @return Debit
     */
    public function setPayTypeText(string $payTypeText): Debit
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
     *
     * @return Debit
     */
    public function setSumTotal(float $sumTotal): Debit
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
     *
     * @return Debit
     */
    public function setSumPayed(float $sumPayed): Debit
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
     *
     * @return Debit
     */
    public function setItems(Collection $items): Debit
    {
        $this->items = $items;
        return $this;
    }
}
