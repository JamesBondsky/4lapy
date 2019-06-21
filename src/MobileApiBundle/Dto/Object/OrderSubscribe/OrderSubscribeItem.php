<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 21.06.2019
 * Time: 14:08
 */

namespace FourPaws\MobileApiBundle\Dto\Object\OrderSubscribe;


use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class OrderSubscribeItem
{
    use PropertiesFillingTrait;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("subscribeId")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $subscribeId;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("offerId")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $offerId;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("quantity")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $quantity;

    /**
     * OrderSubscribeItem constructor.
     * @param $transferObject
     * @throws \Exception
     */
    public function __construct($transferObject)
    {
        $this->fillProperties($transferObject);
    }


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
}