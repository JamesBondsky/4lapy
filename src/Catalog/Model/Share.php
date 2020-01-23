<?php

namespace FourPaws\Catalog\Model;

use DateTimeImmutable;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\Search\Model\HitMetaInfoAwareInterface;
use FourPaws\Search\Model\HitMetaInfoAwareTrait;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

class Share extends IblockElement implements HitMetaInfoAwareInterface
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
}
