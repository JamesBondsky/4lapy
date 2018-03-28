<?php

namespace FourPaws\SaleBundle\Repository\CouponStorage;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface CouponSessionStorage
 *
 * @package FourPaws\SaleBundle\Repository\CouponStorage
 */
class CouponSessionStorage extends ArrayCollection implements CouponStorageInterface
{
    protected const SESSION_KEY = 'CLIENT_COUPONS';

    /**
     * CouponSessionStorage constructor.
     */
    public function __construct()
    {
        $coupons = $_SESSION[self::SESSION_KEY] && \is_array($_SESSION[self::SESSION_KEY]) ? $_SESSION[self::SESSION_KEY] : [];

        parent::__construct($coupons);
    }

    /**
     * @todo Coupon class
     *
     * @param string $coupon
     *
     * @return void
     */
    public function save(string $coupon): void
    {
        $this->add($coupon);
    }

    /**
     * {@inheritDoc}
     */
    public function add($element)
    {
        if ($this->exists(function (/** @noinspection PhpUnusedParameterInspection */$k, $v) use ($element) {
            return $v === $element;
        })) {
            return true;
        }

        $result = parent::add($element);
        $this->actualizeSessionStorage();

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function removeElement($element)
    {
        $result = parent::removeElement($element);
        $this->actualizeSessionStorage();

        return $result;
    }

    /**
     * @todo Coupon class
     *
     * @param string $coupon
     *
     * @return void
     */
    public function delete(string $coupon): void {
        $this->removeElement($coupon);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        parent::clear();
        $this->actualizeSessionStorage();
    }

    /**
     * Persistence
     *
     * @return void
     */
    protected function actualizeSessionStorage(): void {
        $_SESSION[self::SESSION_KEY] = $this->toArray();
    }

    /**
     * @return string
     */
    public function getApplicableCoupon(): string
    {
        return (string)$this->first() ?? '';
    }
}
