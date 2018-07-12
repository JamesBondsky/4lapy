<?php

namespace FourPaws\EcommerceBundle\Dto\GoogleEcommerce;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class ActionField
 *
 * @package FourPaws\EcommerceBundle\Dto\GoogleEcommerce
 */
class ActionField
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $list;

    /**
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $step;

    /**
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $option;

    /**
     * Номер транзакции (заказа)
     *
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $id;

    /**
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $affiliation;

    /**
     * @Serializer\Type("float")
     * @Serializer\SkipWhenEmpty()
     *
     * @var float
     */
    protected $revenue;

    /**
     * @Serializer\Type("float")
     * @Serializer\SkipWhenEmpty()
     *
     * @var float
     */
    protected $tax;

    /**
     * @Serializer\Type("float")
     * @Serializer\SkipWhenEmpty()
     *
     * @var float
     */
    protected $shipping;

    /**
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $coupon;

    /**
     * @return string
     */
    public function getList(): string
    {
        return $this->list;
    }

    /**
     * @param string $list
     *
     * @return ActionField
     */
    public function setList(string $list): ActionField
    {
        $this->list = $list;

        return $this;
    }

    /**
     * @return string
     */
    public function getStep(): string
    {
        return $this->step;
    }

    /**
     * @param string $step
     *
     * @return ActionField
     */
    public function setStep(string $step): ActionField
    {
        $this->step = $step;

        return $this;
    }

    /**
     * @return string
     */
    public function getOption(): string
    {
        return $this->option;
    }

    /**
     * @param string $option
     *
     * @return ActionField
     */
    public function setOption(string $option): ActionField
    {
        $this->option = $option;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return ActionField
     */
    public function setId(string $id): ActionField
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getAffiliation(): string
    {
        return $this->affiliation;
    }

    /**
     * @param string $affiliation
     *
     * @return ActionField
     */
    public function setAffiliation(string $affiliation): ActionField
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    /**
     * @return float
     */
    public function getRevenue(): float
    {
        return $this->revenue;
    }

    /**
     * @param float $revenue
     *
     * @return ActionField
     */
    public function setRevenue(float $revenue): ActionField
    {
        $this->revenue = $revenue;

        return $this;
    }

    /**
     * @return float
     */
    public function getTax(): float
    {
        return $this->tax;
    }

    /**
     * @param float $tax
     *
     * @return ActionField
     */
    public function setTax(float $tax): ActionField
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * @return float
     */
    public function getShipping(): float
    {
        return $this->shipping;
    }

    /**
     * @param float $shipping
     *
     * @return ActionField
     */
    public function setShipping(float $shipping): ActionField
    {
        $this->shipping = $shipping;

        return $this;
    }

    /**
     * @return string
     */
    public function getCoupon(): string
    {
        return $this->coupon;
    }

    /**
     * @param string $coupon
     *
     * @return ActionField
     */
    public function setCoupon(string $coupon): ActionField
    {
        $this->coupon = $coupon;

        return $this;
    }
}
