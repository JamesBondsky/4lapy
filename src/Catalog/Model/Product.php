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

    /**
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_TRANSPORT_ONLY_REFRIGERATOR = false;

    /**
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_DC_SPECIAL_AREA_STORAGE = false;

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
        $this->PROPERTY_BRAND_NAME = '';

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
        if (!$this->PROPERTY_BRAND_NAME && $this->PROPERTY_BRAND) {
            $this->PROPERTY_BRAND_NAME = $this->getBrand()->getName();
        }
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
    public function getForWho(): HlbReferenceItemCollection
    {
        if (null === $this->forWho) {
            $this->forWho = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.forwho'),
                $this->getForWhoXmlIds()
            );
        }

        return $this->forWho;
    }

    /**
     * @param array $xmlIds
     *
     * @return $this
     */
    public function withForWhoXmlIds(array $xmlIds = [])
    {
        $this->PROPERTY_FOR_WHO = $xmlIds;
        $this->forWho = null;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getForWhoXmlIds(): array
    {
        $this->PROPERTY_FOR_WHO = $this->PROPERTY_FOR_WHO ?: [];
        return $this->PROPERTY_FOR_WHO;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getPetSize(): HlbReferenceItemCollection
    {
        if (null === $this->petSize) {
            $this->petSize = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.petsize'),
                $this->getPetSizeXmlIds()
            );
        }

        return $this->petSize;
    }

    /**
     * @return array|string[]
     */
    public function getPetSizeXmlIds(): array
    {
        $this->PROPERTY_PET_SIZE = $this->PROPERTY_PET_SIZE ?: [];
        return $this->PROPERTY_PET_SIZE;
    }

    /**
     * @param array $xmlIds
     *
     * @return $this
     */
    public function withPetSizeXmlIds(array $xmlIds)
    {
        $this->petSize = null;
        $this->PROPERTY_PET_SIZE = $xmlIds;
        return $this;
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
                $this->getSapCategoryXmlId()
            );
        }

        return $this->category;
    }

    /**
     * @return string
     */
    public function getSapCategoryXmlId(): string
    {
        $this->PROPERTY_CATEGORY = $this->PROPERTY_CATEGORY ?: '';
        return $this->PROPERTY_CATEGORY;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withSapCategoryXmlId(string $xmlId)
    {
        $this->category = null;
        $this->PROPERTY_CATEGORY = $xmlId;
        return $this;
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
                $this->getPurposeXmlId()
            );
        }

        return $this->purpose;
    }

    /**
     * @return string
     */
    public function getPurposeXmlId(): string
    {
        $this->PROPERTY_PURPOSE = $this->PROPERTY_PURPOSE ?: '';
        return $this->PROPERTY_PURPOSE;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withPurposeXmlId(string $xmlId)
    {
        $this->purpose = null;
        $this->PROPERTY_PURPOSE = $xmlId;
        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getPetAge(): HlbReferenceItemCollection
    {
        if (null === $this->petAge) {
            $this->petAge = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.petage'),
                $this->getPetAgeXmlIds()
            );
        }

        return $this->petAge;
    }

    /**
     * @return array
     */
    public function getPetAgeXmlIds(): array
    {
        $this->PROPERTY_PET_AGE = $this->PROPERTY_PET_AGE ?: [];
        return $this->PROPERTY_PET_AGE;
    }

    /**
     * @param array $xmlIds
     *
     * @return $this
     */
    public function withPetAgeXmlIds(array $xmlIds)
    {
        $this->petAge = null;
        $this->PROPERTY_PET_AGE = $xmlIds;
        return $this;
    }


    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getPetAgeAdditional(): HlbReferenceItemCollection
    {
        if (null === $this->petAgeAdditional) {
            $this->petAgeAdditional = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.petageadditional'),
                $this->getPetAgeAdditionalXmlIds()
            );
        }

        return $this->petAgeAdditional;
    }

    /**
     * @return array|string[]
     */
    public function getPetAgeAdditionalXmlIds(): array
    {
        $this->PROPERTY_PET_AGE_ADDITIONAL = $this->PROPERTY_PET_AGE_ADDITIONAL ?: [];
        return $this->PROPERTY_PET_AGE_ADDITIONAL;
    }

    /**
     * @param array $xmlIds
     *
     * @return $this
     */
    public function withPetAgeAdditionalXmlIds(array $xmlIds)
    {
        $this->petAgeAdditional = null;
        $this->PROPERTY_PET_AGE_ADDITIONAL = $xmlIds;
        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getPetBreed()
    {
        if ((null === $this->petBreed) && $this->getPetBreedXmlId()) {
            $this->petBreed = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.petbreed'),
                $this->getPetBreedXmlId()
            );
        }

        return $this->petBreed;
    }

    /**
     * @return string
     */
    public function getPetBreedXmlId(): string
    {
        $this->PROPERTY_PET_BREED = $this->PROPERTY_PET_BREED ?: '';
        return $this->PROPERTY_PET_BREED;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withPetBreedXmlId(string $xmlId)
    {
        $this->petBreed = null;
        $this->PROPERTY_PET_BREED = $xmlId;
        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getPetGender()
    {
        if ((null === $this->petGender) && $this->getPetGenderXmlId()) {
            $this->petGender = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.petgender'),
                $this->getPetGenderXmlId()
            );
        }

        return $this->petGender;
    }

    /**
     * @return string
     */
    public function getPetGenderXmlId(): string
    {
        $this->PROPERTY_PET_GENDER = $this->PROPERTY_PET_GENDER ?: '';
        return $this->PROPERTY_PET_GENDER;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withPetGenderXmlId(string $xmlId)
    {
        $this->petGender = null;
        $this->PROPERTY_PET_GENDER = $xmlId;
        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getLabels(): HlbReferenceItemCollection
    {
        if (null === $this->label) {
            $this->label = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.label'),
                $this->getLabelsXmlId()
            );
            /*
             * TODO Добавить динамический запрос шильдиков по акциям, в которых в данном регионе участвует этот продукт
             */

            //TODO Сделать, чтобы это была отдельная коллекция объектов "Шильдик", а не просто элемент справочника.
        }

        return $this->label;
    }

    /**
     * @return array|string[]
     */
    public function getLabelsXmlId(): array
    {
        $this->PROPERTY_LABEL = $this->PROPERTY_LABEL ?: [];
        return $this->PROPERTY_LABEL;
    }

    /**
     * @param array $xmlIds
     *
     * @return $this
     */
    public function withLabelsXmlIds(array $xmlIds)
    {
        $this->label = null;
        $this->PROPERTY_LABEL = $xmlIds;
        return $this;
    }

    /**
     * Возвращает признак "товар собственной торговой марки"
     *
     * @return bool
     */
    public function isSTM(): bool
    {
        return (bool)$this->PROPERTY_STM;
    }

    /**
     * @param bool $isSTM
     *
     * @return $this
     */
    public function withSTM(bool $isSTM = true)
    {
        $this->PROPERTY_STM = $isSTM;
        return $this;
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
     * @return string
     */
    public function getCountryXmlId(): string
    {
        $this->PROPERTY_COUNTRY = $this->PROPERTY_COUNTRY ?: '';
        return $this->PROPERTY_COUNTRY;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withCountryXmlId(string $xmlId)
    {
        $this->country = null;
        $this->PROPERTY_COUNTRY = $xmlId;
        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getTradeNames(): HlbReferenceItemCollection
    {
        if (null === $this->tradeName) {
            $this->tradeName = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.tradename'),
                $this->getTradeNameXmlIds()
            );
        }

        return $this->tradeName;
    }

    /**
     * @return array|string[]
     */
    public function getTradeNameXmlIds(): array
    {
        $this->PROPERTY_TRADE_NAME = $this->PROPERTY_TRADE_NAME ?: [];
        return $this->PROPERTY_TRADE_NAME;
    }

    /**
     * @param array $xmlIds
     *
     * @return $this
     */
    public function withTradeNameXmlIds(array $xmlIds)
    {
        $this->tradeName = null;
        $this->PROPERTY_TRADE_NAME = $xmlIds;
        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getMakers(): HlbReferenceItemCollection
    {
        if (null === $this->maker) {
            $this->maker = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.maker'),
                $this->getMakersXmlIds()
            );
        }

        return $this->maker;
    }

    /**
     * @return array|string[]
     */
    public function getMakersXmlIds(): array
    {
        $this->PROPERTY_MAKER = $this->PROPERTY_MAKER ?: [];
        return $this->PROPERTY_MAKER;
    }

    /**
     * @param array $xmlIds
     *
     * @return $this
     */
    public function withMakersXmlIds(array $xmlIds)
    {
        $this->maker = null;
        $this->PROPERTY_MAKER = $xmlIds;
        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getManagersOfCategory(): HlbReferenceItemCollection
    {
        if (null === $this->managerOfCategory) {
            $this->managerOfCategory = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.categorymanager'),
                $this->getManagersOfCategoryXmlIds()
            );
        }

        return $this->managerOfCategory;
    }

    /**
     * @return array|string[]
     */
    public function getManagersOfCategoryXmlIds(): array
    {
        $this->PROPERTY_MANAGER_OF_CATEGORY = $this->PROPERTY_MANAGER_OF_CATEGORY ?: [];
        return $this->PROPERTY_MANAGER_OF_CATEGORY;
    }

    /**
     * @param array $xmlIds
     *
     * @return $this
     */
    public function withManagersOfCategoryXmlIds(array $xmlIds)
    {
        $this->managerOfCategory = null;
        $this->PROPERTY_MANAGER_OF_CATEGORY = $xmlIds;
        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getManufactureMaterials(): HlbReferenceItemCollection
    {
        if (null === $this->manufactureMaterial) {
            $this->manufactureMaterial = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.material'),
                $this->getManufactureMaterialsXmlIds()
            );
        }

        return $this->manufactureMaterial;
    }

    /**
     * @return array|string[]
     */
    public function getManufactureMaterialsXmlIds(): array
    {
        $this->PROPERTY_MANUFACTURE_MATERIAL = $this->PROPERTY_MANUFACTURE_MATERIAL ?: [];
        return $this->PROPERTY_MANUFACTURE_MATERIAL;
    }

    /**
     * @param array $xmlIds
     *
     * @return $this
     */
    public function withManufactureMaterialsXmlIds(array $xmlIds)
    {
        $this->manufactureMaterial = null;
        $this->PROPERTY_MANUFACTURE_MATERIAL = $xmlIds;
        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return HlbReferenceItemCollection
     */
    public function getClothesSeasons(): HlbReferenceItemCollection
    {
        if (null === $this->seasonClothes) {
            $this->seasonClothes = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.season'),
                $this->getClothesSeasonsXmlIds()
            );
        }

        return $this->seasonClothes;
    }

    /**
     * @return array|string[]
     */
    public function getClothesSeasonsXmlIds(): array
    {
        $this->PROPERTY_SEASON_CLOTHES = $this->PROPERTY_SEASON_CLOTHES ?: [];
        return $this->PROPERTY_SEASON_CLOTHES;
    }

    /**
     * @param array $xmlIds
     *
     * @return $this
     */
    public function withClothesSeasonsXmlIds(array $xmlIds)
    {
        $this->seasonClothes = null;
        $this->PROPERTY_SEASON_CLOTHES = $xmlIds;
        return $this;
    }

    /**
     * Возвращает признак "Требуется лицензия"
     *
     * @return bool
     */
    public function isLicenseRequired(): bool
    {
        return (bool)$this->PROPERTY_LICENSE;
    }

    /**
     * @param bool $licenseRequired
     *
     * @return $this
     */
    public function withLicenseRequired(bool $licenseRequired = true)
    {
        $this->PROPERTY_LICENSE = $licenseRequired;
        return $this;
    }

    /**
     * Возвращает признак "Требуется хранение при низкой температуре"
     *
     * @return bool
     */
    public function isLowTemperatureRequired(): bool
    {
        return (bool)$this->PROPERTY_LOW_TEMPERATURE;
    }

    /**
     * @param bool $isLowTemperature
     *
     * @return $this
     */
    public function withLowTemperatureRequired($isLowTemperature = true)
    {
        $this->PROPERTY_LOW_TEMPERATURE = $isLowTemperature;
        return $this;
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
     * @return string
     */
    public function getPetTypeXmlId(): string
    {
        $this->PROPERTY_PET_TYPE = $this->PROPERTY_PET_TYPE ?: '';
        return $this->PROPERTY_PET_TYPE;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withPetTypeXmlId(string $xmlId)
    {
        $this->petType = null;
        $this->PROPERTY_PET_TYPE = $xmlId;
        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getPharmaGroup()
    {
        if ((null === $this->pharmaGroup) && $this->getPharmaGroupXmlId()) {
            $this->pharmaGroup = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.pharmagroup'),
                $this->getPharmaGroupXmlId()
            );
        }

        return $this->pharmaGroup;
    }

    /**
     * @return string
     */
    public function getPharmaGroupXmlId(): string
    {
        $this->PROPERTY_PHARMA_GROUP = $this->PROPERTY_PHARMA_GROUP ?: '';
        return $this->PROPERTY_PHARMA_GROUP;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withPharmaGroupXmlId(string $xmlId)
    {
        $this->pharmaGroup = null;
        $this->PROPERTY_PHARMA_GROUP = $xmlId;
        return $this;
    }

    /**
     * @todo Ацессоры продолжить
     */

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
        if ((null === $this->feedSpecification) && $this->getFeedSpecificationXmlId()) {
            $this->feedSpecification = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.feedspec'),
                $this->getFeedSpecificationXmlId()
            );
        }

        return $this->feedSpecification;
    }


    /**
     * @return string
     */
    public function getFeedSpecificationXmlId(): string
    {
        $this->PROPERTY_FEED_SPECIFICATION = $this->PROPERTY_FEED_SPECIFICATION ?: '';
        return $this->PROPERTY_FEED_SPECIFICATION;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withFeedSpecificationXmlId(string $xmlId)
    {
        $this->feedSpecification = null;
        $this->PROPERTY_FEED_SPECIFICATION = $xmlId;
        return $this;
    }

    /**
     * Возвращает признак "Еда", т.е. является ли продукт съедобным.
     *
     * @return bool
     */
    public function isFood(): bool
    {
        return (bool)$this->PROPERTY_FOOD;
    }

    /**
     * @param bool $isFood
     *
     * @return $this
     */
    public function withIsFood(bool $isFood = true)
    {
        $this->PROPERTY_FOOD = $isFood;
        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getConsistence()
    {
        if ((null === $this->consistence) && $this->getConsistenceXmlId()) {
            $this->consistence = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.consistence'),
                $this->getConsistenceXmlId()
            );
        }

        return $this->consistence;
    }

    /**
     * @return string
     */
    public function getConsistenceXmlId(): string
    {
        $this->PROPERTY_CONSISTENCE = $this->PROPERTY_CONSISTENCE ?: '';
        return $this->PROPERTY_CONSISTENCE;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withConsistenceXmlId(string $xmlId)
    {
        $this->consistence = null;
        $this->PROPERTY_CONSISTENCE = $xmlId;
        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return Collection|HlbReferenceItem[]
     */
    public function getFlavour(): Collection
    {
        if (null === $this->flavour) {
            $this->flavour = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.flavour'),
                $this->getFlavourXmlIds()
            );
        }

        return $this->flavour;
    }

    /**
     * @return array|string[]
     */
    public function getFlavourXmlIds(): array
    {
        $this->PROPERTY_FLAVOUR = $this->PROPERTY_FLAVOUR ?: [];
        return $this->PROPERTY_FLAVOUR;
    }

    /**
     * @param array $xmlIds
     *
     * @return $this
     */
    public function withFlavourXmlIds(array $xmlIds)
    {
        $this->flavour = null;
        $this->PROPERTY_FLAVOUR = $xmlIds;
        return $this;
    }

    /**
     * Возвращает особенности ингридиентов
     *
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return Collection|HlbReferenceItem[]
     */
    public function getFeaturesOfIngredients(): Collection
    {
        if (null === $this->featuresOfIngredients) {
            $this->featuresOfIngredients = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.ingridientfeatures'),
                $this->getFeaturesOfIngredientsXmlIds()
            );
        }

        return $this->featuresOfIngredients;
    }

    /**
     * @return array|string[]
     */
    public function getFeaturesOfIngredientsXmlIds(): array
    {
        $this->PROPERTY_FEATURES_OF_INGREDIENTS = $this->PROPERTY_FEATURES_OF_INGREDIENTS ?: [];
        return $this->PROPERTY_FEATURES_OF_INGREDIENTS;
    }

    /**
     * @param array $xmlIds
     *
     * @return $this
     */
    public function withFeaturesOfIngredientsXmlIds(array $xmlIds)
    {
        $this->featuresOfIngredients = null;
        $this->PROPERTY_FEATURES_OF_INGREDIENTS = $xmlIds;
        return $this;
    }

    /**
     * Возвращает формы выпуска продукта
     *
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return Collection|HlbReferenceItem[]
     */
    public function getProductForms(): Collection
    {
        if (null === $this->productForm) {
            $this->productForm = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.productform'),
                $this->getProductFormsXmlIds()
            );
        }

        return $this->productForm;
    }

    /**
     * @return array
     */
    public function getProductFormsXmlIds(): array
    {
        $this->PROPERTY_PRODUCT_FORM = $this->PROPERTY_PRODUCT_FORM ?: [];
        return $this->PROPERTY_PRODUCT_FORM;
    }

    /**
     * @param array $xmlIds
     *
     * @return $this
     */
    public function withProductFormsXmlIds(array $xmlIds)
    {
        $this->productForm = null;
        $this->PROPERTY_PRODUCT_FORM = $xmlIds;
        return $this;
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

    /**
     * @return bool
     */
    public function isTransportOnlyRefrigerator(): bool
    {
        return $this->PROPERTY_TRANSPORT_ONLY_REFRIGERATOR;
    }

    /**
     * @param bool $onlyRefrigerator
     *
     * @return Product
     */
    public function withTransportOnlyRefrigerator(bool $onlyRefrigerator = true): Product
    {
        $this->PROPERTY_TRANSPORT_ONLY_REFRIGERATOR = $onlyRefrigerator;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDeliveryAreaRestrict(): bool
    {
        return $this->PROPERTY_DC_SPECIAL_AREA_STORAGE;
    }

    /**
     * @param bool $restrict
     *
     * @return Product
     */
    public function withDeliveryAreaRestrict(bool $restrict = true): Product
    {
        $this->PROPERTY_DC_SPECIAL_AREA_STORAGE = $restrict;
        return $this;
    }
}
