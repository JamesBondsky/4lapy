<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 25.03.2019
 * Time: 16:45
 */

namespace FourPaws\PersonalBundle\Entity;


use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\PersonalBundle\Exception\NotFoundException;
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
     * @var Offer $offer
     */
    protected $offer;


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
    public function setSubscribeId(int $subscribeId): OrderSubscribeItem
    {
        $this->subscribeId = $subscribeId;
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
     */
    public function setOfferId(int $offerId): OrderSubscribeItem
    {
        $this->offerId = $offerId;
        return $this;
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
    public function setQuantity(int $quantity): OrderSubscribeItem
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return Offer
     * @throws NotFoundException
     */
    public function getOffer(): Offer
    {
        if(null === $this->offer){
            $offer = (new OfferQuery())->getById($this->getOfferId());
            if(!$offer){
                throw new NotFoundException(sprintf("Offer not found %s", $this->getOfferId()));
            }
            $this->offer = $offer;
        }
        return $this->offer;
    }
}