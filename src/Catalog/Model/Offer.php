<?php

namespace FourPaws\Catalog\Model;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Model\CatalogProduct;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\BitrixOrm\Query\CatalogProductQuery;
use FourPaws\BitrixOrm\Utils\ReferenceUtils;
use FourPaws\Catalog\Collection\PriceCollection;
use FourPaws\Catalog\Query\PriceQuery;
use FourPaws\Catalog\Query\ProductQuery;
use JMS\Serializer\Annotation\Accessor;
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
     * @var Collection
     * @Type("ArrayCollection<FourPaws\Catalog\Model\Price>")
     * @Accessor(getter="getAllPrices",setter="withAllPrices")
     * @Groups({"elastic"})
     */
    protected $prices;

    /**
     * @var CatalogProduct
     */
    protected $catalogProduct;

    public function __construct(array $fields = [])
    {
        parent::__construct($fields);
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
     * @return HlbReferenceItem
     */
    public function getColor()
    {
        if (null === $this->colour) {
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
     * @return HlbReferenceItem
     */
    public function getVolumeReference()
    {
        if (null === $this->volumeReference) {
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
     * @return HlbReferenceItem
     */
    public function getClothingSize()
    {
        if (null === $this->clothingSize) {
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
     * @return HlbReferenceItem
     */
    public function getKindOfPacking()
    {
        if (null === $this->kindOfPacking) {
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
     * @return HlbReferenceItem
     */
    public function getSeasonYear()
    {
        if (null === $this->seasonYear) {
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
     * @return HlbReferenceItem
     */
    public function getRewardType()
    {
        if (null === $this->rewardType) {
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

    /**
     * @return string
     */
    public function getSkuId()
    {
        return $this->getXmlId();
    }

    /**
     * @param string $regionId
     *
     * @throws RuntimeException
     * @return Price
     */
    public function getPrice(string $regionId): Price
    {
        if (!$this->getAllPrices()->offsetExists($regionId)) {
            throw new RuntimeException(
                sprintf(
                    'Цена торгового предложения %d в регионе %s не найдена.',
                    $this->getId(),
                    $regionId
                )
            );
        }

        return $this->getAllPrices()->offsetGet($regionId);
    }

    /**
     * @return Collection|Price[]
     */
    public function getAllPrices(): Collection
    {
        if (null === $this->prices) {
            $this->withAllPrices((new PriceQuery())->getAllPrices($this->getId()));
        }

        return $this->prices;
    }

    /**
     * @param Collection|Price[] $priceCollection
     * @return $this
     */
    public function withAllPrices(Collection $priceCollection)
    {
        $this->prices = PriceCollection::createIndexedByRegion($priceCollection);

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
     * @return Offer
     */
    public function withCatalogProduct(CatalogProduct $catalogProduct): Offer
    {
        $this->catalogProduct = $catalogProduct;
        return $this;
    }
}
