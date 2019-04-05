<?php

namespace FourPaws\Catalog\Model;

use DateTimeImmutable;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\Search\Model\HitMetaInfoAwareInterface;
use FourPaws\Search\Model\HitMetaInfoAwareTrait;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

class Brand extends IblockElement implements HitMetaInfoAwareInterface
{
    use HitMetaInfoAwareTrait;

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
     * @Type("bool")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_POPULAR = 0;

    /**
     * @var array|null
     * @Type("array")
     */
    protected $PROPERTY_CATALOG_INNER_BANNER = 0;

    /**
     * @var array|null
     * @Type("array")
     */
    protected $PROPERTY_CATALOG_UNDER_BANNER = 0;

    /**
     * @var string Транслиты названия бренда
     * @Type("string")
     * @Accessor(getter="getTranslits")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_TRANSLITS = '';

    /**
     * @var bool Бонус для заводчиков на товары этого бренда
     * @Type("bool")
     * @Accessor(getter="isBonusOpt")
     * @Groups({"elastic"})
     */
    protected $PROPERTY_BONUS_OPT = false;

    /**
     * @return bool
     */
    public function isPopular(): bool
    {
        return (bool)(int)$this->PROPERTY_POPULAR;
    }

    /**
     * @param bool $popular
     *
     * @return $this
     */
    public function withPopular(bool $popular)
    {
        $this->PROPERTY_POPULAR = (int)$popular;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getCatalogInnerBanner(): ?array
    {
        return ($this->PROPERTY_CATALOG_INNER_BANNER) ? $this->PROPERTY_CATALOG_INNER_BANNER : null;
    }

    /**
     * @param $banner
     *
     * @return $this
     */
    public function withCatalogInnerBanner($banner)
    {
        $this->PROPERTY_CATALOG_INNER_BANNER = $banner;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getCatalogUnderBanner(): ?array
    {
        return ($this->PROPERTY_CATALOG_UNDER_BANNER) ? $this->PROPERTY_CATALOG_UNDER_BANNER : null;
    }

    /**
     * @param $banner
     *
     * @return $this
     */
    public function withCatalogUnderBanner($banner)
    {
        $this->PROPERTY_CATALOG_UNDER_BANNER = $banner;

        return $this;
    }

    /**
     * @return string
     */
    public function getTranslits(): string
    {
        return $this->PROPERTY_TRANSLITS;
    }

    /**
     * @param $translits
     * @return $this
     */
    public function withTranslits($translits)
    {
        $this->PROPERTY_TRANSLITS = $translits;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBonusOpt(): bool
    {
        return $this->PROPERTY_POPULAR;
    }


    /**
     * @param $bonus
     *
     * @return $this
     */
    public function withBonusOpt($bonus)
    {
        $this->PROPERTY_CATALOG_UNDER_BANNER = $bonus;

        return $this;
    }
}
