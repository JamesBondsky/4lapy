<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 25.03.2019
 * Time: 16:45
 */

namespace FourPaws\PersonalBundle\Entity;


use FourPaws\AppBundle\Entity\BaseEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class OrderSubscribeItem extends BaseEntity
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_SUBSCRIBE_ID")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $subscribeId;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_OFFER_ID")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $offerId;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_QUANTITY")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $quantity;

    /**
     * @return int
     */
    public function getSubscribeId(): int
    {
        return $this->subscribeId;
    }

    /**
     * @param int $subscribeId
     */
    public function setSubscribeId(int $subscribeId): void
    {
        $this->subscribeId = $subscribeId;
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