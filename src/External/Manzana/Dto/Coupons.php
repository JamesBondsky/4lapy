<?php

namespace FourPaws\External\Manzana\Dto;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Coupons
 *
 * @package FourPaws\External\Manzana\Dto
 */
class Coupons
{
    /**
     * @Serializer\XmlList(inline=true, entry="Item")
     * @Serializer\Type("ArrayCollection<FourPaws\External\Manzana\Dto\Coupon>")
     *
     * @var Collection|Coupon[]
     */
    protected $coupons;
}
