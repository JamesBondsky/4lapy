<?php

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
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\BitrixOrm\Query\CatalogProductQuery;
use FourPaws\BitrixOrm\Utils\ReferenceUtils;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\StoreBundle\Service\StoreService;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;

class Offer extends IblockElement
{
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
    protected $PROPERTY_PACKING_COMBINATION = '';

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
     * @throws \InvalidArgumentException
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        if ($this->images instanceof Collection) {
            return $this->images;
        }
        $this->images = ImageCollection::createFromIds($this->getImagesIds());

        return $this->images;
    }

    /**
     * @param Collection|Image[] $images
     *
     * @return static
     */
    public function withImages(Collection $images)
    {
        $this->images = $images;
        return $this;
    }

    /**
     * @param int $width
     * @param int $height
     *
     * @return ResizeImageCollection
     */
    public function getResizeImages(int $width = 0, int $height = 0): Collection
    {
        if ($this->resizeImages instanceof Collection) {
            if ($width) {
                $this->resizeImages->forAll(function ($key, ResizeImageDecorator $image) use ($width) {
                    $image->setResizeWidth($width);
                    return true;
                });
            }
            if ($height) {
                $this->resizeImages->forAll(function ($key, ResizeImageDecorator $image) use ($height) {
                    $image->setResizeHeight($height);
                    return true;
                });
            }
            return $this->resizeImages;
        }

        $this->resizeImages = ResizeImageCollection::createFromImageCollection($this->getImages(), $width, $height);

        return $this->resizeImages;
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
     * @return Product
     */
    public function getProduct()
    {
        if (null === $this->product) {
            $this->product = (new ProductQuery())->withFilter(['=ID' => (int)$this->PROPERTY_CML2_LINK])
                ->exec()
                ->current();

            if (!($this->product instanceof Product)) {
                $this->product = new Product();
            }
        }

        return $this->product;
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
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @return null|HlbReferenceItem
     */
    public function getVolumeReference()
    {
        if ((null === $this->volumeReference) && $this->PROPERTY_VOLUME_REFERENCE) {
            $this->volumeReference = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.volume'),
                $this->PROPERTY_VOLUME_REFERENCE
            );
        }

        return $this->volumeReference;
    }

    /**
     * @return float
     */
    public function getVolume()
    {
        return (float)$this->PROPERTY_VOLUME;
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
            $this->clothingSize = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.clothingsize'),
                $this->PROPERTY_CLOTHING_SIZE
            );
        }

        return $this->clothingSize;
    }

    /**
     * @return string[]
     */
    public function getBarcodes()
    {
        return $this->PROPERTY_BARCODE;
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
            $this->kindOfPacking = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.packagetype'),
                $this->PROPERTY_KIND_OF_PACKING
            );
        }

        return $this->kindOfPacking;
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
                $this->PROPERTY_SEASON_YEAR
            );
        }

        return $this->seasonYear;
    }

    /**
     * @return int
     */
    public function getMultiplicity()
    {
        return (int)$this->PROPERTY_MULTIPLICITY;
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
            $this->rewardType = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.rewardtype'),
                $this->PROPERTY_REWARD_TYPE
            );
        }

        return $this->rewardType;
    }

    /**
     * @return string
     */
    public function getPackingCombination()
    {
        return $this->PROPERTY_PACKING_COMBINATION;
    }

    /**
     * @return string
     */
    public function getColourCombination()
    {
        return $this->PROPERTY_COLOUR_COMBINATION;
    }

    /**
     * @return string
     */
    public function getFlavourCombination()
    {
        return $this->PROPERTY_FLAVOUR_COMBINATION;
    }

    /**
     * @return string
     */
    public function getOldUrl()
    {
        return $this->PROPERTY_OLD_URL;
    }

    public function isByRequest(): bool
    {
        return (bool)$this->PROPERTY_BY_REQUEST;
    }

    /**
     * @return string
     */
    public function getSkuId()
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
     * @return array
     */
    public function getImagesIds(): array
    {
        return $this->PROPERTY_IMG;
    }

    /**
     * @return CatalogProduct
     */
    public function getCatalogProduct(): CatalogProduct
    {
        if (null === $this->catalogProduct) {
            $catalogProduct = (new CatalogProductQuery())
                ->withFilter(['ID' => $this->getId()])
                ->exec()
                ->current();
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

    public function getBonuses()
    {
        /* @@todo расчет бонусов */
        return 112;
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
}
