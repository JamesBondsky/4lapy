<?php

namespace FourPaws\External\Manzana\Dto;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class SoftChequeResponse
 *
 * @package FourPaws\External\Manzana\Dto
 *
 * @Serializer\XmlRoot("ChequeResponse")
 */
class SoftChequeResponse
{
    /**
     * Дата и время операции (в системе)
     * Используется локальное время системы, обрабатывающей мягкий чек (московское).
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("manzana_date_time_short")
     * @Serializer\SerializedName("Proccessed")
     *
     * @var DateTimeImmutable
     */
    protected $processed;

    /**
     * Код возврата
     * В случае ошибки отличен от нуля. Т.е. значение поля равное «ноль» означает, что ошибки не произошло.
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ReturnCode")
     *
     * @var int
     */
    protected $returnCode = 0;

    /**
     * Текстовое описание ошибки
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Message")
     *
     * @var string
     */
    protected $message = '';

    /**
     * Баланс карты, деньги
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("CardBalance")
     *
     * @var float
     */
    protected $cardBalance = 0;

    /**
     * Активный баланс карты, деньги
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("CardActiveBalance")
     *
     * @var float
     */
    protected $cardActiveBalance = 0;

    /**
     * Сумма без скидки по чеку, деньги
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Summ")
     *
     * @var float
     */
    protected $summ = 0;

    /**
     * Скидка по чеку, %
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Discount")
     *
     * @var float
     */
    protected $discount = '';

    /**
     * Сумма со скидкой по чеку, деньги
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("SummDiscounted")
     *
     * @var float
     */
    protected $summDiscounted = '';

    /**
     * ?, деньги
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("CardSumm")
     *
     * @var float
     */
    protected $cardSumm = 0;

    /**
     * ?, %
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("CardDiscount")
     *
     * @var float
     */
    protected $cardDiscount = '';

    /**
     * ?, деньги
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("CardSummDiscounted")
     *
     * @var float
     */
    protected $cardSummDiscounted = '';

    /**
     * Сумма начисленного бонуса
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("ChargedBonus")
     *
     * @var float
     */
    protected $chargedBonus = '';

    /**
     * Сумма бонусов, доступная для оплаты
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("AvailablePayment")
     *
     * @var float
     */
    protected $availablePayment = 0;

    /**
     * @Serializer\XmlList(inline=false, entry="Coupon")
     * @Serializer\SerializedName("Coupons")
     * @Serializer\Type("ArrayCollection<FourPaws\External\Manzana\Dto\Coupon>")
     *
     * @var Collection|Coupon[]
     */
    protected $coupons;

    /**
     * @Serializer\XmlList(inline=true)
     * @Serializer\Type("ArrayCollection<FourPaws\External\Manzana\Dto\ChequePosition>")
     * @Serializer\SerializedName("Item")
     *
     * @var Collection|ChequePosition[]
     */
    protected $items;

    /**
     * @return bool
     */
    public function isErrorResponse(): bool
    {
        return $this->returnCode !== 0;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getProcessed(): DateTimeImmutable
    {
        return $this->processed;
    }

    /**
     * @return int
     */
    public function getReturnCode(): int
    {
        return (int)$this->returnCode;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return (string)$this->message;
    }

    /**
     * @return float
     */
    public function getCardBalance(): float
    {
        return (float)$this->cardBalance;
    }

    /**
     * @return float
     */
    public function getCardActiveBalance(): float
    {
        return (float)$this->cardActiveBalance;
    }

    /**
     * @return float
     */
    public function getSumm(): float
    {
        return (float)$this->summ;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return (float)$this->discount;
    }

    /**
     * @return float
     */
    public function getSummDiscounted(): float
    {
        return (float)$this->summDiscounted;
    }

    /**
     * @return float
     */
    public function getCardSumm(): float
    {
        return (float)$this->cardSumm;
    }

    /**
     * @return float
     */
    public function getCardDiscount(): float
    {
        return (float)$this->cardDiscount;
    }

    /**
     * @return float
     */
    public function getCardSummDiscounted(): float
    {
        return (float)$this->cardSummDiscounted;
    }

    /**
     * @return float
     */
    public function getChargedBonus(): float
    {
        return (float)$this->chargedBonus;
    }

    /**
     * @return float
     */
    public function getAvailablePayment(): float
    {
        return (float)$this->availablePayment;
    }

    /**
     * @param float $availablePayment
     * @return SoftChequeResponse
     */
    public function setAvailablePayment(float $availablePayment): SoftChequeResponse
    {
        $this->availablePayment = $availablePayment;
        return $this;
    }

    /**
     * @return Collection|ChequePosition[]
     */
    public function getItems(): Collection
    {
        if (!$this->items) {
            $this->items = new ArrayCollection();
        }
        
        return $this->items;
    }

    /**
     * @param $items
     *
     * @return $this
     */
    public function setItems($items): SoftChequeResponse
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @param string $coupon
     */
    public function addCoupon(string $coupon): void
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $coupon = (new Coupon())->setNumber($coupon);

        if (!$this->coupons) {
            $this->coupons = new ArrayCollection();
        }

        $this->coupons->add($coupon);
    }

    /**
     * @param ArrayCollection $coupons
     *
     * @return $this
     */
    public function setCoupons(ArrayCollection $coupons)
    {
        $this->coupons = $coupons;

        return $this;
    }

    /**
     * @return Collection|Coupon[]|null
     */
    public function getCoupons() : ?Collection
    {
        return $this->coupons;
    }
}
