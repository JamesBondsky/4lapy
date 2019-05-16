<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;

use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Bundle
 *
 * @package FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct
 *
 * ОбъектКаталога.ПолныйТовар.CЭтимТоваромПокупают
 */
class Bundle
{
    /**
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct>")
     * @Serializer\SerializedName("goods")
     * @var ShortProduct[]
     */
    protected $goods = [];

    /**
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Price")
     * @Serializer\SerializedName("totalPrice")
     * @var Price
     */
    protected $totalPrice;

    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("bonusAmount")
     * @var int
     */
    protected $bonusAmount = 0;


    /**
     * @return ShortProduct[]
     */
    public function getGoods(): array
    {
        return $this->goods;
    }

    /**
     * @param ShortProduct[] $goods
     * @return Bundle
     */
    public function setGoods(array $goods): Bundle
    {
        $this->goods = $goods;
        return $this;
    }

    /**
     * @return Price
     */
    public function getTotalPrice(): Price
    {
        return $this->totalPrice;
    }

    /**
     * @param Price $totalPrice
     * @return Bundle
     */
    public function setTotalPrice(Price $totalPrice): Bundle
    {
        $this->totalPrice = $totalPrice;
        return $this;
    }

    /**
     * @return int
     */
    public function getBonusAmount(): int
    {
        return $this->bonusAmount;
    }

    /**
     * @param int $bonusAmount
     * @return Bundle
     */
    public function setBonusAmount(int $bonusAmount): Bundle
    {
        $this->bonusAmount = $bonusAmount;
        return $this;
    }
}
