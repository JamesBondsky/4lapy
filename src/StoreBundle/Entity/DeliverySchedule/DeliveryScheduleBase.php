<?php

namespace FourPaws\StoreBundle\Entity\DeliverySchedule;

use FourPaws\StoreBundle\Entity\Base;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

abstract class DeliveryScheduleBase extends Base
{
    /**
     * Еженедельный
     */
    public const TYPE_WEEKLY = '1';

    /**
     * По определенным неделям
     */
    public const TYPE_BY_WEEK = '2';

    /**
     * Ручной
     */
    public const TYPE_MANUAL = '8';

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
     * @var int
     * @Serializer\SerializedName("UF_WEEK_NUMBER")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $weekNumber = 0;

    /**
     * @var array
     * @Serializer\SerializedName("UF_DAY_OF_WEEK")
     * @Serializer\Type("array<int>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $daysOfWeek;

    /**
     * @var string
     * @Serializer\SerializedName("UF_DELIVERY_NUMBER")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $deliveryNumber;

    /**
     * @var DateTime[]
     * @Serializer\SerializedName("UF_DELIVERY_DATE")
     * @Serializer\Type("array<DateTime<'d.m.Y'>>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $deliveryDates;

    /**
     * @var string
     */
    protected $type = '';

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
     * @return DeliveryScheduleBase
     */
    public function setId(int $id): DeliveryScheduleBase
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
     * @return DeliveryScheduleBase
     */
    public function setName(string $name): DeliveryScheduleBase
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
     * @return DeliveryScheduleBase
     */
    public function setSender(string $sender): DeliveryScheduleBase
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
     * @return DeliveryScheduleBase
     */
    public function setReceiver(string $receiver): DeliveryScheduleBase
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
     * @return DeliveryScheduleBase
     */
    public function setActiveFrom(DateTime $activeFrom): DeliveryScheduleBase
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
     * @return DeliveryScheduleBase
     */
    public function setActiveTo(DateTime $activeTo): DeliveryScheduleBase
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
     * @return DeliveryScheduleBase
     */
    public function setActive(bool $active): DeliveryScheduleBase
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
     * @return int
     */
    public function getWeekNumber(): int
    {
        return $this->weekNumber;
    }

    /**
     * @param int $weekNumber
     *
     * @return DeliveryScheduleBase
     */
    public function setWeekNumber(int $weekNumber): DeliveryScheduleBase
    {
        $this->weekNumber = $weekNumber;

        return $this;
    }

    /**
     * @return array
     */
    public function getDaysOfWeek(): array
    {
        return $this->daysOfWeek ?? [];
    }

    /**
     * @param array $daysOfWeek
     *
     * @return DeliveryScheduleBase
     */
    public function setDaysOfWeek(array $daysOfWeek): DeliveryScheduleBase
    {
        $this->daysOfWeek = $daysOfWeek;

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
     * @return DeliveryScheduleBase
     */
    public function setDeliveryNumber(string $deliveryNumber): DeliveryScheduleBase
    {
        $this->deliveryNumber = $deliveryNumber;

        return $this;
    }

    /**
     * @return DateTime[]
     */
    public function getDeliveryDates(): array
    {
        return $this->deliveryDates ?? [];
    }

    /**
     * @param DateTime[] $deliveryDates
     *
     * @return DeliveryScheduleBase
     */
    public function setDeliveryDates(array $deliveryDates): DeliveryScheduleBase
    {
        $this->deliveryDates = $deliveryDates;

        return $this;
    }

    /**
     * @param DateTime $date
     * @return bool
     */
    public function isActiveForDate(DateTime $date): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->activeFrom && $this->activeFrom > $date) {
            return false;
        }

        if ($this->activeTo && $this->activeTo < $date) {
            return false;
        }

        return true;
    }

    /**
     * @param DateTime $from
     * @return DateTime|null
     */
    public function getNextDelivery(DateTime $from = null): ?DateTime
    {
        if (!$from instanceof DateTime) {
            $from = new DateTime();
        }

        if (!$this->isActiveForDate($from)) {
            return null;
        }

        return $this->doGetNextDelivery($from);
    }

    /**
     * @param DateTime $from
     * @return DateTime|null
     */
    abstract protected function doGetNextDelivery(DateTime $from): ?DateTime;
}
