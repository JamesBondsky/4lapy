<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\Shares;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class BonusBuyTo
 *
 * @package FourPaws\SapBundle\Dto\In\Shares
 */
class BonusBuyTo
{
    /**
     * Содержит количество единиц подарка.
     *
     * - если значение поля – натуральное число N, скидка действует на N любых единиц подарка из группы единиц подарков;
     * - если значение поля «0», скидка действует на весь чек.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("FG_QUAN")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $quantity = '';

    /**
     * Содержит математический знак условия акции. Значение по умолчанию «–».
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("KOND_SIGN")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $sign = '-';

    /**
     * Содержит величину скидки в процентах. В зависимости от значения параметра FG_QUAN скидка действует на N единиц
     * подарка или на весь чек.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("KOND_PER")
     * @Serializer\Type("sap_bool")
     *
     * @var bool
     */
    protected $percent = false;

    /**
     * Группа данных о единице подарка
     *
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Shares\BonusBuyToItem>")
     * @Serializer\SerializedName("BONUS_ITEM")
     *
     * @var BonusBuyToItem[]|Collection
     */
    protected $bonusBuyTotems;

    /**
     * @return string
     */
    public function getQuantity(): string
    {
        return $this->quantity;
    }

    /**
     * @param string $quantity
     * @return BonusBuyTo
     */
    public function setQuantity(string $quantity): BonusBuyTo
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return string
     */
    public function getSign(): string
    {
        return $this->sign;
    }

    /**
     * @param string $sign
     * @return BonusBuyTo
     */
    public function setSign(string $sign): BonusBuyTo
    {
        $this->sign = $sign;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPercent(): bool
    {
        return $this->percent;
    }

    /**
     * @param bool $percent
     * @return BonusBuyTo
     */
    public function setPercent(bool $percent): BonusBuyTo
    {
        $this->percent = $percent;

        return $this;
    }

    /**
     * @return BonusBuyToItem[]|Collection
     */
    public function getBonusBuyTotems(): Collection
    {
        return $this->bonusBuyTotems;
    }

    /**
     * @param BonusBuyToItem[]|Collection $bonusBuyTotems
     *
     * @return BonusBuyTo
     */
    public function setBonusBuyTotems($bonusBuyTotems): BonusBuyTo
    {
        $this->bonusBuyTotems = $bonusBuyTotems;

        return $this;
    }
}
