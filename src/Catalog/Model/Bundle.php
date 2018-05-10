<?php

namespace FourPaws\Catalog\Model;

use JMS\Serializer\Annotation as Serializer;

class Bundle
{
    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("ACTIVE")
     * @Serializer\Groups(groups={"read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $active = true;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $id = 0;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("NAME")
     * @Serializer\Groups(groups={"read"})
     */
    protected $name = '';

    /**
     * @var BundleItem[]
     * @Serializer\Type("array<FourPaws\Catalog\Model\BundleItem>")
     * @Serializer\SerializedName("PRODUCTS")
     * @Serializer\Groups(groups={"read"})
     */
    protected $products = [];

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_COUNT_ITEMS")
     * @Serializer\Groups(groups={"read"})
     */
    protected $countItems = 0;

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
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
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return BundleItem[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @param BundleItem[] $products
     */
    public function setProducts(array $products): void
    {
        $this->products = $products;
    }

    /**
     * @return int
     */
    public function getCountItems(): int
    {
        return $this->countItems;
    }

    /**
     * @param int $countItems
     */
    public function setCountItems(int $countItems): void
    {
        $this->countItems = $countItems;
    }
}