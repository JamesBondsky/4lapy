<?php

namespace FourPaws\SapBundle\Dto\In\Shares;

use JMS\Serializer\Annotation as Serializer;


/**
 * Class BonusBuyFromItem
 *
 * @package FourPaws\SapBundle\Dto\In\Shares
 */
class BonusBuyFromItem
{
    /**
     * Содержит уникальный идентификатор торгового предложения, 7-значный цифровой код.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("MAT_NR")
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $offerId = '';

    /**
     * Содержит количество товара, которое необходимо положить в корзину для выполнения условий акции. Значение поля
     * не учитывается, если задано значение параметра FLDVAL.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("MAT_QUAN")
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $quantity = '';

    /**
     * Содержит единицу измерения в соответствии с ISO. Значение по умолчанию «PCE».
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("MAT_UNIT_ISO")
     * @Serializer\Type("string")
     *
     * @var bool
     */
    protected $anyCombination = 'PCE';

    /**
     * @return int
     */
    public function getOfferId(): int
    {
        return $this->offerId;
    }

    /**
     * @param int $offerId
     * @return BonusBuyFromItem
     */
    public function setOfferId(int $offerId): BonusBuyFromItem
    {
        $this->offerId = $offerId;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @return BonusBuyFromItem
     */
    public function setQuantity(int $quantity): BonusBuyFromItem
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAnyCombination(): bool
    {
        return $this->anyCombination;
    }

    /**
     * @param bool $anyCombination
     *
     * @return BonusBuyFromItem
     */
    public function setAnyCombination(bool $anyCombination): BonusBuyFromItem
    {
        $this->anyCombination = $anyCombination;

        return $this;
    }
}
