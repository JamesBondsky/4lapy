<?php

namespace FourPaws\Catalog\Model;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Collection\HlbReferenceItemCollection;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\BitrixOrm\Type\TextContent;
use FourPaws\BitrixOrm\Utils\ReferenceUtils;
use FourPaws\Catalog\Query\BrandQuery;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Search\Model\HitMetaInfoAwareInterface;
use FourPaws\Search\Model\HitMetaInfoAwareTrait;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;

class Product extends IblockElement implements HitMetaInfoAwareInterface
{
    use HitMetaInfoAwareTrait;

    const AVAILABILITY_DELIVERY = 'delivery';

    const AVAILABILITY_PICKUP = 'pickup';

    const AVAILABILITY_BY_REQUEST = 'byRequest';

    /**
     * @var bool
     * @Type("bool")
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
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $CODE = '';

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $XML_ID = '';

    /**
     * @var string
     * @Type("string")
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
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PREVIEW_TEXT = '';

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PREVIEW_TEXT_TYPE = '';

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $DETAIL_TEXT = '';

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $DETAIL_TEXT_TYPE = '';

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $CANONICAL_PAGE_URL = '';

    /**
     * @var string
     * @Type("string")
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
     * @Type("FourPaws\Catalog\Model\Brand")
     * @Accessor(getter="getBrand")
     * @Groups({"elastic"})
     */
    protected $brand;

    /**
     * @var string[]
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_FOR_WHO = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $forWho;

    /**
     * @var string[]
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PET_SIZE = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $petSize;

    /**
     * @var string[]
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PET_AGE = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $petAge;

    /**
     * @var string[]
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PET_AGE_ADDITIONAL = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $petAgeAdditional;

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PET_BREED = '';

    /**
     * @var HlbReferenceItem
     */
    protected $petBreed;

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PET_GENDER = '';

    /**
     * @var HlbReferenceItem
     */
    protected $petGender;

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_CATEGORY = '';

    /**
     * @var HlbReferenceItem
     */
    protected $category;

    /**
     * @var string
     * @Type("string")
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
     * @Type("array")
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
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_COUNTRY = '';

    /**
     * @var HlbReferenceItem
     */
    protected $country;

    /**
     * @var string[]
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_TRADE_NAME = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $tradeName;

    /**
     * @var string[]
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_MAKER = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $maker;

    /**
     * @var string[]
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_MANAGER_OF_CATEGORY = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $managerOfCategory;

    /**
     * @var string[]
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_MANUFACTURE_MATERIAL = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $manufactureMaterial;

    /**
     * @var string[]
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_SEASON_CLOTHES = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $seasonClothes;

    /**
     * @var string
     * @Type("string")
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
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_REFRIGERATED = false;

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PET_TYPE = '';

    /**
     * @var HlbReferenceItem
     */
    protected $petType;

    /**
     * @var string
     * @Type("string")
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
     * @Type("string")
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
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_CONSISTENCE = '';

    /**
     * @var HlbReferenceItem
     */
    protected $consistence;

    /**
     * @var string[]
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_FLAVOUR = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $flavour;

    /**
     * @var string[]
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_FEATURES_OF_INGREDIENTS = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $featuresOfIngredients;

    /**
     * @var string[]
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PRODUCT_FORM = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $productForm;

    /**
     * @var string[]
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_TYPE_OF_PARASITE = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $typeOfParasite;

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_YML_NAME = '';

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_SALES_NOTES = '';

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_GROUP = '';

    /**
     * @var string
     * @Type("string")
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
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_SPECIFICATIONS = [];

    /**
     * @var TextContent
     */
    protected $specifications;

    /**
     * @var Collection
     * @Type("ArrayCollection<FourPaws\Catalog\Model\Offer>")
     * @Accessor(getter="getOffers")
     * @Groups({"elastic"})
     */
    protected $offers;

    /**
     * @var int[] ID всех разделов инфоблока, к которым прикреплён элемент.
     * @Type("array")
     * @Accessor(getter="getSectionsIdList")
     * @Groups({"elastic"})
     */
    protected $sectionIdList;

    /**
     * @var string[]
     * @Type("array<string>")
     * @Accessor(getter="getSuggest")
     * @Groups({"elastic"})
     */
    protected $suggest;

    /**
     * @var bool
     * @Type("bool")
     * @Accessor(getter="hasActions")
     * @Groups({"elastic"})
     */
    protected $hasActions;

    /**
     * @var array
     * @Type("array<string>")
     * @Accessor(getter="getDeliveryAvailability")
     * @Groups({"elastic"})
     */
    protected $deliveryAvailability;

    /**
     * @var string
     */
    protected $PROPERTY_PACKING_COMBINATION = '';

    public function __construct(array $fields = [])
    {
        parent::__construct($fields);
        /**
         * Если свойство не заполнено, битрикс для его значения возвращает bool false. А если это заполненное свойство
         * типа "HTML/текст", то его значение - массив из двух строк. Однако, mapping для Elasticsearch не может
         * одновременно относиться к свойству и как к boolean и как к объекту.
         */
        if (false === $this->PROPERTY_SPECIFICATIONS) {
            $this->PROPERTY_SPECIFICATIONS = [
                'TYPE' => '',
                'TEXT' => '',
            ];
        }
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
     * @return Brand
     */
    public function getBrand(): Brand
    {
        if (null === $this->brand) {
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
     * @return int
     */
    public function getBrandId(): int
    {
        return (int)$this->PROPERTY_BRAND;
    }

    /**
     * @return string
     */
    public function getBrandName(): string
    {
        return $this->PROPERTY_BRAND_NAME;
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
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getForWho()
    {
        if (null === $this->forWho) {
            $this->forWho = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.forwho'),
                $this->PROPERTY_FOR_WHO
            );
        }

        return $this->forWho;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getPetSize()
    {
        if (null === $this->petSize) {
            $this->petSize = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.petsize'),
                $this->PROPERTY_PET_SIZE
            );
        }

        return $this->petSize;
    }

    /**
     * Возвращает категорию товара.
     *
     * \attention Это всего лишь одноимённое свойство из SAP и никак не связано с категориями каталога на сайте.
     *
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getCategory()
    {
        if ((null === $this->category) && $this->PROPERTY_CATEGORY) {
            $this->category = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.productcategory'),
                $this->PROPERTY_CATEGORY
            );
        }

        return $this->category;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getPurpose()
    {
        if ((null === $this->purpose) && $this->PROPERTY_PURPOSE) {
            $this->purpose = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.purpose'),
                $this->PROPERTY_PURPOSE
            );
        }

        return $this->purpose;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getPetAge()
    {
        if (null === $this->petAge) {
            $this->petAge = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.petage'),
                $this->PROPERTY_PET_AGE
            );
        }

        return $this->petAge;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getPetAgeAdditional()
    {
        if (null === $this->petAgeAdditional) {
            $this->petAgeAdditional = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.petageadditional'),
                $this->PROPERTY_PET_AGE_ADDITIONAL
            );
        }

        return $this->petAgeAdditional;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getPetBreed()
    {
        if ((null === $this->petBreed) && $this->PROPERTY_PET_BREED) {
            $this->petBreed = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.petbreed'),
                $this->PROPERTY_PET_BREED
            );
        }

        return $this->petBreed;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getPetGender()
    {
        if ((null === $this->petGender) && $this->PROPERTY_PET_GENDER) {
            $this->petGender = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.petgender'),
                $this->PROPERTY_PET_GENDER
            );
        }

        return $this->petGender;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getLabels()
    {
        if (null === $this->label) {
            $this->label = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.label'),
                $this->PROPERTY_LABEL
            );
            /*
             * TODO Добавить динамический запрос шильдиков по акциям, в которых в данном регионе участвует этот продукт
             */

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
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getCountry()
    {
        if ((null === $this->country) && $this->PROPERTY_COUNTRY) {
            $this->country = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.country'),
                $this->PROPERTY_COUNTRY
            );
        }

        return $this->country;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getTradeNames()
    {
        if (null === $this->tradeName) {
            $this->tradeName = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.tradename'),
                $this->PROPERTY_TRADE_NAME
            );
        }

        return $this->tradeName;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getMakers()
    {
        if (null === $this->maker) {
            $this->maker = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.maker'),
                $this->PROPERTY_MAKER
            );
        }

        return $this->maker;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getManagersOfCategory()
    {
        if (null === $this->managerOfCategory) {
            $this->managerOfCategory = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.categorymanager'),
                $this->PROPERTY_MANAGER_OF_CATEGORY
            );
        }

        return $this->managerOfCategory;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getManufactureMaterials()
    {
        if (null === $this->manufactureMaterial) {
            $this->manufactureMaterial = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.material'),
                $this->PROPERTY_MANUFACTURE_MATERIAL
            );
        }

        return $this->manufactureMaterial;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getClothesSeasons()
    {
        if (null === $this->seasonClothes) {
            $this->seasonClothes = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.season'),
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
     * Возвращает признак "Перевозить в холодильнике"
     *
     * @return bool
     */
    public function isRefrigerated()
    {
        return (bool)(int)$this->PROPERTY_REFRIGERATED;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getPetType()
    {
        if ((null === $this->petType) && $this->PROPERTY_PET_TYPE) {
            $this->petType = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.pettype'),
                $this->PROPERTY_PET_TYPE
            );
        }

        return $this->petType;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getPharmaGroup()
    {
        if ((null === $this->pharmaGroup) && $this->PROPERTY_PHARMA_GROUP) {
            $this->pharmaGroup = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.pharmagroup'),
                $this->PROPERTY_PHARMA_GROUP
            );
        }

        return $this->pharmaGroup;
    }

    /**
     * Возвращает специализацию корма
     *
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getFeedSpecification()
    {
        if ((null === $this->feedSpecification) && $this->PROPERTY_FEED_SPECIFICATION) {
            $this->feedSpecification = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.feedspec'),
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
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getConsistence()
    {
        if ((null === $this->consistence) && $this->PROPERTY_CONSISTENCE) {
            $this->consistence = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.consistence'),
                $this->PROPERTY_CONSISTENCE
            );
        }

        return $this->consistence;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItemCollection
     */
    public function getFlavour()
    {
        if ((null === $this->flavour) && $this->PROPERTY_FLAVOUR) {
            $this->flavour = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.flavour'),
                $this->PROPERTY_FLAVOUR
            );
        }

        return $this->flavour;
    }

    /**
     * Возвращает особенности ингридиентов
     *
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getFeaturesOfIngredients()
    {
        if (null === $this->featuresOfIngredients) {
            $this->featuresOfIngredients = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.ingridientfeatures'),
                $this->PROPERTY_FEATURES_OF_INGREDIENTS
            );
        }

        return $this->featuresOfIngredients;
    }

    /**
     * Возвращает формы выпуска продукта
     *
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getProductForms()
    {
        if (null === $this->productForm) {
            $this->productForm = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.productform'),
                $this->PROPERTY_PRODUCT_FORM
            );
        }

        return $this->productForm;
    }

    /**
     * Возвращает типы паразитов.
     *
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getTypesOfParasites()
    {
        if (null === $this->typeOfParasite) {
            $this->typeOfParasite = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.parasitetype'),
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
        if (!($this->specifications instanceof TextContent)) {
            $this->specifications = new TextContent($this->PROPERTY_SPECIFICATIONS);
        }

        return $this->specifications;
    }

    /**
     * Возвращает информацию, на основе которой Elasticsearch будет строить механизм автодополнения
     *
     * @return string[]
     */
    public function getSuggest()
    {
        if (null === $this->suggest) {
            $fullName = $this->getName();
            $suggest = explode(' ', $fullName);
            array_unshift($suggest, $fullName);

            /** @var Offer $offer */
            foreach ($this->getOffers() as $offer) {
                $suggest[] = $offer->getSkuId();
                if (\is_array($offer->getBarcodes())) {
                    foreach ($offer->getBarcodes() as $barcode) {
                        $suggest[] = $barcode;
                    }
                }
            }

            $suggest = array_filter(
                $suggest,
                function ($token) {
                    return trim($token) != '' && \strlen($token) >= 3;
                }
            );

            /**
             * в suggest обязательно должен быть массив с числовыми индексами от 0 до count($suggest)-1 ,
             * иначе json_encode в недрах пакета elastica превратит его в объект, а Elasticsearch упадёт с ошибкой
             * `java.lang.IllegalArgumentException: unknown field name [0], must be one of [input, weight, contexts]`
             */
            $this->suggest = array_values(array_unique($suggest));
        }

        return $this->suggest;
    }

    /**
     * Проверяет, под заказ данный товар или нет
     *
     * @return bool
     */
    public function isByRequest(): bool
    {
        $result = true;
        /** @var Offer $offer */
        foreach ($this->getOffers() as $offer) {
            $result &= $offer->isByRequest();
        }

        return $result;
    }

    /*
     * @internal Специально для Elasitcsearch храним коллецию без ключей, т.к. ассоциативный массив с торговыми
     * предложениями туда передавать нельзя: это будет объект, а не массив объектов.
     *
     * @return Collection|Offer[]
     */
    public function getOffers(): Collection
    {
        if (null === $this->offers) {
            $this->offers = new ArrayCollection(
                array_values(
                    (new OfferQuery())->withFilterParameter('=PROPERTY_CML2_LINK', $this->getId())
                                      ->exec()
                                      ->toArray()
                )
            );
        }

        return $this->offers;
    }

    /**
     * @return string
     */
    public function getPackingCombination(): string
    {
        return $this->PROPERTY_PACKING_COMBINATION;
    }

    /**
     * @param string $packingCombination
     *
     * @return static
     */
    public function setPackingCombination(string $packingCombination)
    {
        $this->PROPERTY_PACKING_COMBINATION = $packingCombination;

        return $this;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        // @todo возвращать коллекцию акций, когда они будут реализованы
        return [];
    }

    /**
     * @return bool
     */
    public function hasActions(): bool
    {
        return !empty($this->getActions());
    }

    /**
     * @return array
     */
    public function getDeliveryAvailability(): array
    {
        // @todo учитывать региональные ограничения
        $result = [self::AVAILABILITY_PICKUP];
        if (!($this->isLowTemperatureRequired() || $this->isRefrigerated())) {
            $result[] = self::AVAILABILITY_DELIVERY;
        }
        if ($this->isByRequest()) {
            $result[] = self::AVAILABILITY_BY_REQUEST;
        }

        return $result;
    }
}
