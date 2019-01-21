<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;

use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class BundleItem
 *
 * @package FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct
 *
 * ОбъектКаталога.ПолныйТовар.CЭтимТоваромПокупают.Товар
 */
class BundleItem
{
    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("offerId")
     * @var int
     */
    protected $offerId;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     */
    protected $title;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("image")
     */
    protected $image;

    /**
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Price")
     * @Serializer\SerializedName("price")
     * @var Price
     */
    protected $price;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("weight")
     * @var string
     */
    protected $weight;

    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("quantity")
     * @var int
     */
    protected $quantity;


    /**
     * @return int
     */
    public function getOfferId(): int
    {
        return $this->offerId;
    }

    /**
     * @param int $offerId
     * @return BundleItem
     */
    public function setOfferId(int $offerId): BundleItem
    {
        $this->offerId = $offerId;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return BundleItem
     */
    public function setTitle(string $title): BundleItem
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     *
     * @return BundleItem
     * @throws \Bitrix\Main\SystemException
     */
    public function setImage(string $image): BundleItem
    {
        $hrefDecorator = new FullHrefDecorator($image);
        $this->image = $hrefDecorator->getFullPublicPath();
        return $this;
    }

    /**
     * @return Price
     */
    public function getPrice(): Price
    {
        return $this->price;
    }

    /**
     * @param Price $price
     *
     * @return BundleItem
     */
    public function setPrice(Price $price): BundleItem
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return string
     */
    public function getWeight(): string
    {
        return $this->weight;
    }

    /**
     * @param string $weight
     *
     * @return BundleItem
     */
    public function setWeight(string $weight): BundleItem
    {
        $this->weight = $weight;
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
     * @return BundleItem
     */
    public function setQuantity(int $quantity): BundleItem
    {
        $this->quantity = $quantity;
        return $this;
    }
}
