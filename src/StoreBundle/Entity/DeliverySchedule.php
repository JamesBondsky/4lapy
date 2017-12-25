<?php

namespace FourPaws\StoreBundle\Entity;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

class DeliverySchedule extends Base
{
    const TYPE_WEEKLY = '1';

    const TYPE_BY_WEEK = '2';

    const TYPE_MANUAL = '8';

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"read","update","delete"})
     * @Assert\Blank(groups={"create"})
     */
    protected $id = 0;

    /**
     * @var string
     * @Serializer\SerializedName("UF_NAME")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $name = '';

    /**
     * @var string
     * @Serializer\SerializedName("UF_SENDER")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $sender = '';

    /**
     * @var string
     * @Serializer\SerializedName("UF_RECEIVER")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $receiver = '';

    /**
     * @var DateTime
     * @Serializer\SerializedName("UF_ACTIVE_FROM")
     * @Serializer\Type("DateTime<'d.m.Y H:i:s'>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $activeFrom;

    /**
     * @var DateTime
     * @Serializer\SerializedName("UF_ACTIVE_TO")
     * @Serializer\Type("DateTime<'d.m.Y H:i:s'>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $activeTo;

    /**
     * @var bool
     * @Serializer\SerializedName("UF_ACTIVE")
     * @Serializer\Type("bool")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $active = true;

    /**
     * @var string
     * @Serializer\SerializedName("UF_TYPE")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $type = '';

    /**
     * @var int
     * @Serializer\SerializedName("UF_WEEK_NUMBER")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $weekNumber = 0;

    /**
     * @var string
     * @Serializer\SerializedName("UF_DAY_OF_WEEK")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $dayOfWeek;

    /**
     * @var string
     * @Serializer\SerializedName("UF_DELIVERY_NUMBER")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $deliveryNumber;

    /**
     * @var DateTime
     * @Serializer\SerializedName("UF_DELIVERY_DATE")
     * @Serializer\Type("DateTime<'d.m.Y'>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $deliveryDate;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return DeliverySchedule
     */
    public function setId(int $id): DeliverySchedule
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return DeliverySchedule
     */
    public function setName(string $name): DeliverySchedule
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSender(): string
    {
        return $this->sender;
    }

    /**
     * @param string $sender
     *
     * @return DeliverySchedule
     */
    public function setSender(string $sender): DeliverySchedule
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return string
     */
    public function getReceiver(): string
    {
        return $this->receiver;
    }

    /**
     * @param string $receiver
     *
     * @return DeliverySchedule
     */
    public function setReceiver(string $receiver): DeliverySchedule
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getActiveFrom(): DateTime
    {
        return $this->activeFrom;
    }

    /**
     * @param DateTime $activeFrom
     *
     * @return DeliverySchedule
     */
    public function setActiveFrom(DateTime $activeFrom): DeliverySchedule
    {
        $this->activeFrom = $activeFrom;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getActiveTo(): DateTime
    {
        return $this->activeTo;
    }

    /**
     * @param DateTime $activeTo
     *
     * @return DeliverySchedule
     */
    public function setActiveTo(DateTime $activeTo): DeliverySchedule
    {
        $this->activeTo = $activeTo;

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
     *
     * @return DeliverySchedule
     */
    public function setActive(bool $active): DeliverySchedule
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return DeliverySchedule
     */
    public function setType(string $type): DeliverySchedule
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getWeekNumber(): int
    {
        return $this->weekNumber;
    }

    /**
     * @param int $weekNumber
     *
     * @return DeliverySchedule
     */
    public function setWeekNumber(int $weekNumber): DeliverySchedule
    {
        $this->weekNumber = $weekNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getDayOfWeek(): string
    {
        return $this->dayOfWeek;
    }

    /**
     * @param string $dayOfWeek
     *
     * @return DeliverySchedule
     */
    public function setDayOfWeek(string $dayOfWeek): DeliverySchedule
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryNumber(): string
    {
        return $this->deliveryNumber;
    }

    /**
     * @param string $deliveryNumber
     *
     * @return DeliverySchedule
     */
    public function setDeliveryNumber(string $deliveryNumber): DeliverySchedule
    {
        $this->deliveryNumber = $deliveryNumber;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDeliveryDate(): DateTime
    {
        return $this->deliveryDate;
    }

    /**
     * @param DateTime $deliveryDate
     *
     * @return DeliverySchedule
     */
    public function setDeliveryDate(DateTime $deliveryDate): DeliverySchedule
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }
}
