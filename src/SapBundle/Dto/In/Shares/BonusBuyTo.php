<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\Shares;

use Bitrix\Iblock\ElementTable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class BonusBuyTo
 *
 * @package FourPaws\SapBundle\Dto\In\Shares
 */
class BonusBuyTo implements BonusBuyGroupInterface
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
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $percent = 0.0;

    /**
     * Группа данных о единице подарка
     *
     * @Serializer\XmlList(inline=true, entry="BONUS_ITEM")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Shares\BonusBuyToItem>")
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
     * @return float
     */
    public function getPercent(): float
    {
        return $this->percent;
    }

    /**
     * @param float $percent
     * @return BonusBuyTo
     */
    public function setPercent(float $percent): BonusBuyTo
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

    /**
     * Возвращает массив XML_ID, пришедших в импорте
     *
     * @return ArrayCollection
     */
    public function getProductXmlIds(): ArrayCollection
    {
        if (!empty($this->bonusBuyTotems) && $this->bonusBuyTotems->count() >= 1) {

            $result = $this->bonusBuyTotems->map(function (BonusBuyToItem $item) {
                return $item->getOfferId();
            });
            $result = $result->filter(
                function ($e) {
                    return (bool)$e;
                }
            );
        }
        return $result ?? new ArrayCollection();
    }

    /**
     * Возвращает массив ID предложений, существующих на сайте
     *
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\ArgumentException
     *
     * @return ArrayCollection
     */
    public function getProductIds(): ArrayCollection
    {
        if ($xmlIds = $this->getProductXmlIds()->toArray()) {
            $res = ElementTable::getList([
                'select' => ['ID'],
                'filter' => [
                    '=XML_ID' => $xmlIds,
                    '=IBLOCK.CODE' => IblockCode::OFFERS,
                    '=IBLOCK.TYPE.ID' => IblockType::CATALOG,
                ],
            ]);
            $result = [];
            while ($elem = $res->fetch()) {
                $result[] = $elem['ID'];
            }
            $result = array_filter($result);
            $result = new ArrayCollection($result);
        }
        return $result ?? new ArrayCollection();
    }
}
