<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;

use FourPaws\Decorators\FullHrefDecorator;
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
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct\BundleItem>")
     * @Serializer\SerializedName("goods")
     * @var BundleItem[]
     */
    protected $bundleItems = [];

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
     * @return BundleItem[]
     */
    public function getBundleItems(): array
    {
        return $this->bundleItems;
    }

    /**
     * @param BundleItem[] $bundleItems
     * @return Bundle
     */
    public function setBundleItems(array $bundleItems): Bundle
    {
        $this->bundleItems = $bundleItems;
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
     * @param int $totalPrice
     * @return Bundle
     */
    public function setBonusAmount(int $bonusAmount): Bundle
    {
        $this->bonusAmount = $bonusAmount;
        return $this;
    }
}
