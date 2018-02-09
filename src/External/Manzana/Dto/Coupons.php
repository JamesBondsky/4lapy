<?php

namespace FourPaws\External\Manzana\Dto;

use Doctrine\Common\Collections\ArrayCollection;
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
    
    /**
     * @param \FourPaws\External\Manzana\Dto\Coupon $coupon
     *
     * @return $this
     */
    public function addCoupon(Coupon $coupon)
    {
        if (null === $this->coupons) {
            $this->coupons = new ArrayCollection();
        }
        
        $this->coupons->add($coupon);
        
        return $this;
    }
    
    /**
     * @param string $coupon
     *
     * @return $this
     */
    public function addCouponFromString(string $coupon)
    {
        $this->addCoupon((new Coupon())->setNumber($coupon));
        
        return $this;
    }
}
