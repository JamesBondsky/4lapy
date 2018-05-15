<?php

namespace FourPaws\SaleBundle\Repository\CouponStorage;

/**
 * Interface CouponStorageInterface
 *
 * @todo implements Base storage interface
 *
 * @package FourPaws\SaleBundle\Repository\CouponStorage
 */
interface CouponStorageInterface
{
    /**
     * @todo Coupon class
     *
     * @param string $coupon
     *
     * @return void
     */
    public function save(string $coupon): void;

    /**
     * @todo Coupon class
     *
     * @param string $coupon
     *
     * @return void
     */
    public function delete(string $coupon): void;

    /**
     * @return void
     */
    public function clear(): void;

    /**
     * @todo Coupon
     *
     * @return string
     */
    public function getApplicableCoupon(): string;
}
