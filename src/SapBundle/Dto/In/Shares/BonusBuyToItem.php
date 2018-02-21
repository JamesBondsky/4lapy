<?php

namespace FourPaws\SapBundle\Dto\In\Shares;

use JMS\Serializer\Annotation as Serializer;


/**
 * Class BonusBuyToItem
 *
 * @package FourPaws\SapBundle\Dto\In\Shares
 */
class BonusBuyToItem
{
    /**
     * Содержит уникальный идентификатор торгового предложения, 7-значный цифровой код.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("MAT_NR")
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $offerId = '';
}
