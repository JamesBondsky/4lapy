<?php

namespace FourPaws\Catalog\Model;

use DateTimeImmutable;
use FourPaws\BitrixOrm\Model\IblockElement;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

class Brand extends IblockElement
{
    /**
     * @var bool
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
     * @Groups({"elastic"})
     */
    protected $PREVIEW_TEXT = '';

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $PREVIEW_TEXT_TYPE = '';

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $DETAIL_TEXT = '';

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $DETAIL_TEXT_TYPE = '';

    /**
     * @var string
     * @Groups({"elastic"})
     */
    protected $CANONICAL_PAGE_URL = '';

    /**
     * @var string
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

}
