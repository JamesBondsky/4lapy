<?php

namespace FourPaws\Catalog\Model;

use JMS\Serializer\Annotation as Serializer;

class BundleItem
{
    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $id = 0;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("ACTIVE")
     * @Serializer\Groups(groups={"read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $active = true;

    /**
     * @var Offer
     * @Serializer\Type("FourPaws\Catalog\Model\Offer")
     * @Serializer\SerializedName("PRODUCT")
     * @Serializer\Groups(groups={"read"})
     */
    protected $offer;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("PRODUCT_ID")
     * @Serializer\Groups(groups={"read"})
     */
    protected $offerId = 0;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("QUANTITY")
     * @Serializer\Groups(groups={"read"})
     */
    protected $quantity = 1;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return Offer
     */
    public function getOffer(): Offer
    {
        return $this->offer;
    }

    /**
     * @param Offer $offer
     */
    public function setOffer(Offer $offer): void
    {
        $this->offer = $offer;
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
     */
    public function setOfferId(int $offerId): void
    {
        $this->offerId = $offerId;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }
}