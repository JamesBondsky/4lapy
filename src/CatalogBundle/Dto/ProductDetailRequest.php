<?php

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\DeliveryBundle\Service\DeliveryService;

/**
 * Class ProductDetailRequest
 *
 * @package FourPaws\CatalogBundle\Dto
 */
class ProductDetailRequest
{
    protected $productSlug = '';
    protected $sectionSlug = '';
    protected $offerId = 0;
    protected $zone = DeliveryService::ZONE_1;

    /**
     * @return string
     */
    public function getProductSlug(): string
    {
        return $this->productSlug;
    }

    /**
     * @param string $productSlug
     *
     * @return ProductDetailRequest
     */
    public function setProductSlug(string $productSlug): ProductDetailRequest
    {
        $this->productSlug = $productSlug;

        return $this;
    }

    /**
     * @return string
     */
    public function getSectionSlug(): string
    {
        return $this->sectionSlug;
    }

    /**
     * @param string $sectionSlug
     *
     * @return ProductDetailRequest
     */
    public function setSectionSlug(string $sectionSlug): ProductDetailRequest
    {
        $this->sectionSlug = $sectionSlug;

        return $this;
    }

    /**
     * @return int
     */
    public function getOfferId(): int
    {
        return $this->offerId;
    }

    /**
     * @param int $offerId
     *
     * @return $this
     */
    public function setOfferId(int $offerId): self
    {
        $this->offerId = $offerId;

        return $this;
    }

    /**
     * @return string
     */
    public function getZone(): string
    {
        return $this->zone;
    }

    /**
     * @param string $zone
     *
     * @return $this
     */
    public function setZone(string $zone): self
    {
        $this->zone = $zone;

        return $this;
    }
}
