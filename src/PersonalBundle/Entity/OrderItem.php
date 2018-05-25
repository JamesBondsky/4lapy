<?php

namespace FourPaws\PersonalBundle\Entity;

use FourPaws\App\Templates\MediaEnum;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class OrderItem
 *
 * @package FourPaws\PersonalBundle\Entity
 */
class OrderItem extends BaseEntity
{
    /** @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PRODUCT_XML_ID")
     * @Serializer\Groups(groups={"read"})
     */
    protected $article = '';

    /** @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PRODUCT_ID")
     * @Serializer\Groups(groups={"read"})
     */
    protected $productId = '';

    /** @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("NAME")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $name = '';

    /** @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("QUANTITY")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $quantity = 0;

    /** @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("PRICE")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $price = 0;

    /** @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("SUMMARY_PRICE")
     * @Serializer\Groups(groups={"read"})
     */
    protected $sum = 0;

    /** @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("WEIGHT")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $weight = 0;

    /** @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_SELECTED")
     * @Serializer\Groups(groups={"read"})
     */
    protected $offerSelectedProp = '';

    /** @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_SELECTED_NAME")
     * @Serializer\Groups(groups={"read"})
     */
    protected $offerSelectedPropName = '';

    /** @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_FLAVOUR")
     * @Serializer\Groups(groups={"read"})
     */
    protected $flavour = '';

    /** @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("BONUS")
     * @Serializer\Groups(groups={"read"})
     */
    protected $bonus = 0;

    /** @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("HAVE_STOCK")
     * @Serializer\Groups(groups={"read"})
     */
    protected $haveStock = false;

    /** @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_OFFER_IMG")
     * @Serializer\Groups(groups={"read"})
     */
    protected $images = '';

    /** @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("OFFER_IMG")
     * @Serializer\Groups(groups={"read"})
     */
    protected $image = '';

    /** @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_BRAND")
     * @Serializer\Groups(groups={"read"})
     */
    protected $brand = '';

    /** @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("DETAIL_PAGE_URL")
     * @Serializer\Groups(groups={"read"})
     */
    protected $detailPageUrl = '';

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? '';
    }

    /**
     * @param string $name
     *
     * @return OrderItem
     */
    public function setName(string $name): OrderItem
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return float
     */
    public function getBonus(): float
    {
        return $this->bonus ?? 0;
    }

    /**
     * @param float $bonus
     *
     * @return OrderItem
     */
    public function setBonus(float $bonus): OrderItem
    {
        $this->bonus = $bonus;
        return $this;
    }

    /**
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity ?? 0;
    }

    /**
     * @param float $quantity
     *
     * @return OrderItem
     */
    public function setQuantity(float $quantity): OrderItem
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price ?? 0;
    }

    /**
     * @param float $price
     *
     * @return OrderItem
     */
    public function setPrice(float $price): OrderItem
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return float
     */
    public function getSum(): float
    {
        return $this->sum ?? 0;
    }

    /**
     * @param float $sum
     *
     * @return OrderItem
     */
    public function setSum(float $sum): OrderItem
    {
        $this->sum = $sum;
        return $this;
    }

    /**
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight ?? 0;
    }

    /**
     * @param float $weight
     */
    public function setWeight(float $weight): void
    {
        $this->weight = $weight;
    }

    /**
     * @return string
     */
    public function getArticle(): string
    {
        return $this->article ?? '';
    }

    /**
     * @param string $article
     */
    public function setArticle(string $article): void
    {
        $this->article = $article;
    }

    /**
     * @return bool
     */
    public function hasArticle(): bool
    {
        return !empty($this->getArticle());
    }

    /**
     * @return string
     */
    public function getOfferSelectedProp(): string
    {
        return $this->offerSelectedProp ?? '';
    }

    /**
     * @param string $offerSelectedProp
     */
    public function setOfferSelectedProp(string $offerSelectedProp): void
    {
        $this->offerSelectedProp = $offerSelectedProp;
    }

    /**
     * @return bool
     */
    public function isHaveStock(): bool
    {
        return $this->haveStock ?? false;
    }

    /**
     * @param bool $haveStock
     */
    public function setHaveStock(bool $haveStock): void
    {
        $this->haveStock = $haveStock;
    }

    /**
     * @return string
     */
    public function getFormattedSum(): string
    {
        return \number_format($this->getSum(), 2, '.', ' ');
    }

    /**
     * @return string
     */
    public function getFormattedPrice(): string
    {
        return \number_format($this->getPrice(), 2, '.', ' ');
    }

    /**
     * @return string
     */
    public function getBrand(): string
    {
        return $this->brand ?? '';
    }

    /**
     * @param string $brand
     *
     * @return OrderItem
     */
    public function setBrand(string $brand): OrderItem
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @return string
     */
    public function getOfferSelectedPropName(): string
    {
        return $this->offerSelectedPropName ?? '';
    }

    /**
     * @param string $offerSelectedPropName
     */
    public function setOfferSelectedPropName(string $offerSelectedPropName): void
    {
        $this->offerSelectedPropName = $offerSelectedPropName;
    }

    /**
     * @return string
     */
    public function getFlavour(): string
    {
        return $this->flavour ?? '';
    }

    /**
     * @param string $flavour
     */
    public function setFlavour(string $flavour): void
    {
        $this->flavour = $flavour;
    }

    /**
     * @return string
     */
    public function getDetailPageUrl(): string
    {
        /**
         * @todo оптимизировать - перегружаться будет всегда при запросе если пустая строка - малый перегруз - но перегруз
         */
        if (($this->hasArticle() || $this->hasProductId())
            && (
                !$this->hasDetailPageUrl(true)
                || (
                    $this->hasDetailPageUrl(true)
                    && !\preg_match('/^\/catalog\/.*\.html\?offer2=/i', $this->detailPageUrl)
                )
            )
        ) {
            $this->reloadPageUrl();
        }
        return $this->detailPageUrl ?? '';
    }

    /**
     * @param string $detailPageUrl
     */
    public function setDetailPageUrl(string $detailPageUrl): void
    {
        $this->detailPageUrl = $detailPageUrl;
    }

    /**
     * @param bool $inner
     *
     * @return bool
     */
    public function hasDetailPageUrl(bool $inner = false): bool
    {
        if ($inner) {
            $detailPageUrl = $this->detailPageUrl;
        } else {
            $detailPageUrl = $this->getDetailPageUrl();
        }

        return !empty($detailPageUrl);
    }

    public function reloadPageUrl(): void
    {
        $filter = [];
        $offer = null;

        if ($this->hasArticle()) {
            $filter = ['=XML_ID' => $this->getArticle()];
        } elseif ($this->hasProductId()) {
            $filter = ['=ID' => $this->getProductId()];
            $offer = OfferQuery::getById((int)$this->getProductId());
        }

        if (!empty($filter)) {
            if ($offer === null) {
                $offer = (new OfferQuery())->withFilter($filter)->exec()->first();
            }
            /** @var Offer $offer */
            if ($offer) {
                $this->setDetailPageUrl($offer->getLink());
            }
        }
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId ?? '';
    }

    /**
     * @param string $productId
     */
    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    /**
     * @return bool
     */
    public function hasProductId(): bool
    {
        return !empty($this->getProductId());
    }

    /**
     * @return string
     */
    public function getImages(): string
    {
        return $this->images ?? '';
    }

    /**
     * @param string $images
     */
    public function setImages(string $images): void
    {
        $this->images = $images;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image ?? '';
    }

    /**
     * @param string $image
     *
     * @return OrderItem
     */
    public function setImage(string $image): OrderItem
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return string
     */
    public function getImagePath(): string
    {
        $path = '';
        $image = (int)$this->getImage();

        if ($image) {
            $path = $this->resizeImage($image);
        } else {
            $image = $this->getImages();
            /** @noinspection UnserializeExploitsInspection */
            $unserializeImage = \unserialize($image);
            if (\is_array($unserializeImage)) {
                if (\is_array($unserializeImage['VALUE']) && !empty($unserializeImage['VALUE'])) {
                    foreach ($unserializeImage['VALUE'] as $imgId) {
                        if (!empty($imgId)) {
                            $path = $this->resizeImage((int)$imgId);
                            if (!empty($path)) {
                                break;
                            }
                        }
                    }
                }
            } else {
                $path = $this->resizeImage((int)$image);
            }
        }

        return $path;
    }

    /**
     * @param int $id
     *
     * @return string
     */
    protected function resizeImage(int $id): string
    {
        try {
            $path = ResizeImageDecorator::createFromPrimary($id)
                ->setResizeWidth(80)
                ->setResizeHeight(145)->getSrc();
        } catch (FileNotFoundException $e) {
            $path = (new ResizeImageDecorator())->setSrc(MediaEnum::NO_IMAGE_WEB_PATH)
                ->setResizeWidth(80)
                ->setResizeHeight(145)
                ->getSrc();
        }

        return $path;
    }
}
