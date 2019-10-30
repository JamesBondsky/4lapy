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
     * @var string
     *
     * @Serializer\SerializedName("Название для Яндекс.Маркет")
     * @Serializer\Type("string")
     */
    protected $yandexName;

    /**
     * @var string
     *
     * @Serializer\SerializedName("Категория 1-го уровня")
     * @Serializer\Type("string")
     */
    protected $firstLevelCategory;

    /**
     * @var string
     *
     * @Serializer\SerializedName("Категория 2-го уровня")
     * @Serializer\Type("string")
     */
    protected $secondLevelCategory;

    /**
     * @var string
     *
     * @Serializer\SerializedName("Категория 3-го уровня")
     * @Serializer\Type("string")
     */
    protected $thirdLevelCategory;

    /**
     * @var string
     *
     * @Serializer\SerializedName("Бренд")
     * @Serializer\Type("string")
     */
    protected $brand;

    /**
     * @var int
     *
     * @Serializer\SerializedName("Суммарный остаток во всех магазинах")
     * @Serializer\Type("int")
     */
    protected $summQOffers;

    /**
     * @var int
     *
     * @Serializer\SerializedName("Суммарный остаток на складе транзитного поставщика")
     * @Serializer\Type("int")
     */
    protected $summQExporterDelivery;

    /**
     * @var string
     *
     * @Serializer\SerializedName("Вес")
     * @Serializer\Type("string")
     */
    protected $weight;

    /**
     * @var string
     *
     * @Serializer\SerializedName("Длина")
     * @Serializer\Type("string")
     */
    protected $length;

    /**
     * @var string
     *
     * @Serializer\SerializedName("Ширина")
     * @Serializer\Type("string")
     */
    protected $width;

    /**
     * @var string
     *
     * @Serializer\SerializedName("Высота")
     * @Serializer\Type("string")
     */
    protected $height;


    /**
     * @var int
     *
     * @Serializer\SerializedName("Сортировка (из товара)")
     * @Serializer\Type("int")
     */
    protected $sort;


    /**
     * @var string
     *
     * @Serializer\SerializedName("СТМ (из товара)")
     * @Serializer\Type("string")
     */
    protected $ctm;


    /**
     * @var string
     *
     * @Serializer\SerializedName("Основной раздел (из товара)")
     * @Serializer\Type("string")
     */
    protected $group;

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

    /**
     * @return string
     */
    public function getYandexName(): string
    {
        return $this->yandexName;
    }

    /**
     * @param string $yandexName
     * @return Product
     */
    public function setYandexName(string $yandexName): Product
    {
        $this->yandexName = $yandexName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstLevelCategory(): string
    {
        return $this->firstLevelCategory;
    }

    /**
     * @param string $firstLevelCategory
     * @return Product
     */
    public function setFirstLevelCategory(string $firstLevelCategory): Product
    {
        $this->firstLevelCategory = $firstLevelCategory;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecondLevelCategory(): string
    {
        return $this->secondLevelCategory;
    }

    /**
     * @param string $secondLevelCategory
     * @return Product
     */
    public function setSecondLevelCategory(string $secondLevelCategory): Product
    {
        $this->secondLevelCategory = $secondLevelCategory;

        return $this;
    }

    /**
     * @return string
     */
    public function getThirdLevelCategory(): string
    {
        return $this->thirdLevelCategory;
    }

    /**
     * @param string $thirdLevelCategory
     * @return Product
     */
    public function setThirdLevelCategory(string $thirdLevelCategory): Product
    {
        $this->thirdLevelCategory = $thirdLevelCategory;

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
     * @return Product
     */
    public function setBrand(string $brand): Product
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @param int $summQOffers
     * @return Product
     */
    public function setSummQOffers(int $summQOffers): Product
    {
        $this->summQOffers = $summQOffers;

        return $this;
    }

    /**
     * @return int
     */
    public function getSummQOffers(): int
    {
        return $this->summQOffers;
    }

    /**
     * @param int $summQExporterDelivery
     * @return Product
     */
    public function setSummQExporterDelivery(int $summQExporterDelivery): Product
    {
        $this->summQExporterDelivery = $summQExporterDelivery;

        return $this;
    }

    /**
     * @return int
     */
    public function getSummQExporterDelivery(): int
    {
        return $this->summQExporterDelivery;
    }

    /**
     * @param string $weight
     * @return Product
     */
    public function setWeight(string $weight): Product
    {
        $this->weight = $weight;

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
     * @param string $length
     * @return Product
     */
    public function setLength(string $length): Product
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return string
     */
    public function getLength(): string
    {
        return $this->length;
    }

    /**
     * @param string $width
     * @return Product
     */
    public function setWidth(string $width): Product
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return string
     */
    public function getWidth(): string
    {
        return $this->width;
    }

    /**
     * @param string $height
     * @return Product
     */
    public function setHeight(string $height): Product
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeight(): string
    {
        return $this->height;
    }

    /**
     * @param string $sort
     * @return Product
     */
    public function setSort(string $sort): Product
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return string
     */
    public function getSort(): string
    {
        return $this->sort;
    }

    /**
     * @param string $ctm
     * @return Product
     */
    public function setCtm(string $ctm): Product
    {
        $this->ctm = $ctm;

        return $this;
    }

    /**
     * @return string
     */
    public function getCtm(): string
    {
        return $this->ctm;
    }

    /**
     * @param string $groupId
     * @return Product
     */
    public function setGroup(string $group): Product
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }
}
