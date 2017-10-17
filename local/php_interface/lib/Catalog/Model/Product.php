<?php

namespace FourPaws\Catalog\Model;

use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\Catalog\Query\BrandQuery;
use FourPaws\Catalog\Utils;

class Product extends IblockElement
{
    /**
     * @var int
     */
    protected $PROPERTY_BRAND = 0;

    /**
     * @var string Вспомогательное поле, которое хранит имя бренда, чтобы не делать из-за этого дополнительный запрос,
     *     т.к. бывает нужно просто вывести его в названии продукта.
     */
    protected $PROPERTY_BRAND_NAME = '';

    /**
     * @var Brand
     */
    protected $brand;

    /**
     * @var string
     */
    protected $PROPERTY_FOR_WHO = '';

    /**
     * @var HlbReferenceItem
     */
    protected $forWho;

    /**
     * @var string
     */
    protected $PROPERTY_PET_SIZE = '';

    /**
     * @var HlbReferenceItem
     */
    protected $petSize;

    protected $PROPERTY_PET_AGE;

    protected $PROPERTY_PET_AGE_ADDITIONAL;

    protected $PROPERTY_PET_BREED;

    protected $PROPERTY_PET_GENDER;

    protected $PROPERTY_CATEGORY;

    protected $PROPERTY_PURPOSE;

    protected $PROPERTY_IMG = [];

    protected $PROPERTY_LABEL;

    protected $PROPERTY_STM;

    protected $PROPERTY_COUNTRY;

    protected $PROPERTY_TRADE_NAME;

    protected $PROPERTY_MAKER;

    protected $PROPERTY_MANAGER_OF_CATEGORY;

    protected $PROPERTY_MANUFACTURE_MATERIAL;

    protected $PROPERTY_SEASON_CLOTHES;

    protected $PROPERTY_WEIGHT_CAPACITY_PACKING;

    protected $PROPERTY_LICENSE;

    protected $PROPERTY_LOW_TEMPERATURE;

    protected $PROPERTY_PET_TYPE;

    protected $PROPERTY_PHARMA_GROUP;

    protected $PROPERTY_FEED_SPECIFICATION;

    protected $PROPERTY_FOOD;

    protected $PROPERTY_CONSISTENCE;

    protected $PROPERTY_FLAVOUR;

    protected $PROPERTY_FEATURES_OF_INGREDIENTS;

    protected $PROPERTY_PRODUCT_FORM;

    protected $PROPERTY_TYPE_OF_PARASITE;

    protected $PROPERTY_YML_NAME;

    protected $PROPERTY_SALES_NOTES;

    protected $PROPERTY_GROUP;

    protected $PROPERTY_GROUP_NAME;

    protected $PROPERTY_PRODUCED_BY_HOLDER;

    protected $PROPERTY_SPECIFICATIONS;

    /**
     * @return int
     */
    public function getBrandId(): int
    {
        return (int)$this->PROPERTY_BRAND;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function withBrandId(int $id)
    {
        $this->PROPERTY_BRAND = $id;

        //Сбросить бренд, чтобы выбрался новый.
        $this->brand = null;
        //Освежить вспомогательное свойство с именем бренда
        $this->PROPERTY_BRAND_NAME = $this->getBrand()->getName();

        return $this;
    }

    /**
     * @return string
     */
    public function getBrandName(): string
    {
        return $this->PROPERTY_BRAND_NAME;
    }

    /**
     * @return Brand
     */
    public function getBrand(): Brand
    {
        if (is_null($this->brand)) {
            $this->brand = (new BrandQuery())->withFilter(['=ID' => $this->getBrandId()])->exec()->current();
            /**
             * Если бренд не найден, "заткнуть" пустышкой.
             * Позволяет не рушить сайт, когда какой-то бренд выключают или удаляют.
             */
            if (!($this->brand instanceof Brand)) {
                $this->brand = new Brand();
            }
        }

        return $this->brand;
    }

    /**
     * @param Brand $brand
     *
     * @return $this
     */
    public function withBrand(Brand $brand)
    {
        $this->brand = $brand;
        $this->PROPERTY_BRAND = $brand;
        $this->PROPERTY_BRAND_NAME = $brand->getName();

        return $this;
    }

    /**
     * @return HlbReferenceItem
     */
    public function getForWho()
    {
        if (is_null($this->forWho)) {
            //TODO Починить, т.к. множественное
            $this->forWho = Utils::getReference('bx.hlblock.forwho', $this->PROPERTY_FOR_WHO);
        }

        return $this->forWho;
    }

    /**
     * @return HlbReferenceItem
     */
    public function getPetSize()
    {
        if(is_null($this->petSize)) {
            //TODO Починить, т.к. множественное
            $this->petSize = Utils::getReference(, $this->PROPERTY_PET_SIZE);
        }

        return $this->petSize;
    }

}
