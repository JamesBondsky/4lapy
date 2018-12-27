<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;

use FourPaws\Decorators\FullHrefDecorator;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Bundle
 *
 * @package FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct
 *
 * ОбъектКаталога.ПолныйТовар.СопутствующийТовар
 */
class Bundle
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
     * @return Bundle
     */
    public function setOfferId(int $offerId): Bundle
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
     * @return Bundle
     */
    public function setTitle(string $title): Bundle
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
     * @return Bundle
     * @throws \Bitrix\Main\SystemException
     */
    public function setImage(string $image): Bundle
    {
        $hrefDecorator = new FullHrefDecorator($image);
        $this->image = $hrefDecorator->getFullPublicPath();
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
     * @return Bundle
     */
    public function setPrice(float $price): Bundle
    {
        $this->price = $price;
        return $this;
    }
}
