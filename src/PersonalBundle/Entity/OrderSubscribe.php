<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 25.03.2019
 * Time: 16:18
 */

namespace FourPaws\PersonalBundle\Entity;


use Bitrix\Main\Type\DateTime;
use FourPaws\AppBundle\Entity\BaseEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class OrderSubscribe extends BaseEntity
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_USER_ID")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $userId;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_DEL_TYPE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $deliveryId;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_FREQUENCY")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $frequency;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_DELIVERY_TIME")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $deliveryTime;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_DEL_PLACE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $deliveryPlace;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_LOCATION")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $locationId;

    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("UF_ACTIVITY")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $active = true;

    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("UF_SKIP_DEL")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $skipNextDelivery;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_LAST_ORDER")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $lastOrderId;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_NEXT_DEL")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $nextDate;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_DATE_CREATE")
     * @Serializer\Groups(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateCreate;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_DATE_UPDATE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateUpdate;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_DEL_DAY")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $deliveryDay;

    /**
     * @return int
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return OrderSubscribe
     */
    public function setUserId(int $userId): OrderSubscribe
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryId(): ?int
    {
        return $this->deliveryId;
    }

    /**
     * @param int $deliveryId
     * @return OrderSubscribe
     */
    public function setDeliveryId(int $deliveryId): OrderSubscribe
    {
        $this->deliveryId = $deliveryId;
        return $this;
    }

    /**
     * @return int
     */
    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    /**
     * @param int $frequency
     * @return OrderSubscribe
     */
    public function setFrequency(int $frequency): OrderSubscribe
    {
        $this->frequency = $frequency;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryTime(): ?string
    {
        return $this->deliveryTime;
    }

    /**
     * @param string $deliveryTime
     * @return OrderSubscribe
     */
    public function setDeliveryTime(string $deliveryTime): OrderSubscribe
    {
        $this->deliveryTime = $deliveryTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryPlace(): ?string
    {
        return $this->deliveryPlace;
    }

    /**
     * @param string $deliveryPlace
     * @return OrderSubscribe
     */
    public function setDeliveryPlace(string $deliveryPlace): OrderSubscribe
    {
        $this->deliveryPlace = $deliveryPlace;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocationId(): ?string
    {
        return $this->locationId;
    }

    /**
     * @param string $locationId
     * @return OrderSubscribe
     */
    public function setLocationId(string $locationId): OrderSubscribe
    {
        $this->locationId = $locationId;
        return $this;
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
     * @return OrderSubscribe
     */
    public function setActive(bool $active): OrderSubscribe
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSkipNextDelivery(): bool
    {
        return $this->skipNextDelivery;
    }

    /**
     * @param bool $skipNextDelivery
     * @return OrderSubscribe
     */
    public function setSkipNextDelivery(bool $skipNextDelivery): OrderSubscribe
    {
        $this->skipNextDelivery = $skipNextDelivery;
        return $this;
    }

    /**
     * @return int
     */
    public function getLastOrderId(): ?int
    {
        return $this->lastOrderId;
    }

    /**
     * @param int $lastOrderId
     * @return OrderSubscribe
     */
    public function setLastOrderId(int $lastOrderId): OrderSubscribe
    {
        $this->lastOrderId = $lastOrderId;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getNextDate(): ?DateTime
    {
        return $this->nextDate;
    }

    /**
     * @param DateTime $nextDate
     * @return OrderSubscribe
     */
    public function setNextDate(DateTime $nextDate): OrderSubscribe
    {
        $this->nextDate = $nextDate;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreate(): DateTime
    {
        return $this->dateCreate;
    }

    /**
     * @param DateTime $dateCreate
     * @return OrderSubscribe
     */
    public function setDateCreate(DateTime $dateCreate): OrderSubscribe
    {
        $this->dateCreate = $dateCreate;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateUpdate(): ?DateTime
    {
        return $this->dateUpdate;
    }

    /**
     * @param DateTime $dateUpdate
     * @return OrderSubscribe
     */
    public function setDateUpdate(DateTime $dateUpdate): OrderSubscribe
    {
        $this->dateUpdate = $dateUpdate;
        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryDay(): ?int
    {
        return $this->deliveryDay;
    }

    /**
     * @param int $deliveryDay
     * @return OrderSubscribe
     */
    public function setDeliveryDay(int $deliveryDay): OrderSubscribe
    {
        $this->deliveryDay = $deliveryDay;
        return $this;
    }


}