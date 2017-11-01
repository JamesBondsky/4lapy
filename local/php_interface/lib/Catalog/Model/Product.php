<?php

namespace FourPaws\Catalog\Model;

use DateTimeImmutable;
use FourPaws\BitrixOrm\Collection\HlbReferenceItemCollection;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\BitrixOrm\Model\TextContent;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Query\BrandQuery;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\ReferenceUtils;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

/**
 * Class Product
 * @package FourPaws\Catalog\Model
 *
 */
class Product extends IblockElement
{
    /**
     * @var bool
     * @Groups({"elastic"})
     */
    protected $active = true;

    /**
     * @var DateTimeImmutable
     * @Type("DateTimeImmutable")
     * @Accessor(getter="getDateActiveFrom")
     * @Groups({"elastic"})
     */
    protected $dateActiveFrom;

    /**
     * @var DateTimeImmutable
     * @Type("DateTimeImmutable")
     * @Accessor(getter="getDateActiveTo")
     * @Groups({"elastic"})
     */
    protected $dateActiveTo;

    /**
     * @var int
     * @Type("int")
     * @Groups({"elastic"})
     */
    protected $ID = 0;

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $NAME = '';

    /**
     * @var int
     * @Type("int")
     * @Groups({"elastic"})
     */
    protected $SORT = 500;

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $PREVIEW_TEXT = '';

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $PREVIEW_TEXT_TYPE = '';

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $DETAIL_TEXT = '';

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $DETAIL_TEXT_TYPE = '';

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $CANONICAL_PAGE_URL = '';

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $DETAIL_PAGE_URL = '';

    /**
     * @var int
     * @Type("int")
     * @Groups({"elastic"})
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
     * @var string[]
     * @Groups({"elastic"})
     */
    protected $PROPERTY_FOR_WHO = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $forWho;

    /**
     * @var string[]
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PET_SIZE = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $petSize;

    /**
     * @var string[]
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PET_AGE = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $petAge;

    /**
     * @var string[]
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PET_AGE_ADDITIONAL = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $petAgeAdditional;

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PET_BREED = '';

    /**
     * @var HlbReferenceItem
     */
    protected $petBreed;

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PET_GENDER = '';

    /**
     * @var HlbReferenceItem
     */
    protected $petGender;

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $PROPERTY_CATEGORY = '';

    /**
     * @var HlbReferenceItem
     */
    protected $category;

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PURPOSE = '';

    /**
     * @var HlbReferenceItem
     */
    protected $purpose;

    //TODO Изображения
    protected $PROPERTY_IMG = [];

    /**
     * @var string[]
     * @Groups({"elastic"})
     */
    protected $PROPERTY_LABEL = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $label;

    /**
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_STM = false;

    /**
     * @var string
     */
    protected $PROPERTY_COUNTRY = '';

    /**
     * @var HlbReferenceItem
     */
    protected $country;

    /**
     * @var string[]
     * @Groups({"elastic"})
     */
    protected $PROPERTY_TRADE_NAME = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $tradeName;

    /**
     * @var string[]
     * @Groups({"elastic"})
     */
    protected $PROPERTY_MAKER = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $maker;

    /**
     * @var string[]
     * @Groups({"elastic"})
     */
    protected $PROPERTY_MANAGER_OF_CATEGORY = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $managerOfCategory;

    /**
     * @var string[]
     * @Groups({"elastic"})
     */
    protected $PROPERTY_MANUFACTURE_MATERIAL = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $manufactureMaterial;

    /**
     * @var string[]
     * @Groups({"elastic"})
     */
    protected $PROPERTY_SEASON_CLOTHES = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $seasonClothes;

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $PROPERTY_WEIGHT_CAPACITY_PACKING = '';

    /**
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_LICENSE = false;

    /**
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_LOW_TEMPERATURE = false;

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PET_TYPE = '';

    /**
     * @var HlbReferenceItem
     */
    protected $petType;

    /**
     * @var string
     * @Groups({"elastic"})
     * TODO Есть риск, что это свойство окажется множественным
     */
    protected $PROPERTY_PHARMA_GROUP = '';

    /**
     * @var HlbReferenceItem
     */
    protected $pharmaGroup;

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $PROPERTY_FEED_SPECIFICATION = '';

    /**
     * @var HlbReferenceItem
     */
    protected $feedSpecification;

    /**
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_FOOD = false;

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $PROPERTY_CONSISTENCE = '';

    /**
     * @var HlbReferenceItem
     */
    protected $consistence;

    /**
     * @var string[]
     * @Groups({"elastic"})
     */
    protected $PROPERTY_FLAVOUR = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $flavour;

    /**
     * @var string[]
     * @Groups({"elastic"})
     */
    protected $PROPERTY_FEATURES_OF_INGREDIENTS = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $featuresOfIngredients;

    /**
     * @var string[]
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PRODUCT_FORM = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $productForm;

    /**
     * @var string[]
     * @Groups({"elastic"})
     */
    protected $PROPERTY_TYPE_OF_PARASITE = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $typeOfParasite;

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $PROPERTY_YML_NAME = '';

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $PROPERTY_SALES_NOTES = '';

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $PROPERTY_GROUP = '';

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $PROPERTY_GROUP_NAME = '';

    /**
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PRODUCED_BY_HOLDER = false;

    /**
     * @var array
     * @Groups({"elastic"})
     */
    protected $PROPERTY_SPECIFICATIONS = [];

    /**
     * @var TextContent
     */
    protected $specifications;

    /**
     * @var OfferCollection
     */
    protected $offers;

    /**
     * @return OfferCollection
     */
    public function getOffers(): OfferCollection
    {
        if (is_null($this->offers)) {
            $this->offers = (new OfferQuery())->withFilterParameter('=PROPERTY_CML2_LINK', $this->getId())
                                              ->exec();
        }

        return $this->offers;
    }

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
     * @return HlbReferenceItemCollection
     */
    public function getForWho()
    {
        if (is_null($this->forWho)) {
            $this->forWho = ReferenceUtils::getReferenceMulti('bx.hlblock.forwho', $this->PROPERTY_FOR_WHO);
        }

        return $this->forWho;
    }

    /**
     * @return HlbReferenceItemCollection
     */
    public function getPetSize()
    {
        if (is_null($this->petSize)) {
            $this->petSize = ReferenceUtils::getReferenceMulti('bx.hlblock.petsize', $this->PROPERTY_PET_SIZE);
        }

        return $this->petSize;
    }

    /**
     * Возвращает категорию товара.
     *
     * \attention Это всего лишь одноимённое свойство из SAP и никак не связано с категориями каталога на сайте.
     *
     * @return HlbReferenceItem
     */
    public function getCategory()
    {
        if (is_null($this->category)) {
            $this->category = ReferenceUtils::getReference('bx.hlblock.productcategory', $this->PROPERTY_CATEGORY);
        }

        return $this->category;
    }

    /**
     * @return HlbReferenceItem
     */
    public function getPurpose()
    {
        if (is_null($this->purpose)) {
            $this->purpose = ReferenceUtils::getReference('bx.hlblock.purpose', $this->PROPERTY_PURPOSE);
        }

        return $this->purpose;
    }

    /**
     * @return HlbReferenceItemCollection
     */
    public function getPetAge()
    {
        if (is_null($this->petAge)) {
            $this->petAge = ReferenceUtils::getReferenceMulti('bx.hlblock.petage', $this->PROPERTY_PET_AGE);
        }

        return $this->petAge;
    }

    /**
     * @return HlbReferenceItemCollection
     */
    public function getPetAgeAdditional()
    {
        if (is_null($this->petAgeAdditional)) {
            $this->petAgeAdditional = ReferenceUtils::getReferenceMulti(
                'bx.hlblock.petageadditional',
                $this->PROPERTY_PET_AGE_ADDITIONAL
            );
        }

        return $this->petAgeAdditional;
    }

    /**
     * @return HlbReferenceItem
     */
    public function getPetBreed()
    {
        if (is_null($this->petBreed)) {
            $this->petBreed = ReferenceUtils::getReference('bx.hlblock.petbreed', $this->PROPERTY_PET_BREED);
        }

        return $this->petBreed;
    }

    /**
     * @return HlbReferenceItem
     */
    public function getPetGender()
    {
        if (is_null($this->petGender)) {
            $this->petGender = ReferenceUtils::getReference('bx.hlblock.petgender', $this->PROPERTY_PET_GENDER);
        }

        return $this->petGender;
    }

    /**
     * @return HlbReferenceItemCollection
     */
    public function getLabels()
    {
        if (is_null($this->label)) {
            $this->label = ReferenceUtils::getReferenceMulti('bx.hlblock.label', $this->PROPERTY_LABEL);
            //TODO Добавить динамический запрос шильдиков по акциям, в которых в данном регионе участвует этот продукт
            //TODO Сделать, чтобы это была отдельная коллекция объектов "Шильдик", а не просто элемент справочника.
        }

        return $this->label;
    }

    /**
     * Возвращает признак "товар собственной торговой марки"
     *
     * @return bool
     */
    public function isSTM()
    {
        return (bool)(int)$this->PROPERTY_STM;
    }

    /**
     * @return HlbReferenceItem
     */
    public function getCountry()
    {
        if (is_null($this->country)) {
            $this->country = ReferenceUtils::getReference('bx.hlblock.country', $this->PROPERTY_COUNTRY);
        }

        return $this->country;
    }

    /**
     * @return HlbReferenceItemCollection
     */
    public function getTradeNames()
    {
        if (is_null($this->tradeName)) {
            $this->tradeName = ReferenceUtils::getReferenceMulti('bx.hlblock.tradename', $this->PROPERTY_TRADE_NAME);
        }

        return $this->tradeName;
    }

    /**
     * @return HlbReferenceItemCollection
     */
    public function getMakers()
    {
        if (is_null($this->maker)) {
            $this->maker = ReferenceUtils::getReferenceMulti('bx.hlblock.maker', $this->PROPERTY_MAKER);
        }

        return $this->maker;
    }

    /**
     * @return HlbReferenceItemCollection
     */
    public function getManagersOfCategory()
    {
        if (is_null($this->managerOfCategory)) {
            $this->managerOfCategory = ReferenceUtils::getReferenceMulti(
                'bx.hlblock.categorymanager',
                $this->PROPERTY_MANAGER_OF_CATEGORY
            );
        }

        return $this->managerOfCategory;
    }

    /**
     * @return HlbReferenceItemCollection
     */
    public function getManufactureMaterials()
    {
        if (is_null($this->manufactureMaterial)) {
            $this->manufactureMaterial = ReferenceUtils::getReferenceMulti(
                'bx.hlblock.material',
                $this->PROPERTY_MANUFACTURE_MATERIAL
            );
        }

        return $this->manufactureMaterial;
    }

    /**
     * @return HlbReferenceItemCollection
     */
    public function getClothesSeasons()
    {
        if (is_null($this->seasonClothes)) {
            $this->seasonClothes = ReferenceUtils::getReferenceMulti(
                'bx.hlblock.season',
                $this->PROPERTY_SEASON_CLOTHES
            );
        }

        return $this->seasonClothes;
    }

    /**
     * @return string
     */
    public function getWeightCapacityPacking()
    {
        return $this->PROPERTY_WEIGHT_CAPACITY_PACKING;
    }

    /**
     * Возвращает признак "Требуется лицензия"
     *
     * @return bool
     */
    public function isLicenseRequired()
    {
        return (bool)(int)$this->PROPERTY_LICENSE;
    }

    /**
     * Возвращает признак "Требуется хранение при низкой температуре"
     *
     * @return bool
     */
    public function isLowTemperatureRequired()
    {
        return (bool)(int)$this->PROPERTY_LOW_TEMPERATURE;
    }

    /**
     * @return HlbReferenceItem
     */
    public function getPetType()
    {
        if (is_null($this->petType)) {
            $this->petType = ReferenceUtils::getReference('bx.hlblock.pettype', $this->PROPERTY_PET_TYPE);
        }

        return $this->petType;
    }

    /**
     * @return HlbReferenceItem
     */
    public function getPharmaGroup()
    {
        if (is_null($this->pharmaGroup)) {
            $this->pharmaGroup = ReferenceUtils::getReference('bx.hlblock.pharmagroup', $this->PROPERTY_PHARMA_GROUP);
        }

        return $this->pharmaGroup;
    }

    /**
     * Возвращает специализацию корма
     *
     * @return HlbReferenceItem
     */
    public function getFeedSpecification()
    {
        if (is_null($this->feedSpecification)) {
            $this->feedSpecification = ReferenceUtils::getReference(
                'bx.hlblock.feedspec',
                $this->PROPERTY_FEED_SPECIFICATION
            );
        }

        return $this->feedSpecification;
    }

    /**
     * Возвращает признак "Еда", т.е. является ли продукт съедобным.
     *
     * @return bool
     */
    public function isFood()
    {
        return (bool)(int)$this->PROPERTY_FOOD;
    }

    /**
     * @return HlbReferenceItem
     */
    public function getConsistence()
    {
        if (is_null($this->consistence)) {
            $this->consistence = ReferenceUtils::getReference('bx.hlblock.consistence', $this->PROPERTY_CONSISTENCE);
        }

        return $this->consistence;
    }

    /**
     * @return HlbReferenceItemCollection
     */
    public function getFlavour()
    {
        if (is_null($this->flavour)) {
            $this->flavour = ReferenceUtils::getReferenceMulti('bx.hlblock.flavour', $this->PROPERTY_FLAVOUR);
        }

        return $this->flavour;
    }

    /**
     * Возвращает особенности ингридиентов
     *
     * @return HlbReferenceItemCollection
     */
    public function getFeaturesOfIngredients()
    {
        if (is_null($this->featuresOfIngredients)) {
            $this->featuresOfIngredients = ReferenceUtils::getReferenceMulti(
                'bx.hlblock.ingridientfeatures',
                $this->PROPERTY_FEATURES_OF_INGREDIENTS
            );
        }

        return $this->featuresOfIngredients;
    }

    /**
     * Возвращает формы выпуска продукта
     *
     * @return HlbReferenceItemCollection
     */
    public function getProductForms()
    {
        if (is_null($this->productForm)) {
            $this->productForm = ReferenceUtils::getReferenceMulti(
                'bx.hlblock.productform',
                $this->PROPERTY_PRODUCT_FORM
            );
        }

        return $this->productForm;
    }

    /**
     * Возвращает типы паразитов.
     *
     * @return HlbReferenceItemCollection
     */
    public function getTypesOfParasites()
    {
        if (is_null($this->typeOfParasite)) {
            $this->typeOfParasite = ReferenceUtils::getReferenceMulti(
                'bx.hlblock.parasitetype',
                $this->PROPERTY_TYPE_OF_PARASITE
            );
        }

        return $this->typeOfParasite;
    }

    /**
     * Возвращает имя товара для Яндекс.Маркет
     *
     * @return string
     */
    public function getYmlName()
    {
        return $this->PROPERTY_YML_NAME;
    }

    /**
     * Возвращает примечания о товаре (sales notes) для Яндекс.Маркет
     *
     * @return string
     */
    public function getSalesNotes()
    {
        return $this->PROPERTY_SALES_NOTES;
    }

    /**
     * Возвращает id группы товаров.
     *
     * \remark Скорее всего, ненужное на текущем сайте свойство товара.
     *
     * @return string
     */
    public function getGroupId()
    {
        return $this->PROPERTY_GROUP;
    }

    /**
     * Возвращает название группы товаров.
     *
     * \remark Скорее всего, ненужное на текущем сайте свойство товара.
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->PROPERTY_GROUP_NAME;
    }

    /**
     * Возвращает признак "Произведено по заказу правообладателя"
     *
     * @return bool
     */
    public function isProducedByHolderRequest()
    {
        return (bool)(int)$this->PROPERTY_PRODUCED_BY_HOLDER;
    }

    /**
     * Возвращает технические характеристики товара.
     *
     * @return TextContent
     */
    public function getSpecifications()
    {
        if (is_null($this->specifications)) {
            $this->specifications = new TextContent($this->PROPERTY_SPECIFICATIONS);
        }

        return $this->specifications;
    }
}
