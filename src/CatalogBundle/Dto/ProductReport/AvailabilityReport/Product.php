<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\CatalogBundle\Dto\ProductReport\AvailabilityReport;

use JMS\Serializer\Annotation as Serializer;

class Product
{
    /**
     * @var string
     *
     * @Serializer\SerializedName("Внешний код")
     * @Serializer\Type("string")
     */
    protected $xmlId;

    /**
     * @var string
     *
     * @Serializer\SerializedName("Название")
     * @Serializer\Type("string")
     */
    protected $name;

    /**
     * @var bool
     *
     * @Serializer\SerializedName("Наличие изображения")
     * @Serializer\Type("bitrix_bool")
     */
    protected $image;

    /**
     * @var bool
     *
     * @Serializer\SerializedName("Наличие описания")
     * @Serializer\Type("bitrix_bool")
     */
    protected $description;

    /**
     * @var bool
     *
     * @Serializer\SerializedName("Активен")
     * @Serializer\Type("bitrix_bool")
     */
    protected $active;

    /**
     * @var \DateTimeImmutable
     *
     * @Serializer\SerializedName("Дата создания")
     * @Serializer\Type("DateTimeImmutable<'Y-m-d H:i:s'>")
     */
    protected $dateCreate;

    /**
     * @var int
     *
     * @Serializer\SerializedName("Остаток на РЦ")
     * @Serializer\Type("int")
     */
    protected $stocks;

    /**
     * @var float
     *
     * @Serializer\SerializedName("Цена")
     * @Serializer\Type("float")
     */
    protected $price;

    /**
     * @return string
     */
    public function getXmlId(): string
    {
        return $this->xmlId;
    }

    /**
     * @param string $xmlId
     * @return Product
     */
    public function setXmlId(string $xmlId): Product
    {
        $this->xmlId = $xmlId;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Product
     */
    public function setName(string $name): Product
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasImage(): bool
    {
        return $this->image;
    }

    /**
     * @param bool $image
     * @return Product
     */
    public function setImage(bool $image): Product
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasDescription(): bool
    {
        return $this->description;
    }

    /**
     * @param bool $description
     * @return Product
     */
    public function setDescription(bool $description): Product
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return Product
     */
    public function setActive(bool $active): Product
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDateCreate(): \DateTimeImmutable
    {
        return $this->dateCreate;
    }

    /**
     * @param \DateTimeImmutable $dateCreate
     * @return Product
     */
    public function setDateCreate(\DateTimeImmutable $dateCreate): Product
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    /**
     * @return int
     */
    public function getStocks(): int
    {
        return $this->stocks;
    }

    /**
     * @param int $stocks
     * @return Product
     */
    public function setStocks(int $stocks): Product
    {
        $this->stocks = $stocks;

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
     * @return Product
     */
    public function setPrice(float $price): Product
    {
        $this->price = $price;

        return $this;
    }
}
