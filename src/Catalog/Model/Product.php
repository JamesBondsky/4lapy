<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Catalog\Model;

use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
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
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Search\Model\HitMetaInfoAwareInterface;
use FourPaws\Search\Model\HitMetaInfoAwareTrait;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class Product
 *
 * @package FourPaws\Catalog\Model
 */
class Product extends IblockElement implements HitMetaInfoAwareInterface
{
    use HitMetaInfoAwareTrait;

    public const AVAILABILITY_DELIVERY = 'delivery';
    public const AVAILABILITY_PICKUP = 'pickup';
    public const AVAILABILITY_BY_REQUEST = 'byRequest';

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
     * @var array
     */
    protected $PROPERTY_COMPOSITION = [];

    /**
     * @var TextContent
     */
    protected $composition;

    /**
     * @var array
     */
    protected $PROPERTY_NORMS_OF_USE = [];

    /**
     * @var TextContent
     */
    protected $normsOfUse;

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
     * @Accessor(getter="getFullDeliveryAvailabilityForFilter")
     * @Groups({"elastic"})
     */
    protected $deliveryAvailability;

    /**
     * @var string
     */
    protected $PROPERTY_PACKING_COMBINATION = '';

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
    protected $PROPERTY_TRANSPORT_ONLY_REFRIGERATOR = false;

    /**
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_DC_SPECIAL_AREA_STORAGE = false;

    /**
     * @var array
     */
    protected $fullDeliveryAvailability;

    /**
     * BitrixArrayItemBase constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $fields['PROPERTY_SPECIFICATIONS_VALUE'] = $fields['~PROPERTY_SPECIFICATIONS_VALUE'] ?? [
                'TYPE' => '',
                'TEXT' => '',
            ];
        $fields['PROPERTY_COMPOSITION_VALUE'] = $fields['~PROPERTY_COMPOSITION_VALUE'] ?? [
                'TYPE' => '',
                'TEXT' => '',
            ];
        $fields['PROPERTY_NORMS_OF_USE_VALUE'] = $fields['~PROPERTY_NORMS_OF_USE_VALUE'] ?? [
                'TYPE' => '',
                'TEXT' => '',
            ];
        parent::__construct($fields);
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function withBrandId(int $id): self
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
    public function withBrand(Brand $brand): self
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
    public function withForWhoXmlIds(array $xmlIds = []): self
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
    public function withPetSizeXmlIds(array $xmlIds): self
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
    public function getCategory(): ?HlbReferenceItem
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
    public function withSapCategoryXmlId(string $xmlId): self
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
    public function getPurpose(): ?HlbReferenceItem
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
    public function withPurposeXmlId(string $xmlId): self
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
    public function withPetAgeXmlIds(array $xmlIds): self
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
    public function withPetAgeAdditionalXmlIds(array $xmlIds): self
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
    public function getPetBreed(): ?HlbReferenceItem
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
    public function withPetBreedXmlId(string $xmlId): self
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
    public function getPetGender(): ?HlbReferenceItem
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
    public function withPetGenderXmlId(string $xmlId): self
    {
        $this->petGender = null;
        $this->PROPERTY_PET_GENDER = $xmlId;
        return $this;
    }

    /**
     * Возвращает признак "товар собственной торговой марки"
     *
     * @return bool
     */
    public function isStm(): bool
    {
        return (bool)$this->PROPERTY_STM;
    }

    /**
     * @param bool $isSTM
     *
     * @return $this
     */
    public function withStm(bool $isSTM = true): self
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
    public function getCountry(): ?HlbReferenceItem
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
    public function withCountryXmlId(string $xmlId): self
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
    public function withTradeNameXmlIds(array $xmlIds): self
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
    public function withMakersXmlIds(array $xmlIds): self
    {
        $this->maker = null;
        $this->PROPERTY_MAKER = $xmlIds;

        return $this;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     *
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
    public function withManagersOfCategoryXmlIds(array $xmlIds): self
    {
        $this->managerOfCategory = null;
        $this->PROPERTY_MANAGER_OF_CATEGORY = $xmlIds;

        return $this;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     *
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
    public function withManufactureMaterialsXmlIds(array $xmlIds): self
    {
        $this->manufactureMaterial = null;
        $this->PROPERTY_MANUFACTURE_MATERIAL = $xmlIds;
        return $this;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     *
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
    public function withClothesSeasonsXmlIds(array $xmlIds): self
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
    public function withLicenseRequired(bool $licenseRequired = false): self
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
    public function withLowTemperatureRequired($isLowTemperature = false): self
    {
        $this->PROPERTY_LOW_TEMPERATURE = $isLowTemperature;

        return $this;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     *
     * @return null|HlbReferenceItem
     */
    public function getPetType(): ?HlbReferenceItem
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
    public function withPetTypeXmlId(string $xmlId): self
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
    public function getPharmaGroup(): ?HlbReferenceItem
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
    public function withPharmaGroupXmlId(string $xmlId): self
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
    public function getFeedSpecification(): ?HlbReferenceItem
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
    public function withFeedSpecificationXmlId(string $xmlId): self
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
    public function withIsFood(bool $isFood = true): self
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
    public function getConsistence(): ?HlbReferenceItem
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
    public function withConsistenceXmlId(string $xmlId): self
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
    public function withFlavourXmlIds(array $xmlIds): self
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
    public function withFeaturesOfIngredientsXmlIds(array $xmlIds): self
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
    public function withProductFormsXmlIds(array $xmlIds): self
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
    public function getTypesOfParasites(): ?HlbReferenceItemCollection
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
    public function getYmlName(): string
    {
        return $this->PROPERTY_YML_NAME;
    }

    /**
     * Возвращает примечания о товаре (sales notes) для Яндекс.Маркет
     *
     * @return string
     */
    public function getSalesNotes(): string
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
    public function getGroupId(): string
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
    public function getGroupName(): string
    {
        return $this->PROPERTY_GROUP_NAME;
    }

    /**
     * Возвращает признак "Произведено по заказу правообладателя"
     *
     * @return bool
     */
    public function isProducedByHolderRequest(): bool
    {
        return (bool)(int)$this->PROPERTY_PRODUCED_BY_HOLDER;
    }

    /**
     * Возвращает технические характеристики товара.
     *
     * @return TextContent
     */
    public function getSpecifications(): TextContent
    {
        if (!($this->specifications instanceof TextContent)) {
            if (empty($this->PROPERTY_SPECIFICATIONS)) {
                $this->PROPERTY_SPECIFICATIONS = ['TYPE' => 'text', 'TEXT' => ''];
            }
            $this->specifications = new TextContent($this->PROPERTY_SPECIFICATIONS);
        }

        return $this->specifications;
    }

    /**
     * Возвращает состав товара.
     *
     * @return TextContent
     */
    public function getComposition(): TextContent
    {
        if (!($this->composition instanceof TextContent)) {
            if (empty($this->PROPERTY_COMPOSITION)) {
                $this->PROPERTY_COMPOSITION = ['TYPE' => 'text', 'TEXT' => ''];
            }
            $this->composition = new TextContent($this->PROPERTY_COMPOSITION);
        }

        return $this->composition;
    }

    /**
     * Возвращает нормы.
     *
     * @return TextContent
     */
    public function getNormsOfUse(): TextContent
    {
        if (!($this->normsOfUse instanceof TextContent)) {
            if (empty($this->PROPERTY_NORMS_OF_USE)) {
                $this->PROPERTY_NORMS_OF_USE = ['TYPE' => 'text', 'TEXT' => ''];
            }
            $this->normsOfUse = new TextContent($this->PROPERTY_NORMS_OF_USE);
        }

        return $this->normsOfUse;
    }

    /**
     * Возвращает информацию, на основе которой Elasticsearch будет строить механизм автодополнения
     *
     * @return string[]
     *
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    public function getSuggest(): array
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
                    return \trim($token) !== '' && \strlen($token) >= 3;
                }
            );

            /**
             * в suggest обязательно должен быть массив с числовыми индексами от 0 до count($suggest)-1 ,
             * иначе json_encode в недрах пакета elastica превратит его в объект, а Elasticsearch упадёт с ошибкой
             * `java.lang.IllegalArgumentException: unknown field name [0], must be one of [input, weight, contexts]`
             */
            $this->suggest = array_values(array_unique($suggest));
        }

        return $this->suggest ?? [];
    }

    /**
     * Проверяет, под заказ данный товар или нет
     *
     * @return bool
     *
     * @throws ObjectNotFoundException
     * @throws NotSupportedException
     * @throws LoaderException
     * @throws ApplicationCreateException
     */
    public function isByRequest(): bool
    {
        $result = false;
        /** @var Offer $offer */
        foreach ($this->getOffers() as $offer) {
            $result |= $offer->isByRequest();
        }

        return $result;
    }

    /**
     * @internal Специально для Elasitcsearch храним коллецию без ключей, т.к. ассоциативный массив с торговыми
     * предложениями туда передавать нельзя: это будет объект, а не массив объектов.
     *
     * @param bool $skipZeroPrice
     * @param bool $reload
     * @param array $additionalFilter
     * @return Collection
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    public function getOffers($skipZeroPrice = true, bool $reload = false, array $additionalFilter = []): Collection
    {
        if (null === $this->offers || $reload) {
            $offerQuery =  (new OfferQuery())->withFilterParameter('=PROPERTY_CML2_LINK', $this->getId())
                ->withOrder(['CATALOG_WEIGHT' => 'ASC']);
            if(!empty($additionalFilter)){
                foreach ($additionalFilter as $key => $value) {
                    $offerQuery->withFilterParameter($key, $value);
                }
            }
            $offers = new ArrayCollection(array_values($offerQuery->exec()->toArray()));

            /**
             * @var Offer $offer
             */
            foreach ($offers as $i => $offer) {
                if ($skipZeroPrice && !$offer->getPrice()) {
                    unset($offers[$i]);
                    continue;
                }
                try {
                    $offer->setProduct($this);
                } catch (\InvalidArgumentException $e) {
                    /**
                     * Никогда не должна возникнуть такая ситуация
                     */
                }
            }

            $this->offers = $offers;
        }

        return $this->offers;
    }

    /**
     * @param array|ArrayCollection $offers
     */
    public function setOffers($offers)
    {
        if (!($offers instanceof ArrayCollection) && \is_array($offers)) {
            $offers = new ArrayCollection($offers);
        }
        $this->offers = $offers;
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
    public function withPackingCombination(string $packingCombination)
    {
        $this->PROPERTY_PACKING_COMBINATION = $packingCombination;

        return $this;
    }

    /**
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @return bool
     */
    public function hasActions(): bool
    {
        $result = false;
        /** @var Offer $offer */
        foreach ($this->getOffers() as $offer) {
            $result |= $offer->getOldPrice() > $offer->getPrice();
        }

        return $result;
    }

    /**
     * @throws ApplicationCreateException
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @return array
     */
    public function getFullDeliveryAvailability(): array
    {
        if (null === $this->fullDeliveryAvailability) {
            $isByRequest = $this->isByRequest();
            $canDeliver = $this->isLowTemperatureRequired() || $this->isTransportOnlyRefrigerator()
                || $this->isDeliveryAreaRestrict();
            /** @var DeliveryService $deliveryService */
            $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
            $zones = array_keys($deliveryService->getAllZones());
            foreach ($zones as $zone) {
                $result = [];
                foreach ($deliveryService->getByZone($zone) as $deliveryCode) {
                    switch (true) {
                        case $canDeliver && \in_array($deliveryCode, DeliveryService::DELIVERY_CODES, true):
                            $result[] = static::AVAILABILITY_DELIVERY;
                            break;
                        case $deliveryCode === DeliveryService::INNER_PICKUP_CODE:
                        case $canDeliver && $deliveryCode === DeliveryService::DPD_PICKUP_CODE:
                            $result[] = static::AVAILABILITY_PICKUP;
                            break;
                    }
                }
                if ($isByRequest && !empty($result)) {
                    $result[] = static::AVAILABILITY_BY_REQUEST;
                }
                $this->fullDeliveryAvailability[$zone] = $result;
            }
        }

        return $this->fullDeliveryAvailability;
    }

    /**
     * @throws ApplicationCreateException
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @return array
     */
    public function getFullDeliveryAvailabilityForFilter(): array
    {
        $result = [];
        /**
         * @var string $zone
         * @var array $deliveries
         */
        foreach ($this->getFullDeliveryAvailability() as $zone => $deliveries) {
            foreach ($deliveries as $delivery) {
                $result[] = $zone . '_' . $delivery;
            }
        }

        return $result;
    }

    /**
     * @throws ApplicationCreateException
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @return array
     */
    public function getDeliveryAvailability(): array
    {
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        return $this->getFullDeliveryAvailability()[$deliveryService->getCurrentDeliveryZone()] ?? [];
    }

    /**
     * @throws ApplicationCreateException
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @return bool
     */
    public function isDeliveryAvailable(): bool
    {
        return \in_array(
            self::AVAILABILITY_DELIVERY,
            $this->getDeliveryAvailability(),
            true
        );
    }

    /**
     * @throws ApplicationCreateException
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @return bool
     */
    public function isPickupAvailable(): bool
    {
        return \in_array(
            self::AVAILABILITY_PICKUP,
            $this->getDeliveryAvailability(),
            true
        );
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

    /**
     * @return string
     */
    public function getWeightCapacityPacking(): string
    {
        return $this->PROPERTY_WEIGHT_CAPACITY_PACKING;
    }
}
