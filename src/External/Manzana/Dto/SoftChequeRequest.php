<?php

namespace FourPaws\External\Manzana\Dto;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class SoftChequeRequest
 *
 * @package FourPaws\External\Manzana\Dto
 *
 * @Serializer\XmlRoot("ChequeRequest")
 * @Serializer\XmlNamespace(uri="http://loyalty.manzanagroup.ru/loyalty.xsd")
 */
class SoftChequeRequest
{
    protected const ROOT_NAME = 'ChequeRequest';

    /**
     * УИД торгового предложения
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ChequeType")
     *
     * @var string
     */
    protected $chequeType = 'Soft';

    /**
     * Идентификатор запроса
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("RequestId")
     *
     * @var string
     */
    protected $requestId = '';

    /**
     * Дата и время совершения операции
     * Дата не может быть больше текущей даты системы Manzana Loyalty
     *
     * @Serializer\Type("DateTimeImmutable<'Y-m-d\TH:i:s'>")
     * @Serializer\SerializedName("DateTime")
     *
     * @var DateTimeImmutable
     */
    protected $datetime;

    /**
     * Код Партнера
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Organization")
     *
     * @var string
     */
    protected $organization = '';

    /**
     * Код Магазина
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("BusinessUnit")
     *
     * @var string
     */
    protected $businessUnit = '';

    /**
     * Код POS терминала
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("POS")
     *
     * @var string
     */
    protected $pos = '';

    /**
     * Номер карты
     *
     * @Serializer\Type("FourPaws\External\Manzana\Dto\Card")
     * @Serializer\SerializedName("Card")
     * @Serializer\Accessor(setter="setCard", getter="getCard")
     *
     * @var Card
     */
    protected $card;

    /**
     * Номер чека
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Number")
     *
     * @var string
     */
    protected $number = '';

    /**
     * Тип операции
     * Всегда значене sale для данного БП
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("OperationType")
     *
     * @var string
     */
    protected $operationType = 'Sale';

    /**
     * @Serializer\XmlList(inline=true)
     * @Serializer\Type("ArrayCollection<FourPaws\External\Manzana\Dto\ChequePosition>")
     * @Serializer\SerializedName("Item")
     *
     * @var Collection|ChequePosition[]
     */
    protected $items;

    /**
     * @Serializer\XmlList(inline=false, entry="Coupon")
     * @Serializer\SerializedName("Coupons")
     * @Serializer\Type("ArrayCollection<FourPaws\External\Manzana\Dto\Coupon>")
     *
     * @var Collection|Coupon[]
     */
    protected $coupons;

    /**
     * Сумма.
     *
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Summ")
     *
     * @var float
     */
    protected $summ = 0;

    /**
     * Скидка, %
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Discount")
     *
     * @var float
     */
    protected $discount = 0;

    /**
     * Сумма с учетом скидки
     *
     * @Serializer\Type("float")
     * @Serializer\SerializedName("SummDiscounted")
     *
     * @var float
     */
    protected $summDiscounted = 0;

    /**
     * Оплачено бонусами
     *
     * @Serializer\Type("float")
     * @Serializer\SerializedName("PaidByBonus")
     *
     * @var float
     */
    protected $paidByBonus = 0;

    /**
     * @param string $requestId
     *
     * @return $this
     */
    public function setRequestId(string $requestId): self
    {
        $this->requestId = $requestId;

        return $this;
    }

    /**
     * @param DateTimeImmutable $datetime
     *
     * @return $this
     */
    public function setDatetime(DateTimeImmutable $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * @param string $organization
     *
     * @return $this
     */
    public function setOrganization(string $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @param string $businessUnit
     *
     * @return $this
     */
    public function setBusinessUnit(string $businessUnit): self
    {
        $this->businessUnit = $businessUnit;

        return $this;
    }

    /**
     * @param string $pos
     *
     * @return $this
     */
    public function setPos(string $pos): self
    {
        $this->pos = $pos;

        return $this;
    }

    /**
     * @param string $cardNumber
     *
     * @return $this
     */
    public function setCardByNumber(string $cardNumber): self
    {
        $this->setCard((new Card())->setNumber($cardNumber));

        return $this;
    }

    /**
     * @param Card $card
     *
     * @return $this
     */
    public function setCard(Card $card): self
    {
        if ($card->getNumber()) {
            $this->card = $card;
        }

        return $this;
    }

    /**
     * @param string $number
     *
     * @return $this
     */
    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @param ChequePosition $position
     */
    public function addItem(ChequePosition $position): void
    {
        if (!$this->items) {
            $this->items = new ArrayCollection();
        }

        $this->items->add($position);
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
     * @param Collection|ChequePosition[] $items
     *
     * @return $this
     */
    public function setItems($items): self
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @param ArrayCollection $coupons
     *
     * @return $this
     */
    public function setCoupons(ArrayCollection $coupons): self
    {
        $this->coupons = $coupons;

        return $this;
    }

    /**
     * @param float $summ
     *
     * @return $this
     */
    public function setSumm(float $summ): self
    {
        $this->summ = $summ;

        return $this;
    }

    /**
     * @param float $discount
     *
     * @return $this
     */
    public function setDiscount(float $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * @param float $summDiscounted
     *
     * @return $this
     */
    public function setSummDiscounted(float $summDiscounted): self
    {
        $this->summDiscounted = $summDiscounted;

        return $this;
    }

    /**
     * @param float $paidByBonus
     *
     * @return $this
     */
    public function setPaidByBonus(float $paidByBonus): self
    {
        $this->paidByBonus = $paidByBonus;

        return $this;
    }

    /**
     * @return Collection|Coupon[]
     */
    public function getCoupons(): Collection
    {
        return $this->coupons;
    }

    /**
     * @return Collection|ChequePosition[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @return Card
     */
    public function getCard(): ?Card
    {
        return $this->card;
    }
}
