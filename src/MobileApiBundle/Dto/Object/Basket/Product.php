<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Basket;

use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use FourPaws\MobileApiBundle\Dto\Object\PriceWithQuantity;
use JMS\Serializer\Annotation as Serializer;

class Product
{
    /**
     * @Serializer\SerializedName("basketItemId")
     * @Serializer\Type("integer")
     * @var int
     */
    protected $basketItemId = 0;

    /**
     * @Serializer\SerializedName("goods")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct")
     * @var ShortProduct
     */
    protected $shortProduct;

    /**
     * @Serializer\SerializedName("qty")
     * @Serializer\Type("integer")
     * @var int
     */
    protected $quantity = 0;

    /**
     * @Serializer\SerializedName("prices")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\PriceWithQuantity>")
     * @var PriceWithQuantity[]
     */
    protected $prices = [];

    /**
     * @Serializer\SerializedName("useStamps")
     * @Serializer\Type("bool")
     * @var bool
     */
    protected $useStamps = false;

    /**
     * @Serializer\SerializedName("canUseStamps")
     * @Serializer\Type("bool")
     * @var bool
     */
    protected $canUseStamps = false;

    /**
     * @Serializer\SerializedName("canUseStampsAmount")
     * @Serializer\Type("int")
     * @var int
     */
    protected $canUseStampsAmount = 0;


    /**
     * @return int
     */
    public function getBasketItemId(): int
    {
        return $this->basketItemId;
    }

    /**
     * @param int $basketItemId
     *
     * @return Product
     */
    public function setBasketItemId(int $basketItemId): Product
    {
        $this->basketItemId = $basketItemId;
        return $this;
    }

    /**
     * @return null|ShortProduct
     */
    public function getShortProduct()
    {
        return $this->shortProduct;
    }

    /**
     * @param ShortProduct $shortProduct
     *
     * @return Product
     */
    public function setShortProduct(ShortProduct $shortProduct): Product
    {
        $this->shortProduct = $shortProduct;
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
     *
     * @return Product
     */
    public function setQuantity(int $quantity): Product
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return PriceWithQuantity[]
     */
    public function getPrices(): array
    {
        return $this->prices;
    }

    /**
     * @param PriceWithQuantity[] $prices
     * @return Product
     */
    public function setPrices(array $prices): Product
    {
        $this->prices = $prices;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUseStamps(): bool
    {
        return $this->useStamps;
    }

    /**
     * @param bool $useStamps
     * @return Product
     */
    public function setUseStamps(bool $useStamps): Product
    {
        $this->useStamps = $useStamps;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCanUseStamps(): bool
    {
        return $this->canUseStamps;
    }

    /**
     * @param bool $canUseStamps
     * @return Product
     */
    public function setCanUseStamps(bool $canUseStamps): Product
    {
        $this->canUseStamps = $canUseStamps;
        return $this;
    }

    /**
     * @return int
     */
    public function getCanUseStampsAmount(): int
    {
        return $this->canUseStampsAmount;
    }

    /**
     * @param int $canUseStampsAmount
     * @return Product
     */
    public function setCanUseStampsAmount(int $canUseStampsAmount): Product
    {
        $this->canUseStampsAmount = $canUseStampsAmount;
        return $this;
    }
}
