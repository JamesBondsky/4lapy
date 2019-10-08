<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use JMS\Serializer\Annotation as Serializer;

class UserCouponsResponse
{
    /**
     * Содержит активные купоны пользователя
     *
     * @Serializer\Type("array")
     * @Serializer\SerializedName("coupons")
     * @var array
     */
    protected $coupons = [];
    
    /**
     * @return array
     */
    public function getUserCoupons(): array
    {
        return $this->coupons;
    }
    
    /**
     * @param array $coupons
     *
     * @return UserCouponsResponse
     */
    public function setUserCoupons(array $coupons): UserCouponsResponse
    {
        $this->coupons = $coupons;
        return $this;
    }
}
