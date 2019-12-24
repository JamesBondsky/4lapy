<?php

namespace FourPaws\PersonalBundle\Entity;

use Bitrix\Main\Type\DateTime;
use FourPaws\AppBundle\Entity\BaseEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class CouponPoolCoupon extends BaseEntity
{
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_PROMO_CODE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $promoCode;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_OFFER")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $offerId;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_DATE_CREATED")
     * @Serializer\Groups(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateCreated;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_DATE_CHANGED")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateChanged;

    /**
     * Номер задания на выделение купона из пула (номер >=2).
     * После присвоения купона пользователю проставляется в значение 1 (обработан).
     * Свободные купоны - пустое значение или 0.
     * Если у какого-то промокода остается висеть номер >=2 - это внештатная ситуация, промокод, возможно, не был выдан.
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_TAKEN")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $taken = 0;

    /**
     * @return string
     */
    public function getPromoCode(): string
    {
        return $this->promoCode;
    }

    /**
     * @param string $promoCode
     * @return CouponPoolCoupon
     */
    public function setPromoCode(string $promoCode): CouponPoolCoupon
    {
        $this->promoCode = $promoCode;
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
     * @return CouponPoolCoupon
     */
    public function setOfferId(int $offerId): CouponPoolCoupon
    {
        $this->offerId = $offerId;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated(): DateTime
    {
        return $this->dateCreated;
    }

    /**
     * @param DateTime $dateCreated
     * @return CouponPoolCoupon
     */
    public function setDateCreated(DateTime $dateCreated): CouponPoolCoupon
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateChanged(): DateTime
    {
        return $this->dateChanged;
    }

    /**
     * @param DateTime $dateChanged
     * @return CouponPoolCoupon
     */
    public function setDateChanged(DateTime $dateChanged): CouponPoolCoupon
    {
        $this->dateChanged = $dateChanged;
        return $this;
    }

    /**
     * @return bool
     */
    public function getTaken(): bool
    {
        return $this->taken;
    }

    /**
     * @param bool $taken
     * @return CouponPoolCoupon
     */
    public function setTaken(bool $taken): CouponPoolCoupon
    {
        $this->taken = $taken;
        return $this;
    }
}
