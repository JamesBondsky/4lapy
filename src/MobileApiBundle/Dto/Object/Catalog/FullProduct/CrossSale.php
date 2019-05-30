<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;

use FourPaws\Decorators\FullHrefDecorator;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class CrossSale
 *
 * @package FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct
 *
 * ОбъектКаталога.ПолныйТовар.СопутствующийТовар
 */
class CrossSale
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
     * @Serializer\Type("float")
     * @Serializer\SerializedName("price")
     * @var float
     */
    protected $price;

    /**
     * @return int
     */
    public function getOfferId(): int
    {
        return $this->offerId;
    }

    /**
     * @param int $offerId
     * @return CrossSale
     */
    public function setOfferId(int $offerId): CrossSale
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
     * @return CrossSale
     */
    public function setTitle(string $title): CrossSale
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
     * @return CrossSale
     */
    public function setImage(string $image): CrossSale
    {
        $this->image = (string) new FullHrefDecorator($image);
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return CrossSale
     */
    public function setPrice(float $price): CrossSale
    {
        $this->price = $price;
        return $this;
    }
}
