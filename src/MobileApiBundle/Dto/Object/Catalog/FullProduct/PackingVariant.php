<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class PackingVariant
 *
 * @package FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct
 *
 * ОбъектКаталога.ПолныйТовар.Фасовки
 */
class PackingVariant
{
    /**
     * Идентификатор предложения товара
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("offerId")
     */
    protected $offerId;

    /**
     * Вес фасовки
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("weight")
     */
    protected $weight;

    /**
     * Цена
     * @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("price")
     */
    protected $price;

    /**
     * Есть ли акции?
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("hasSpecialOffer")
     */
    protected $hasSpecialOffer = false;

    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("isAvailable")
     */
    protected $isAvailable = false;

    /**
     * @return int
     */
    public function getOfferId(): int
    {
        return $this->offerId;
    }

    /**
     * @param int $offerId
     *
     * @return PackingVariant
     */
    public function setOfferId(int $offerId): PackingVariant
    {
        $this->offerId = $offerId;
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
     * @return PackingVariant
     */
    public function setWeight(string $weight): PackingVariant
    {
        $this->weight = $weight;
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
     * @return PackingVariant
     */
    public function setPrice(float $price): PackingVariant
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHasSpecialOffer(): bool
    {
        return $this->hasSpecialOffer;
    }

    /**
     * @param bool $hasSpecialOffer
     *
     * @return PackingVariant
     */
    public function setHasSpecialOffer(bool $hasSpecialOffer): PackingVariant
    {
        $this->hasSpecialOffer = $hasSpecialOffer;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * @param bool $isAvailable
     * @return PackingVariant
     */
    public function setIsAvailable(bool $isAvailable): PackingVariant
    {
        $this->isAvailable = $isAvailable;
        return $this;
    }
}
