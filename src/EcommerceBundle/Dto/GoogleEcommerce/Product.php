<?php

namespace FourPaws\EcommerceBundle\Dto\GoogleEcommerce;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Product
 *
 * @package FourPaws\EcommerceBundle\Dto\GoogleEcommerce
 */
class Product
{
    /**
     * Id товара
     *
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $id;

    /**
     * Название торгового предложения
     *
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * Бренд
     *
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $brand;

    /**
     * Категория товара, строка типа 'Категория 1|Категория 2|Категория 3'
     *
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $category;

    /**
     * Тип товара
     *
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $variant;

    /**
     * Список, в котором показывается товар
     *
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $list;

    /**
     * Цена
     *
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $price;

    /**
     * Количество
     *
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $quantity = 0;

    /**
     * Номер в списке товаров
     *
     * @Serializer\Type("int")
     * @Serializer\SkipWhenEmpty()
     *
     * @var int
     */
    protected $position;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return Product
     */
    public function setId(string $id): Product
    {
        $this->id = $id;

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
     *
     * @return Product
     */
    public function setName(string $name): Product
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     *
     * @return Product
     */
    public function setBrand(string $brand): Product
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $category
     *
     * @return Product
     */
    public function setCategory(string $category): Product
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return string
     */
    public function getList(): string
    {
        return $this->list;
    }

    /**
     * @param string $list
     *
     * @return Product
     */
    public function setList(?string $list): Product
    {
        $this->list = $list;

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
     * @return Product
     */
    public function setPrice(float $price): Product
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     *
     * @return Product
     */
    public function setPosition(int $position): Product
    {
        $this->position = $position;

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
     * @return $this
     */
    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getVariant(): string
    {
        return $this->variant;
    }

    /**
     * @param string $variant
     *
     * @return $this
     */
    public function setVariant(string $variant): self
    {
        $this->variant = $variant;

        return $this;
    }
}
