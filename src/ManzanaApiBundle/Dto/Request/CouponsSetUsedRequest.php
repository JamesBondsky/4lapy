<?php

namespace FourPaws\ManzanaApiBundle\Dto\Request;

use FourPaws\ManzanaApiBundle\Dto\Object\Coupon;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class CouponsSetUsedRequest extends Request
{
    /**
     * @Assert\NotBlank()
     * @Serializer\Type("array<FourPaws\ManzanaApiBundle\Dto\Object\Coupon>")
     * @Serializer\SerializedName("messages")
     * @var Coupon[]
     */
    protected $coupons = [];

    /**
     * @param Coupon[] $coupons
     * @return CouponsSetUsedRequest
     */
    public function setCoupons(array $coupons): CouponsSetUsedRequest
    {
        $this->coupons = $coupons;
        return $this;
    }

    /**
     * @return Coupon[]
     */
    public function getCoupons(): array
    {
        return $this->coupons;
    }
}