<?php

namespace FourPaws\EcommerceBundle\Dto\GoogleEcommerce;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Ecommerce
 *
 * @package FourPaws\EcommerceBundle\Dto\GoogleEcommerce
 */
class Ecommerce
{
    /**
     * Код валюты.
     *
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $currencyCode;

    /**
     * @Serializer\Type("ArrayCollection<FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Product>")
     * @Serializer\SkipWhenEmpty()
     *
     * @var Collection|Product[]
     */
    protected $impressions;

    /**
     * @Serializer\Type("FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Action")
     * @Serializer\SkipWhenEmpty()
     *
     * @var Action
     */
    protected $click;

    /**
     * @Serializer\Type("FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Action")
     * @Serializer\SkipWhenEmpty()
     *
     * @var Action
     */
    protected $detail;

    /**
     * @Serializer\Type("FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Action")
     * @Serializer\SkipWhenEmpty()
     *
     * @var Action
     */
    protected $add;

    /**
     * @Serializer\Type("FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Action")
     * @Serializer\SkipWhenEmpty()
     *
     * @var Action
     */
    protected $remove;

    /**
     * @Serializer\Type("FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Action")
     * @Serializer\SkipWhenEmpty()
     *
     * @var Action
     */
    protected $checkout;

    /**
     * @Serializer\Type("FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Action")
     * @Serializer\SkipWhenEmpty()
     *
     * @var Action
     */
    protected $purchase;

    /**
     * @Serializer\Type("FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Action")
     * @Serializer\SkipWhenEmpty()
     *
     * @var Action
     */
    public $promoClick;

    /**
     * @Serializer\Type("FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Action")
     * @Serializer\SkipWhenEmpty()
     *
     * @var Action
     */
    public $promoView;

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     *
     * @return Ecommerce
     */
    public function setCurrencyCode(string $currencyCode): Ecommerce
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getImpressions()
    {
        return $this->impressions;
    }

    /**
     * @param Collection|Product[] $impressions
     *
     * @return Ecommerce
     */
    public function setImpressions($impressions): Ecommerce
    {
        $this->impressions = $impressions;

        return $this;
    }

    /**
     * @return Action
     */
    public function getClick(): Action
    {
        return $this->click;
    }

    /**
     * @param Action $click
     *
     * @return Ecommerce
     */
    public function setClick(Action $click): Ecommerce
    {
        $this->click = $click;

        return $this;
    }

    /**
     * @return Action
     */
    public function getDetail(): Action
    {
        return $this->detail;
    }

    /**
     * @param Action $detail
     *
     * @return Ecommerce
     */
    public function setDetail(Action $detail): Ecommerce
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * @return Action
     */
    public function getAdd(): Action
    {
        return $this->add;
    }

    /**
     * @param Action $add
     *
     * @return Ecommerce
     */
    public function setAdd(Action $add): Ecommerce
    {
        $this->add = $add;

        return $this;
    }

    /**
     * @return Action
     */
    public function getRemove(): Action
    {
        return $this->remove;
    }

    /**
     * @param Action $remove
     *
     * @return Ecommerce
     */
    public function setRemove(Action $remove): Ecommerce
    {
        $this->remove = $remove;

        return $this;
    }

    /**
     * @return Action
     */
    public function getCheckout(): Action
    {
        return $this->checkout;
    }

    /**
     * @param Action $checkout
     *
     * @return Ecommerce
     */
    public function setCheckout(Action $checkout): Ecommerce
    {
        $this->checkout = $checkout;

        return $this;
    }

    /**
     * @return Action
     */
    public function getPurchase(): Action
    {
        return $this->purchase;
    }

    /**
     * @param Action $purchase
     *
     * @return Ecommerce
     */
    public function setPurchase(Action $purchase): Ecommerce
    {
        $this->purchase = $purchase;

        return $this;
    }

    /**
     * @return Action
     */
    public function getPromoClick(): Action
    {
        return $this->promoClick;
    }

    /**
     * @param Action $promoClick
     *
     * @return Ecommerce
     */
    public function setPromoClick(Action $promoClick): Ecommerce
    {
        $this->promoClick = $promoClick;

        return $this;
    }

    /**
     * @return Action
     */
    public function getPromoView(): Action
    {
        return $this->promoView;
    }

    /**
     * @param Action $promoView
     *
     * @return Ecommerce
     */
    public function setPromoView(Action $promoView): Ecommerce
    {
        $this->promoView = $promoView;

        return $this;
    }


}
