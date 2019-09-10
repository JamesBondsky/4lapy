<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Colour
 *
 * @package FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct
 *
 * ОбъектКаталога.ПолныйТовар.Цвет
 */
class Colour
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
     * @Serializer\SerializedName("hexCode")
     */
    protected $hexCode;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("imageUrl")
     */
    protected $imageUrl;


    /**
     * @return int
     */
    public function getOfferId(): int
    {
        return $this->offerId;
    }

    /**
     * @param int $offerId
     * @return Colour
     */
    public function setOfferId(int $offerId): self
    {
        $this->offerId = $offerId;
        return $this;
    }

    public function setTitle($title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setHexCode($code): self
    {
        $this->hexCode = $code;
        return $this;
    }

    public function getHexCode(): string
    {
        return $this->hexCode;
    }

    public function setImageUrl($url): self
    {
        $this->imageUrl = $url;
        return $this;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }
}
