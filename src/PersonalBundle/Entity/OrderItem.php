<?php

namespace FourPaws\PersonalBundle\Entity;


use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use JMS\Serializer\Annotation as Serializer;

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
     * @Serializer\SerializedName("PROPERTY_IMG")
     * @Serializer\Groups(groups={"read"})
     */
    protected $image = '';

    /** @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_BRAND")
     * @Serializer\Groups(groups={"read"})
     */
    protected $brand = '';

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
    public function setWeight(float $weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return string
     */
    public function getArticle(): string
    {
        return  $this->article ?? '';
    }

    /**
     * @param string $article
     */
    public function setArticle(string $article)
    {
        $this->article = $article;
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
    public function setOfferSelectedProp(string $offerSelectedProp)
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
    public function setHaveStock(bool $haveStock)
    {
        $this->haveStock = $haveStock;
    }

    /**
     * @return string
     */
    public function getFormatedSum(): string
    {
        return number_format($this->getSum(), 2, '.', ' ');
    }

    /**
     * @return string
     */
    public function getFormatedPrice(): string
    {
        return number_format($this->getPrice(), 2, '.', ' ');
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
        $image = $this->getImage();
        if (!empty($image)){
            if(is_numeric($image)) {
                try {
                    $path = ResizeImageDecorator::createFromPrimary($image)
                        ->setResizeHeight(120)
                        ->setResizeWidth(80);
                } catch (FileNotFoundException $e) {
                }
            }else{
                /** установка второго аргумента все ломает */
                $unserializeImage = unserialize($image);
                if(\is_array($unserializeImage['VALUE']) && !empty($unserializeImage['VALUE'])){
                    $image = current($unserializeImage['VALUE']);
                    try {
                        $path = ResizeImageDecorator::createFromPrimary($image)
                            ->setResizeHeight(120)
                            ->setResizeWidth(80);
                    } catch (FileNotFoundException $e) {
                    }
                }
            }
        }
        return $path;
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
    public function setOfferSelectedPropName(string $offerSelectedPropName)
    {
        $this->offerSelectedPropName = $offerSelectedPropName;
    }

    /**
     * @return string
     */
    public function getFlavour(): string
    {
        return $this->flavour;
    }

    /**
     * @param string $flavour
     */
    public function setFlavour(string $flavour): void
    {
        $this->flavour = $flavour;
    }
}