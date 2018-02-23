<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Catalog\Model;

use CCatalogDiscountSave;
use CCatalogProduct;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Collection\ImageCollection;
use FourPaws\BitrixOrm\Collection\ResizeImageCollection;
use FourPaws\BitrixOrm\Model\CatalogProduct;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\BitrixOrm\Model\Image;
use FourPaws\BitrixOrm\Model\Interfaces\ResizeImageInterface;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\BitrixOrm\Query\CatalogProductQuery;
use FourPaws\BitrixOrm\Utils\ReferenceUtils;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\StoreBundle\Collection\StockCollection;
use FourPaws\StoreBundle\Service\StoreService;
use InvalidArgumentException;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class Offer extends IblockElement
{
    const SIMPLE_SHARE_SALE_CODE = 'VKA0';
    const SIMPLE_SHARE_DISCOUNT_CODE = 'ZRBT';

    /**
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $active = true;

    /**p
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
    protected $Catalog_structure20170920134928_REWARD_TYPE = '';

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
     * @var int
     */
    protected $PROPERTY_BY_REQUEST = 0;

    /**
     * Цена по акции - простая акция из SAP
     *
     * @var float
     */
    protected $PROPERTY_PRICE_ACTION = 0;

    /**
     * @var string
     */
    protected $PROPERTY_COND_FOR_ACTION = '';

    /**
     * Размер скидки на товар - простая акция из SAP
     *
     * @var float
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
     * @var float
     */
    protected $oldPrice = 0;

    /**
     * @Type("string")
     * @Groups({"elastic"})
     * @var string
     */
    protected $currency = '';

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
    protected $PROPERTY_HIT;

    /**
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_NEW;
    /**
     * @var bool
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_SALE;

    /**
     * @var string
     */
    protected $link = '';

    /**
     * @var StockCollection
     */
    protected $stocks;

    public function __construct(array $fields = [])
    {
        parent::__construct($fields);

        if (isset($fields['CATALOG_PRICE_2'])) {
            $this->price = (float)$fields['CATALOG_PRICE_2'];
        }

        if (isset($fields['CATALOG_CURRENCY_2'])) {
            $this->currency = (string)$fields['CATALOG_CURRENCY_2'];
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
     * @return Collection|ResizeImageInterface[]
     *
     * @throws InvalidArgumentException
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
    public function withCml2Link(int $productId)
    {
        $this->PROPERTY_CML2_LINK = $productId;
        $this->product = null;

        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getColor()
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
    public function withColourXmlId(string $xmlId)
    {
        $this->PROPERTY_COLOUR = $xmlId;
        $this->colour = null;

        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getVolumeReference()
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
    public function withVolumeReferenceXmlId(string $xmlId)
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

    public function withVolume(float $volume)
    {
        $this->PROPERTY_VOLUME = $volume;

        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null||HlbReferenceItem
     */
    public function getClothingSize()
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

    public function getClothingSizeXmlId(): string
    {
        return (string)$this->PROPERTY_CLOTHING_SIZE;
    }

    public function withClothingSizeXmlId(string $xmlId)
    {
        $this->PROPERTY_CLOTHING_SIZE = $xmlId;
        $this->clothingSize = null;

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
    public function withBarcodes(array $barcodes)
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
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getKindOfPacking()
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
    public function withKindOfPackingXmlId(string $xmlId)
    {
        $this->kindOfPacking = null;
        $this->PROPERTY_KIND_OF_PACKING = $xmlId;

        return $this;
    }

    /**
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getSeasonYear()
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
    public function withSeasonYearXmlId(string $xmlId)
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
    public function withMultiplicity(int $multiplicity)
    {
        $this->PROPERTY_MULTIPLICITY = $multiplicity;

        return $this;
    }

    /**
     * Возвращает тип вознаграждения для заводчика.
     *
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getRewardType()
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
    public function withRewardTypeXmlId(string $xmlId)
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

    public function withColourCombination(string $colourCombination)
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

    public function withFlavourCombination(string $flavourCombination)
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
    public function withOldUrl(string $oldUrl)
    {
        $this->PROPERTY_OLD_URL = $oldUrl;

        return $this;
    }

    /**
     * @return bool
     */
    public function isByRequest(): bool
    {
        return (bool)$this->PROPERTY_BY_REQUEST;
    }

    public function withByRequest(bool $byRequest)
    {
        $this->PROPERTY_BY_REQUEST = $byRequest;

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
        $this->checkOptimalPrice();

        return $this->price;
    }

    /**
     * @return bool
     */
    public function getPropertyHit()
    {
        return $this->PROPERTY_HIT;
    }

    /**
     * @param bool $propertyHit
     *
     * @return Offer
     */
    public function setPropertyHit($propertyHit)
    {
        $this->PROPERTY_HIT = $propertyHit;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPropertyNew()
    {
        return $this->PROPERTY_NEW;
    }

    /**
     * @param bool $propertyNew
     *
     * @return Offer
     */
    public function setPropertyNew($propertyNew)
    {
        $this->PROPERTY_HIT = $propertyNew;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPropertySale()
    {
        return $this->PROPERTY_SALE;
    }

    /**
     * @param bool $propertySale
     *
     * @return Offer
     */
    public function setPropertySale($propertySale)
    {
        $this->PROPERTY_SALE = $propertySale;

        return $this;
    }

    protected function checkOptimalPrice()
    {
        CCatalogDiscountSave::Disable();
        $optimalPrice = CCatalogProduct::GetOptimalPrice($this->getId());
        CCatalogDiscountSave::Enable();

        if (\is_array($optimalPrice)) {
            /**
             * @var array $optimalPrice
             */
            $resultPrice = $optimalPrice['RESULT_PRICE'] ?? [
                    'PERCENT'        => 0,
                    'BASE_PRICE'     => $this->price,
                    'DISCOUNT_PRICE' => $this->price,
                ];
            $this->withDiscount(floor($resultPrice['PERCENT']));
            if ($this->discount > 0) {
                $this->withOldPrice($resultPrice['BASE_PRICE']);
                $this->withPrice($resultPrice['DISCOUNT_PRICE']);
            }
        }
    }

    /**
     * @param int $discount
     *
     * @return static
     */
    public function withDiscount(int $discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * @param float $oldPrice
     *
     * @return $this
     */
    public function withOldPrice(float $oldPrice)
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

    public function withImagesIds(array $ids)
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
        $this->checkOptimalPrice();

        return $this->oldPrice;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return int
     */
    public function getDiscount(): int
    {
        $this->checkOptimalPrice();

        return $this->discount;
    }

    public function getBonuses()
    {
        /** @todo расчет бонусов */
        return 112;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        if (!$this->link) {
            $this->link = sprintf('%s?offer=%s', $this->getProduct()->getDetailPageUrl(), $this->getId());
        }

        return $this->link;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        if (null === $this->product) {
            $this->product = (new ProductQuery())->withFilter(['=ID' => $this->getCml2Link()])->exec()->current();

            if (!($this->product instanceof Product)) {
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
    public function setProduct(Product $product)
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
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->getStocks()->getTotalAmount();
    }

    /**
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return StockCollection
     */
    public function getStocks(): StockCollection
    {
        if (!$this->stocks) {
            /** @var StoreService $storeService */
            $storeService = Application::getInstance()->getContainer()->get('store.service');
            $allStocks = $storeService->getStocksByOffer($this);
            $stores = $storeService->getByCurrentLocation();
            $this->withStocks($allStocks->filterByStores($stores));
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
     * Участвует ли товар в акции "Скидка на товар"
     */
    public function isSimpleDiscountAction()
    {
        return $this->PROPERTY_COND_VALUE > 0 && $this->PROPERTY_COND_FOR_ACTION === self::SIMPLE_SHARE_DISCOUNT_CODE;
    }

    /**
     * Участвует ли товар в ации "Цена по акции"
     */
    public function isSimpleSaleAction()
    {
        return $this->PROPERTY_PRICE_ACTION > 0 && $this->PROPERTY_COND_FOR_ACTION === self::SIMPLE_SHARE_SALE_CODE;
    }

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
        return $this->getPropertyHit();
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->getPropertyNew();
    }

    /**
     * @return bool
     */
    public function isSale(): bool
    {
        return $this->isSale();
    }
}
