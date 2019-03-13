<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Catalog\Model;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Catalog\Product\Basket as BitrixBasket;
use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\Order;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Service\UserFieldEnumService;
use FourPaws\BitrixOrm\Collection\ImageCollection;
use FourPaws\BitrixOrm\Collection\ResizeImageCollection;
use FourPaws\BitrixOrm\Collection\ShareCollection;
use FourPaws\BitrixOrm\Model\CatalogProduct;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\BitrixOrm\Model\Image;
use FourPaws\BitrixOrm\Model\Interfaces\ResizeImageInterface;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\BitrixOrm\Query\CatalogProductQuery;
use FourPaws\BitrixOrm\Query\ShareQuery;
use FourPaws\BitrixOrm\Utils\ReferenceUtils;
use FourPaws\Catalog\Collection\PriceCollection;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\CatalogBundle\Service\BrandService;
use FourPaws\CatalogBundle\Service\CatalogGroupService;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\WordHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Service\BonusService;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\StoreBundle\Collection\StockCollection;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\StockService;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Service\UserService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\StoreBundle\Entity\Store;
use InvalidArgumentException;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class Offer
 *
 * @package FourPaws\Catalog\Model
 */
class Offer extends IblockElement
{
    /**
     *
     */
    public const SIMPLE_SHARE_SALE_CODE = 'VKA0';

    public const SIMPLE_SHARE_DISCOUNT_CODE = 'ZRBT';

    public const PACKAGE_LABEL_TYPE_SIZE = 'SIZE';

    public const PACKAGE_LABEL_TYPE_VOLUME = 'VOLUME';

    public const PACKAGE_LABEL_TYPE_WEIGHT = 'WEIGHT';

    public const CATALOG_GROUP_ID_BASE = 2;

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
     * @var int
     * @Type("int")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_CML2_LINK = 0;

    /**
     * @var Product
     * @Type("FourPaws\Catalog\Model\Product")
     */
    protected $product;

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_COLOUR = '';

    /**
     * @var HlbReferenceItem
     */
    protected $colour;

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_VOLUME_REFERENCE = '';

    /**
     * @var HlbReferenceItem
     */
    protected $volumeReference;

    /**
     * @var float
     * @Type("float")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_VOLUME = 0.0;

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_CLOTHING_SIZE = '';

    /**
     * @var HlbReferenceItem
     */
    protected $clothingSize;

    /**
     * @Type("array")
     * @Groups({"elastic"})
     * @var int[]
     */
    protected $PROPERTY_IMG = [];

    /**
     * @var string[]
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_BARCODE = [];

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_KIND_OF_PACKING = '';

    /**
     * @var HlbReferenceItem
     */
    protected $kindOfPacking;

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_SEASON_YEAR = '';

    /**
     * @var HlbReferenceItem
     */
    protected $seasonYear;

    /**
     * @var int
     * @Type("int")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_MULTIPLICITY = 0;

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_REWARD_TYPE = '';

    /**
     * @var HlbReferenceItem
     */
    protected $rewardType;

    /**
     * @var string
     */
    protected $PROPERTY_COLOUR_COMBINATION = '';

    /**
     * @var string
     */
    protected $PROPERTY_FLAVOUR_COMBINATION = '';

    /**
     * @var string
     */
    protected $PROPERTY_OLD_URL = '';

    /**
     * @var bool
     * @Type("bool")
     */
    protected $PROPERTY_BONUS_EXCLUDE = false;

    /**
     * Цена по акции - простая акция из SAP
     *
     * @var float
     * @Type("float")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_PRICE_ACTION = 0;

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_COND_FOR_ACTION = '';

    /**
     * Размер скидки на товар - простая акция из SAP
     *
     * @var float
     * @Type("float")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_COND_VALUE = 0;

    /**
     * @Type("float")
     * @Groups({"elastic"})
     * @Accessor(getter="getPrice", setter="withPrice")
     * @var float
     */
    protected $price = 0;

    /**
     * @var Collection
     * @Type("ArrayCollection<FourPaws\Catalog\Model\Price>")
     * @Accessor(getter="getPrices")
     * @Groups({"elastic"})
     */
    protected $prices;

    /**
     * @var float
     * @Type("float")
     * @Groups({"elastic"})
     * @Accessor(getter="getOldPrice", setter="withOldPrice")
     */
    protected $oldPrice = 0;

    /**
     * @var bool
     */
    protected $isByRequest;

    /**
     * @var bool
     */
    protected $isDeliverable;

    /**
     * @var bool
     */
    protected $isPickupAvailable;

    /**
     * @Type("string")
     * @Groups({"elastic"})
     * @var string
     */
    protected $currency = '';

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     * @Accessor(getter="getCatalogVatId", setter="withCatalogVatId")
     */
    protected $VAT_ID = '2';

    /**
     * @var CatalogProduct
     */
    protected $catalogProduct;

    /**
     * @var int
     */
    protected $discount = 0;

    /**
     * @Serializer\Expose()
     * @Groups({"elastic"})
     * @Type("ArrayCollection<FourPaws\BitrixOrm\Model\Image>")
     * @Accessor(getter="getImages", setter="withImages")
     * @var Collection|Image[]
     */
    protected $images;

    /**
     * @var Collection|Image[]
     */
    protected $resizeImages;

    /**
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_IS_HIT = false;

    /**
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_IS_NEW = false;

    /**
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_IS_SALE = false;

    /**
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_IS_POPULAR = false;

    /**
     * @var string
     */
    protected $link = '';

    /**
     * @var StockCollection
     */
    protected $stocks;

    /**
     * @var StockCollection
     * @Type("array<string>")
     * @Accessor(getter="getAllStocksForFilter")
     * @Groups({"elastic"})
     */
    protected $allStocks;

    /**
     * @var bool
     */
    protected $isCounted = false;

    /**
     * @var int
     */
    protected $quantity;

    /**
     * @var int
     */
    protected $deliverableQuantity;

    /**
     * @var ShareCollection
     */
    protected $share;

    /**
     * Приоритетный процент начисленных бонусов от стоимости товара
     * @var int
     */
    protected $bonusPercent;

    /**
     * @var string
     * @Type("array<string>")
     * @Groups({"elastic"})
     * @Accessor(getter="getAvailableStores")
     */
    protected $availableStores = [];

    /**
     * @var array
     * @Type("array")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_REGION_DISCOUNTS = [];

    /**
     * @var array|false
     */
    protected $regionDiscount;

    /**
     * @var int
     */
    protected $catalogGroupId;

    /**
     * Offer constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        parent::__construct($fields);

        if (isset($fields['CATALOG_PRICE_2'])) {
            $this->price = (float)$fields['CATALOG_PRICE_2'];
        }

        if (isset($fields['CATALOG_CURRENCY_2'])) {
            $this->currency = (string)$fields['CATALOG_CURRENCY_2'];
        }

        if (isset($fields['CATALOG_VAT'])) {
            $this->VAT_ID = (string)$fields['CATALOG_VAT_ID'];
        }
    }

    /**
     * @param Collection|Image[] $images
     *
     * @return static
     */
    public function withImages(Collection $images)
    {
        $this->images = $images;
        $this->resizeImages = null;

        return $this;
    }

    /**
     * @param int $width
     * @param int $height
     *
     * @throws InvalidArgumentException
     * @return Collection|ResizeImageInterface[]
     *
     */
    public function getResizeImages(int $width = 0, int $height = 0): Collection
    {
        if ($this->resizeImages instanceof Collection) {
            if ($width) {
                $this->resizeImages->forAll(
                    function (
                        /** @noinspection PhpUnusedParameterInspection */
                        $key,
                        ResizeImageDecorator $image
                    ) use ($width) {
                        $image->setResizeWidth($width);

                        return true;
                    }
                );
            }

            if ($height) {
                $this->resizeImages->forAll(
                    function (
                        /** @noinspection PhpUnusedParameterInspection */
                        $key,
                        ResizeImageDecorator $image
                    ) use ($height) {
                        $image->setResizeHeight($height);

                        return true;
                    }
                );
            }

            return $this->resizeImages;
        }

        $this->resizeImages = ResizeImageCollection::createFromImageCollection($this->getImages(), $width, $height);

        return $this->resizeImages;
    }

    /**
     * @throws InvalidArgumentException
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        if ($this->images instanceof Collection) {
            return $this->images;
        }

        $this->images = ImageCollection::createFromIds($this->getImagesIds());

        if ($this->images->count() < 1) {
            $this->images = ImageCollection::createNoImageCollection();
        }

        return $this->images;
    }

    /**
     * @return array
     */
    public function getImagesIds(): array
    {
        $this->PROPERTY_IMG = \is_array($this->PROPERTY_IMG) ? $this->PROPERTY_IMG : [];

        return $this->PROPERTY_IMG;
    }

    /**
     * @param Collection $resizeImages
     *
     * @return static
     */
    public function withResizeImages(Collection $resizeImages)
    {
        $this->resizeImages = $resizeImages;

        return $this;
    }

    /**
     * @param int $productId
     *
     * @return $this
     */
    public function withCml2Link(int $productId): self
    {
        $this->PROPERTY_CML2_LINK = $productId;
        $this->product = null;

        return $this;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getColor(): ?HlbReferenceItem
    {
        if ((null === $this->colour) && $this->PROPERTY_COLOUR) {
            $this->colour = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.colour'),
                $this->PROPERTY_COLOUR
            );
        }

        return $this->colour;
    }

    /**
     * @return string
     */
    public function getColourXmlId(): string
    {
        return (string)$this->PROPERTY_COLOUR;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withColourXmlId(string $xmlId): self
    {
        $this->PROPERTY_COLOUR = $xmlId;
        $this->colour = null;

        return $this;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getVolumeReference(): ?HlbReferenceItem
    {
        if ((null === $this->volumeReference) && $this->PROPERTY_VOLUME_REFERENCE) {
            $this->volumeReference =
                ReferenceUtils::getReference(
                    Application::getHlBlockDataManager('bx.hlblock.volume'),
                    $this->PROPERTY_VOLUME_REFERENCE
                );
        }

        return $this->volumeReference;
    }

    /**
     * @return string
     */
    public function getVolumeReferenceXmlId(): string
    {
        return (string)$this->PROPERTY_VOLUME_REFERENCE;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withVolumeReferenceXmlId(string $xmlId): self
    {
        $this->PROPERTY_VOLUME_REFERENCE = $xmlId;
        $this->volumeReference = null;

        return $this;
    }

    /**
     * @return float
     */
    public function getVolume(): float
    {
        return (float)$this->PROPERTY_VOLUME;
    }

    /**
     * @param float $volume
     *
     * @return $this
     */
    public function withVolume(float $volume): self
    {
        $this->PROPERTY_VOLUME = $volume;

        return $this;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getClothingSize(): ?HlbReferenceItem
    {
        if ((null === $this->clothingSize) && $this->PROPERTY_CLOTHING_SIZE) {
            $this->clothingSize =
                ReferenceUtils::getReference(
                    Application::getHlBlockDataManager('bx.hlblock.clothingsize'),
                    $this->PROPERTY_CLOTHING_SIZE
                );
        }

        return $this->clothingSize;
    }

    /**
     * @return string
     */
    public function getClothingSizeXmlId(): string
    {
        return (string)$this->PROPERTY_CLOTHING_SIZE;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withClothingSizeXmlId(string $xmlId): self
    {
        $this->PROPERTY_CLOTHING_SIZE = $xmlId;
        $this->clothingSize = null;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBonusExclude(): bool
    {
        return (bool)(int)$this->PROPERTY_BONUS_EXCLUDE;
    }

    /**
     * @param bool $exclude
     *
     * @return Offer
     */
    public function setBonusExclude(bool $exclude): Offer
    {
        $this->PROPERTY_BONUS_EXCLUDE = $exclude;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getBarcodes(): array
    {
        $this->PROPERTY_BARCODE = \is_array($this->PROPERTY_BARCODE) ? $this->PROPERTY_BARCODE : [];

        return $this->PROPERTY_BARCODE;
    }

    /**
     * @param string[] $barcodes
     *
     * @return $this
     */
    public function withBarcodes(array $barcodes): self
    {
        $barcodes = array_filter(
            $barcodes,
            function ($value) {
                return $value && \is_string($value);
            }
        );
        $this->PROPERTY_BARCODE = $barcodes;

        return $this;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getKindOfPacking(): ?HlbReferenceItem
    {
        if ((null === $this->kindOfPacking) && $this->PROPERTY_KIND_OF_PACKING) {
            $this->kindOfPacking =
                ReferenceUtils::getReference(
                    Application::getHlBlockDataManager('bx.hlblock.packagetype'),
                    $this->getKindOfPackingXmlId()
                );
        }

        return $this->kindOfPacking;
    }

    /**
     * @return string
     */
    public function getKindOfPackingXmlId(): string
    {
        return (string)$this->PROPERTY_KIND_OF_PACKING;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withKindOfPackingXmlId(string $xmlId): self
    {
        $this->kindOfPacking = null;
        $this->PROPERTY_KIND_OF_PACKING = $xmlId;

        return $this;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getSeasonYear(): ?HlbReferenceItem
    {
        if ((null === $this->seasonYear) && $this->PROPERTY_SEASON_YEAR) {
            $this->seasonYear = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.year'),
                $this->getSeasonYearXmlId()
            );
        }

        return $this->seasonYear;
    }

    /**
     * @return string
     */
    public function getSeasonYearXmlId(): string
    {
        return (string)$this->PROPERTY_SEASON_YEAR;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withSeasonYearXmlId(string $xmlId): self
    {
        $this->seasonYear = null;
        $this->PROPERTY_SEASON_YEAR = $xmlId;

        return $this;
    }

    /**
     * @return int
     */
    public function getMultiplicity(): int
    {
        return (int)$this->PROPERTY_MULTIPLICITY;
    }

    /**
     * @param int $multiplicity
     *
     * @return $this
     */
    public function withMultiplicity(int $multiplicity): self
    {
        $this->PROPERTY_MULTIPLICITY = $multiplicity;

        return $this;
    }

    /**
     * Возвращает тип вознаграждения для заводчика.
     *
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getRewardType(): ?HlbReferenceItem
    {
        if ((null === $this->rewardType) && $this->PROPERTY_REWARD_TYPE) {
            $this->rewardType =
                ReferenceUtils::getReference(
                    Application::getHlBlockDataManager('bx.hlblock.rewardtype'),
                    $this->getRewardTypeXmlId()
                );
        }

        return $this->rewardType;
    }

    /**
     * @return string
     */
    public function getRewardTypeXmlId(): string
    {
        return (string)$this->PROPERTY_REWARD_TYPE;
    }

    /**
     * @param string $xmlId
     *
     * @return $this
     */
    public function withRewardTypeXmlId(string $xmlId): self
    {
        $this->rewardType = null;
        $this->PROPERTY_REWARD_TYPE = $xmlId;

        return $this;
    }

    /**
     * @return string
     */
    public function getColourCombination(): string
    {
        return (string)$this->PROPERTY_COLOUR_COMBINATION;
    }

    /**
     * @param string $colourCombination
     *
     * @return $this
     */
    public function withColourCombination(string $colourCombination): self
    {
        $this->PROPERTY_COLOUR_COMBINATION = $colourCombination;

        return $this;
    }

    /**
     * @return string
     */
    public function getFlavourCombination(): string
    {
        return (string)$this->PROPERTY_FLAVOUR_COMBINATION;
    }

    /**
     * @param string $flavourCombination
     *
     * @return $this
     */
    public function withFlavourCombination(string $flavourCombination): self
    {
        $this->PROPERTY_FLAVOUR_COMBINATION = $flavourCombination;

        return $this;
    }

    /**
     * @return string
     */
    public function getOldUrl(): string
    {
        return (string)$this->PROPERTY_OLD_URL;
    }

    /**
     * @param string $oldUrl
     *
     * @return $this
     */
    public function withOldUrl(string $oldUrl): self
    {
        $this->PROPERTY_OLD_URL = $oldUrl;

        return $this;
    }

    /**
     * @return bool
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     */
    public function isByRequest(): bool
    {
        if (null === $this->isByRequest) {
            /** @var StoreService $storeService */
            $storeService = Application::getInstance()->getContainer()->get('store.service');
            $stores = $storeService->getSupplierStores();
            $this->isByRequest = !$this->getAllStocks()->filterByStores($stores)->isEmpty();
        }

        return $this->isByRequest;
    }

    /**
     * @param bool $byRequest
     *
     * @return $this
     */
    public function withByRequest(bool $byRequest): self
    {
        $this->isByRequest = $byRequest;

        return $this;
    }

    /**
     * @return string
     */
    public function getSkuId(): string
    {
        return $this->getXmlId();
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        $this->checkOptimalPriceTmp();

        return $this->price;
    }

    /**
     * Отображаемая цена в каталоге
     *
     * @return float
     */
    public function getCatalogPrice(): float
    {
        return \round($this->getPrice());
    }

    /**
     * @return bool
     */
    public function getPropertyIsHit(): bool
    {
        return $this->PROPERTY_IS_HIT;
    }

    /**
     * @param bool $propertyHit
     *
     * @return Offer
     */
    public function setPropertyIsHit($propertyHit): Offer
    {
        $this->PROPERTY_IS_HIT = $propertyHit;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPropertyIsNew(): bool
    {
        return $this->PROPERTY_IS_NEW;
    }

    /**
     * @param bool $propertyNew
     *
     * @return Offer
     */
    public function setPropertyIsNew($propertyNew): Offer
    {
        $this->PROPERTY_IS_HIT = $propertyNew;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPropertyIsSale(): bool
    {
        return $this->PROPERTY_IS_SALE;
    }

    /**
     * @param bool $propertySale
     *
     * @return Offer
     */
    public function setPropertyIsSale($propertySale): Offer
    {
        $this->PROPERTY_IS_SALE = $propertySale;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPropertyPopular(): bool
    {
        return $this->PROPERTY_IS_POPULAR;
    }

    /**
     * @param bool $PROPERTY_IS_POPULAR
     *
     * @return Offer
     */
    public function setPropertyPopular(bool $PROPERTY_IS_POPULAR): Offer
    {
        $this->PROPERTY_IS_POPULAR = $PROPERTY_IS_POPULAR;

        return $this;
    }

    /**
     * размер скидки в процентах
     *
     * @param float $discount
     *
     * @return static
     */
    public function withDiscount(float $discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * @param float $oldPrice
     *
     * @return $this
     */
    public function withOldPrice(float $oldPrice): self
    {
        $this->oldPrice = $oldPrice;

        return $this;
    }

    /**
     * @param float $price
     *
     * @return static
     */
    public function withPrice(float $price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @param array $ids
     *
     * @return $this
     */
    public function withImagesIds(array $ids): self
    {
        $this->PROPERTY_IMG = $ids;

        return $this;
    }

    /**
     * @return CatalogProduct
     */
    public function getCatalogProduct(): CatalogProduct
    {
        if (null === $this->catalogProduct) {
            $catalogProduct = (new CatalogProductQuery())->withFilter(['ID' => $this->getId()])->exec()->current();
            $this->withCatalogProduct($catalogProduct);
        }

        return $this->catalogProduct;
    }

    /**
     * @param CatalogProduct $catalogProduct
     *
     * @return Offer
     */
    public function withCatalogProduct(CatalogProduct $catalogProduct): Offer
    {
        $this->catalogProduct = $catalogProduct;

        return $this;
    }

    /**
     * @return float
     */
    public function getOldPrice(): float
    {
        $this->checkOptimalPriceTmp();

        return $this->oldPrice;
    }

    /**
     * Отображаемая цена без скидки в каталоге
     *
     * @return float
     */
    public function getCatalogOldPrice(): float
    {
        return \round($this->getOldPrice());
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getCatalogVatId(): string
    {
        return $this->VAT_ID;
    }

    /**
     * @param string $catalogVat
     * @return Offer
     */
    public function withCatalogVatId(string $catalogVat): Offer
    {
        $this->VAT_ID = $catalogVat;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        $this->checkOptimalPriceTmp();

        return $this->discount;
    }

    /**
     * Сумма начисляемых бонусов
     *
     * @param int $percent
     * @param int $quantity
     *
     * @throws NotAuthorizedException
     *
     * @return float
     */
    public function getBonusCount(int $percent, int $quantity = 1): float
    {
        $result = 0;

        if(null === $this->bonusPercent){
            try{
                /** @var UserService $userCurrentUserService*/
                $userCurrentUserService = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
                $currentUser = $userCurrentUserService->getCurrentUser();

                if($currentUser->isOpt() && $this->getProduct()->getBrand()->isBonusOpt()){
                    $this->bonusPercent = BrandService::getBonusOptPercent();
                }
                else{
                    $this->bonusPercent = 0;
                }
            } catch(NotAuthorizedException $e){
                /** просто пропускаем */
            }
        }

        if($this->bonusPercent > 0){
            $percent = $this->bonusPercent;
        }

        if (!$this->isBonusExclude() && !$this->isShare()) {
            $result = $this->getPrice() * $quantity * $percent / 100;
        }

        return $result;
    }

    /**
     * @param int $percent
     * @param int $quantity
     * @param int $precision
     *
     * @return string
     */
    public function getBonusFormattedText(int $percent = 3, int $quantity = 1, int $precision = 2): string
    {
        $bonusText = '';

        $bonus = floor($this->getBonusCount($percent, $quantity));
        if ($bonus > 0) {
            $bonusText = \sprintf(
                '+ %s %s',
                $bonus,
                WordHelper::declension($bonus, ['бонус', 'бонуса', 'бонусов'])
            );
        }

        return $bonusText;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        if (!$this->link) {
            $productUrl = str_replace('#', '%23', $this->getProduct()->getDetailPageUrl());

            $this->link = $productUrl ? \sprintf(
                '%s?offer=%s',
                $productUrl,
                $this->getId()
            ) : 'javascript:void(0)';
        }

        return $this->link;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        if (null === $this->product) {
            $this->product = ProductQuery::getById($this->getCml2Link());

            if ($this->product === null) {
                $this->product = new Product();
            }
        }

        return $this->product;
    }

    /**
     * Optimization: internal current product set
     *
     *
     * @param Product $product
     *
     * @throws InvalidArgumentException
     */
    public function setProduct(Product $product): void
    {
        if ($product->getId() !== $this->getCml2Link()) {
            throw new InvalidArgumentException('Wrong product set');
        }

        $this->product = $product;
    }

    /**
     * @return int
     */
    public function getCml2Link(): int
    {
        return (int)$this->PROPERTY_CML2_LINK;
    }




    /**
     *
     *
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @return int
     */
    public function getQuantity(): int
    {
        if (null === $this->quantity) {
            $this->quantity = $this->getStocks()->getTotalAmount();
        }

        return $this->quantity;
    }

    /**
     * Максимальное доступное для доставки количество товара в текущем местоположении
     * @todo сейчас метод всегда возвращает либо 0 либо 1, а не действительное количество
     * @todo заменить getQuantity() на этот метод после оптимизации расчета доставок
     *
     * @return int
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws LoaderException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     */
    public function getDeliverableQuantity(): int
    {
        if (null === $this->deliverableQuantity) {
            $this->deliverableQuantity = $this->getAvailableAmount();
        }

        return $this->deliverableQuantity;
    }


    /**
     * Доступность оффера для доставки в текущем регионе
     *
     * @return int
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws LoaderException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     */
    public function isDeliverable(): bool
    {
        if (null === $this->isDeliverable) {
            $this->isDeliverable = ($this->getAvailableAmount('', DeliveryService::DELIVERY_CODES) > 0);
        }

        return $this->isDeliverable;
    }

    /**
     * Доступность оффера для самовывоза в текущем регионе
     *
     * @return int
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws LoaderException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     */
    public function isPickupAvailable(): bool
    {
        if (null === $this->isPickupAvailable) {
            $this->isPickupAvailable = ($this->getAvailableAmount('', DeliveryService::PICKUP_CODES) > 0);
        }

        return $this->isPickupAvailable;
    }

    /**
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     */
    public function getAllStocks(): StockCollection
    {
        if (!$this->allStocks) {
            /** @var StockService $stockService */
            $stockService = Application::getInstance()->getContainer()->get(StockService::class);
            $this->withAllStocks($stockService->getStocksByOffer($this));
        }

        return $this->allStocks;
    }

    /**
     * @param StockCollection $allStocks
     *
     * @return Offer
     */
    public function withAllStocks(StockCollection $allStocks): Offer
    {
        $this->allStocks = $allStocks;

        return $this;
    }

    /**
     * @return StockCollection
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws SystemException
     */
    public function getStocks(): StockCollection
    {
        if (!$this->stocks) {
            /** @var StoreService $storeService */
            $storeService = Application::getInstance()->getContainer()->get('store.service');
            if ($this->isByRequest()) {
                $stores = $storeService->getSupplierStores();
            } else {
                $stores = $storeService->getStoresByCurrentLocation();
            }
            $this->withStocks($this->getAllStocks()->filterByStores($stores));
        }

        return $this->stocks;
    }

    /**
     * @param StockCollection $stocks
     *
     * @return Offer
     */
    public function withStocks(StockCollection $stocks): Offer
    {
        $this->stocks = $stocks;

        return $this;
    }

    /**
     * @return array
     * @throws ApplicationCreateException
     * @throws ServiceNotFoundException
     * @throws StoreNotFoundException
     */
    public function getAllStocksForFilter(): array
    {
        return $this->getAllStocks()->getStores(1)->getXmlIds();
    }

    /**
     * Участвует ли товар в акции "Скидка на товар"
     */
    public function isSimpleDiscountAction(): bool
    {
        $regionDiscount = $this->getCurrentRegionDiscount();
        if($regionDiscount){
            return $regionDiscount['cond_value'] > 0 && $regionDiscount['cond_for_action'] === self::SIMPLE_SHARE_DISCOUNT_CODE;
        }
        return $this->PROPERTY_COND_VALUE > 0 && $this->PROPERTY_COND_FOR_ACTION === self::SIMPLE_SHARE_DISCOUNT_CODE;
    }

    /**
     * Участвует ли товар в акции "Цена по акции"
     */
    public function isSimpleSaleAction(): bool
    {
        $regionDiscount = $this->getCurrentRegionDiscount();
        if($regionDiscount){
            return $regionDiscount['price_action'] > 0 && $regionDiscount['cond_for_action'] === self::SIMPLE_SHARE_SALE_CODE;
        }
        return $this->PROPERTY_PRICE_ACTION > 0 && $this->PROPERTY_COND_FOR_ACTION === self::SIMPLE_SHARE_SALE_CODE;
    }

    /**
     * @return bool
     */
    public function hasAction(): bool
    {
        /**
         * @todo
         */
        return false;
    }

    /**
     * @return bool
     */
    public function isHit(): bool
    {
        return $this->getPropertyIsHit();
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->getPropertyIsNew();
    }

    /**
     * @return bool
     */
    public function isSale(): bool
    {
        return $this->getPropertyIsSale();
    }

    /**
     * @return bool
     */
    public function isPopular(): bool
    {
        return $this->getPropertyPopular();
    }

    /**
     * @return bool
     */
    public function isShare(): bool
    {
        return !$this->getShare()->isEmpty();
    }

    /**
     * @return ShareCollection
     */
    public function getShare(): ShareCollection
    {
        if ($this->share === null) {
            $this->share = (new ShareQuery())->withOrder(['SORT' => 'ASC', 'ACTIVE_FROM' => 'DESC'])->withFilter(
                [
                    'ACTIVE'            => 'Y',
                    'ACTIVE_DATE'       => 'Y',
                    'PROPERTY_PRODUCTS' => $this->getXmlId(),
                ]
            )->withSelect(
                [
                    'ID',
                    'NAME',
                    'IBLOCK_ID',
                    'PREVIEW_TEXT',
                    'DATE_ACTIVE_FROM',
                    'DATE_ACTIVE_TO',
                ]
            )->exec();
        }

        return $this->share;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @return bool
     */
    public function isAvailable(): bool
    {
        return
            $this->isActive()
            && ($this->getQuantity() > 0)
            && ($this->getProduct()->isDeliveryAvailable() || $this->getProduct()->isPickupAvailable());
    }

    /**
     * Возвращает подпись упаковки торгового предложения: размер фасовки, объём, цвет и т.п. в зависимости от типа
     * торгового предложения.
     *
     * @param $short
     *
     * @param $fullLimit
     *
     * @return string
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     *
     * @see \FourPaws\Helpers\WordHelper::showWeight
     */
    public function getPackageLabel(bool $short = false, int $fullLimit =0): string
    {
        if ($this->getClothingSize()) {
            return $this->getClothingSize()->getName();
        }

        if ($value = $this->getPackageDimension($short, $fullLimit)) {
            return $value;
        }

        return 'арт. ' . $this->getXmlId();
    }

    /**
     * @param bool $short
     * @param int  $fullLimit
     *
     * @return null|string
     * @throws ApplicationCreateException
     */
    public function getPackageDimension(bool $short = false, int $fullLimit = 0): ?string
    {
        if ($this->getVolumeReference()) {
            return $this->getVolumeReference()->getName();
        }
        $weight = $this->getCatalogProduct()->getWeight();
        if ($weight > 0) {
            return WordHelper::showWeight($weight, $short, $fullLimit);
        }
        return null;
    }

    /**
     * @return string
     * @throws ApplicationCreateException
     */
    public function getFlavourWithWeight(): string
    {
        $flavourName = [];

        $this->getProduct()->getFlavour()->map(function (HlbReferenceItem $item) use (&$flavourName) {
            $flavourName[] = $item->getName();
        });

        return \sprintf('%s, %s', \implode('/', $flavourName), $this->getPackageDimension());
    }

    /**
     * Возвращает тип подписи упаковки
     *
     * @return string Одна из констант \FourPaws\Catalog\Model\Offer::PACKAGE_LABEL_TYPE_*
     *
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function getPackageLabelType(): string
    {
        if ($this->getClothingSize()) {
            return self::PACKAGE_LABEL_TYPE_SIZE;
        }

        if ($this->getVolumeReference()) {

            return self::PACKAGE_LABEL_TYPE_VOLUME;
        }

        return self::PACKAGE_LABEL_TYPE_WEIGHT;
    }

    /**
     * @return string
     */
    public function getCatalogGroupId(): ?string
    {
        $catalogGroupService = Application::getInstance()->getContainer()->get('catalog_group.service');
        $locationService = Application::getInstance()->getContainer()->get('location.service');
        if($catalogGroupId = $catalogGroupService->getCatalogGroupIdByRegion($locationService->getCurrentRegionCode())){
            $this->catalogGroupId = $catalogGroupId;
        } else {
            $this->catalogGroupId = self::CATALOG_GROUP_ID_BASE;
        }
        return $this->catalogGroupId;
    }

    /**
     * @param int $catalogGroupId
     * @return Offer
     */
    public function withCatalogGroup(int $catalogGroupId): Offer
    {
        $this->catalogGroupId = $catalogGroupId;
        return $this;
    }

    /**
     * @todo не использовать этот метод для расчета скидочных цен
     */
    protected function checkOptimalPriceTmp(): void
    {
        /**
         * В эластике price индексируется с уже посчитанной скидкой,
         * поэтому проводить расчеты ни к чему
         * upd: цены зависят от региона
         */
        /*if ($this->oldPrice) {
            return;
        }*/

        /** @var Price $arPrice */
        $arPrice = $this->getPriceByGroupId($this->getCatalogGroupId());

        if($arPrice){
            $oldPrice = $price = (float)$arPrice->getPrice();
        } else{
            $oldPrice = $price = $this->price;
        }

        if ($this->isSimpleSaleAction()) {
            $price = (float)$this->getPriceAction();
        } elseif ($this->isSimpleDiscountAction()) {
            $price *= (100 - $this->getCondValue()) / 100;
        }

        $this->withPrice($price)
             ->withOldPrice($oldPrice)
             ->withDiscount(round(100 * $oldPrice / $price));
        $this->isCounted = true;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        /** LP23-365 Удаляем названия бренда в названии оффера */
        $brandName = $this->getProduct()->getBrandName();
        $name = $this->NAME;

        if (mb_strpos($name, $brandName) !== false) {
            $pattern = '/' . preg_quote($brandName, '\\') . '/i';
            $parts = preg_split($pattern, $name);
            $name = \implode(' ', \array_filter(\array_map('trim', $parts)));
        }

        return $name;
    }

    /**
     * Возвращает доступное количество товара на складах
     *
     * @todo сделать рассчёт действительного количества, а не для 1 штуки
     *
     * @param bool $locationId : id местоположения
     * @param int  $deliveryCodes : символьные коды служб доставки
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws LoaderException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     * @return int
     */
    protected function getAvailableAmount(string $locationId = '', $deliveryCodes = []): int
    {
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        $deliveries = $deliveryService->getByLocation($locationId, $deliveryCodes);
        $max = 0;
        foreach ($deliveries as $delivery) {

            // Из-за того, что здесь не передаётся количество оффера, максимальное количество не будет >1
            $delivery->setStockResult($deliveryService->getStockResultForOffer($this, $delivery));

            if ($delivery->isSuccess()) {
                $availableAmount = $delivery->getStockResult()->getOrderable()->getAmount();
                $max = $max > $availableAmount ? $max : $availableAmount;
            }
        }

        return $max;
    }

    /**
     * @return ArrayCollection
     */
    public function getPrices(): ?Collection
    {
        return $this->prices;
    }

    /**
     * @param Collection $prices
     * @return Offer
     */
    public function withPrices(Collection $prices): Offer
    {
        $this->prices = $prices;
        return $this;
    }

    /**
     * @todo использовать этот метод для расчета скидочных цен
     *
     * Check and set optimal price, discount, old price with bitrix discount
     *
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    protected function checkOptimalPrice(): void
    {
        if ($this->isCounted) {
            return;
        }

        $needEnable = false;

        if (Manager::isExtendDiscountEnabled()) {
            $needEnable = true;
            Manager::disableExtendsDiscount();
        }

        global $USER;

        static $order;
        if (null === $order) {
            $order = Order::create(SITE_ID);
        }
        $shipmentCollection = $order->getShipmentCollection();
        foreach ($shipmentCollection as $i => $shipment) {
            unset($shipmentCollection[$i]);
        }
        /** @var Basket $basket */
        $basket = Basket::create(SITE_ID);
        $basket->setFUserId((int)Fuser::getId());
        $fields = [
            'PRODUCT_ID'             => $this->getId(),
            'QUANTITY'               => 1,
            'MODULE'                 => 'catalog',
            'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
        ];

        BitrixBasket::addProductToBasket($basket, $fields, ['USER_ID' => $USER->GetID()]);

        $order->setBasket($basket);
        /** @var \Bitrix\Sale\BasketItem $basketItem */
        foreach ($basket->getBasketItems() as $basketItem) {
            if (
                (int)$basketItem->getProductId() === $this->getId()
                && $discountPercent = round(100 * ($basketItem->getDiscountPrice() / $basketItem->getBasePrice()))
            ) {
                $this
                    ->withDiscount($discountPercent)
                    ->withOldPrice($basketItem->getBasePrice())
                    ->withPrice($basketItem->getPrice());
            }
        }

        if ($needEnable) {
            Manager::enableExtendsDiscount();
        }

        $this->isCounted = true;
    }

    /**
     * @return Bundle|null
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Exception
     */
    public function getBundle(): ?Bundle
    {
        $break = false;
        $offerId = $this->getId();
        $result = null;
        $setItemsEntity = HLBlockFactory::createTableObject('BundleItems');
        $resBundleItems = $setItemsEntity::query()
                                         ->where('UF_ACTIVE', true)
                                         ->where('UF_PRODUCT', $offerId)
                                         ->setSelect(['ID'])
                                         ->setOrder(['RAND'])
                                         ->registerRuntimeField(new ExpressionField('RAND', 'RAND()'))
                                         ->exec();
        while ($break === false) {
            /**
             * @var array $bundleItem
             */

            $bundleItem = $resBundleItems->fetch();
            if (!$bundleItem) {
                $break = true;
                continue;
            }
            $setEntity = HLBlockFactory::createTableObject('Bundle');
            $resBundle = $setEntity::query()
                                   ->where('UF_ACTIVE', true)
                                   ->where('UF_PRODUCTS', $bundleItem['ID'])
                                   ->setSelect(['UF_NAME', 'UF_PRODUCTS', 'UF_COUNT_ITEMS'])
                                   ->setOrder(['RAND'])
                                   ->registerRuntimeField(new ExpressionField('RAND', 'RAND()'))
                                   ->exec();

            if ($resBundle->getSelectedRowsCount() === 0) {
                continue;
            }

            $breakBundle = false;
            $hasItems = false;

            while ($breakBundle === false) {
                /**
                 * @var array $setItem
                 */
                $setItem = $resBundle->fetch();
                if (!$setItem) {
                    $breakBundle = true;
                    continue;
                }

                $countItems = 2;
                if ($setItem['UF_COUNT_ITEMS']) {
                    $enumField = (new UserFieldEnumService())->getEnumValueEntity((int)$setItem['UF_COUNT_ITEMS']);
                    $countItems = (int)$enumField->getValue();
                }

                $res = $setItemsEntity::query()
                                      ->where('UF_ACTIVE', true)
                                      ->whereIn('ID', $setItem['UF_PRODUCTS'])
                                      ->setLimit($countItems)
                                      ->setSelect(['UF_PRODUCT', 'UF_QUANTITY'])
                                      ->exec();
                if ($res->getSelectedRowsCount() === 0) {
                    continue;
                }
                $result = [
                    'NAME'        => $setItem['UF_NAME'],
                    'COUNT_ITEMS' => $countItems,
                    'PRODUCTS'    => [],
                ];

                /** @noinspection PhpAssignmentInConditionInspection */
                while ($item = $res->fetch()) {
                    /**
                     * @var array $item
                     */
                    $itemFields = [
                        'PRODUCT'    => null,
                        'PRODUCT_ID' => $item['UF_PRODUCT'],
                        'QUANTITY'   => $item['UF_QUANTITY'],
                    ];
                    if ($offerId === (int)$item['UF_PRODUCT']) {
                        $itemFields['PRODUCT'] = $this;
                        $result['PRODUCTS'] = [$item['UF_PRODUCT'] => $itemFields] + $result['PRODUCTS'];
                    } else {
                        $result['PRODUCTS'][$item['UF_PRODUCT']] = $itemFields;
                    }
                    $productIds[] = $item['UF_PRODUCT'];
                }
                $breakBundle = true;
                $hasItems = true;
            }

            if (!$hasItems) {
                continue;
            }

            $break = true;
        }

        if ($result !== null) {
            $serializer = Application::getInstance()->getContainer()->get(SerializerInterface::class);
            $result = $serializer->fromArray(
                $result,
                Bundle::class,
                DeserializationContext::create()->setGroups(['read'])
            );
            if (!empty($productIds)) {
                $offerCollection = (new OfferQuery())->withFilter(['=ID' => $productIds])->exec();
                /** @var Offer $offer */
                foreach ($offerCollection as $offer) {
                    /** @var BundleItem $product */
                    /** @noinspection ForeachSourceInspection */
                    foreach ($result->getProducts() as &$product) {
                        if ($product->getOfferId() === $offer->getId()) {
                            $product->setOffer($offer);
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function hasDiscount(): bool
    {
        return $this->getOldPrice() > 0 && $this->getPrice() > 0 && $this->getOldPrice() > $this->getPrice();
    }

    /**
     * @return float
     */
    public function getDiscountPrice(): float
    {
        return round($this->getOldPrice() - $this->getPrice());
    }

    /**
     * @return string[]
     * @throws ApplicationCreateException
     * @throws ServiceNotFoundException
     * @throws StoreNotFoundException
     */
    public function getAvailableStores(): array
    {
        return $this->getAllStocks()->getStores(1)->getXmlIds();
    }

    /**
     * @param string $primary
     *
     * @return Offer|null
     */
    public static function createFromPrimary(string $primary): ?Offer
    {
        return OfferQuery::getById((int)$primary);
    }

    /**
     * Возможность "довоза" оффера в магазин (DC001 -> Rxxx)
     *
     * @param Store $store
     * @return bool
     * @throws \Exception
     */
    public function isAvailableForDelay(Store $store): bool
    {
        /** @var StoreService $storeService */
        $storeService = Application::getInstance()->getContainer()->get('store.service');

        if (
            $this->getProduct()->isTransportOnlyRefrigerator()
            || ($this->getProduct()->isLicenseRequired() && !$storeService->hasLicense($store) )
        )
        {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getRegionDiscounts(): array
    {
        $this->PROPERTY_REGION_DISCOUNTS = \is_array($this->PROPERTY_REGION_DISCOUNTS) ? array_values($this->PROPERTY_REGION_DISCOUNTS) : [];
        return $this->PROPERTY_REGION_DISCOUNTS;
    }

    /**
     * @param array $regionDiscounts
     * @return Offer
     */
    public function withRegionDiscounts(array $regionDiscounts): self
    {
        $regionDiscounts = array_filter(
            $regionDiscounts,
            function ($value) {
                return $value && \is_array($value);
            }
        );
        $this->PROPERTY_REGION_DISCOUNTS = $regionDiscounts;
        return $this;
    }

    /**
     * @return array|bool|false|null
     * @throws ApplicationCreateException
     */
    public function getCurrentRegionDiscount()
    {
        /** @var LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');
        $this->regionDiscount = false;
        if($regionDiscount = $this->getRegionDiscount($locationService->getCurrentRegionCode())){
            $this->regionDiscount = $regionDiscount;
        }
        return $this->regionDiscount;
    }

    /**
     * @param string $regionCode
     * @return array|null
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getRegionDiscount(string $regionCode): ?array
    {
        /** @var CatalogGroupService $catalogGroupService */
        $catalogGroupService = Application::getInstance()->getContainer()->get('catalog_group.service');
        $catalogGroupId = $catalogGroupService->getCatalogGroupIdByRegion($regionCode);
        $regionDiscounts = $this->getRegionDiscounts();
        foreach ($regionDiscounts as $discount) {
            if ($catalogGroupId == $discount['id']) {
                $regionDiscount = $discount;
                break;
            }
        }

        return $regionDiscount ?? $regionDiscount;
    }

    /**
     * @return float|null
     */
    public function getPriceAction(): ?float
    {
        $regionDiscount = $this->getCurrentRegionDiscount();
        if($regionDiscount){
            return $regionDiscount['price_action'];
        }
        return $this->PROPERTY_PRICE_ACTION;
    }

    /**
     * @return int|null
     */
    public function getCondValue(): ?int
    {
        $regionDiscount = $this->getCurrentRegionDiscount();
        if($regionDiscount){
            return $regionDiscount['cond_value'];
        }
        return $this->PROPERTY_COND_VALUE;
    }

    /**
     * @param int $groupId
     * @return Price|null
     */
    public function getPriceByGroupId(int $groupId): ?Price
    {
        if(!$this->getPrices()){
            return null;
        }

        $price = $this->getPrices()->filter(function($price) use ($groupId){
            /** @var Price $price */
            return $price->getCatalogGroupId() == $groupId;
        });

        if($price->isEmpty()){
            $price = $this->getPrices()->filter(function($price){
                /** @var Price $price */
                return $price->getCatalogGroupId() == self::CATALOG_GROUP_ID_BASE;
            });
        }

        return $price->first();
    }
}