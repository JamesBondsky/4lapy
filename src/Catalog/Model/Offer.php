<?php

namespace FourPaws\Catalog\Model;

use DateTimeImmutable;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Catalog\ReferenceUtils;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

class Offer extends IblockElement
{
    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $XML_ID = '';

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

    //TODO Изображения
    protected $PROPERTY_IMG;

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
     * @return Product
     */
    public function getProduct()
    {
        if (is_null($this->product)) {
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
     * @return HlbReferenceItem
     */
    public function getColor()
    {
        if (is_null($this->colour)) {
            $this->colour = ReferenceUtils::getReference('bx.hlblock.colour', $this->PROPERTY_COLOUR);
        }

        return $this->colour;
    }

    /**
     * @return HlbReferenceItem
     */
    public function getVolumeReference()
    {
        if (is_null($this->volumeReference)) {
            $this->volumeReference = ReferenceUtils::getReference(
                'bx.hlblock.volume',
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
     * @return HlbReferenceItem
     */
    public function getClothingSize()
    {
        if (is_null($this->clothingSize)) {
            $this->clothingSize = ReferenceUtils::getReference(
                'bx.hlblock.clothingsize',
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
     * @return HlbReferenceItem
     */
    public function getKindOfPacking()
    {
        if (is_null($this->kindOfPacking)) {
            $this->kindOfPacking = ReferenceUtils::getReference(
                'bx.hlblock.packagetype',
                $this->PROPERTY_KIND_OF_PACKING
            );
        }

        return $this->kindOfPacking;
    }

    /**
     * @return HlbReferenceItem
     */
    public function getSeasonYear()
    {
        if (is_null($this->seasonYear)) {
            $this->seasonYear = ReferenceUtils::getReference('bx.hlblock.year', $this->PROPERTY_SEASON_YEAR);
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
     * @return HlbReferenceItem
     */
    public function getRewardType()
    {
        if (is_null($this->rewardType)) {
            $this->rewardType = ReferenceUtils::getReference('bx.hlblock.rewardtype', $this->PROPERTY_REWARD_TYPE);
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
}
