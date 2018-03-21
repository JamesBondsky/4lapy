<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Entity;

use DateTime;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\StoreBundle\Collection\DeliveryScheduleCollection;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Service\DeliveryScheduleService;
use FourPaws\StoreBundle\Service\StoreService;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DeliverySchedule
 *
 * @package FourPaws\StoreBundle\Entity
 */
class DeliverySchedule extends Base
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
     *
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     *
     * @Assert\NotBlank(groups={"read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"read","update","delete"})
     * @Assert\Blank(groups={"create"})
     */
    protected $id;

    /**
     * @var string
     * @Serializer\SerializedName("UF_NAME")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $name = '';

    /**
     * @var string
     * @Serializer\SerializedName("UF_XML_ID")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $xmlId = '';

    /**
     * @var string
     * @Serializer\SerializedName("UF_SENDER")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $senderCode = '';

    /**
     * @var string
     * @Serializer\SerializedName("UF_RECEIVER")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $receiverCode = '';

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
     * @var int[]
     * @Serializer\SerializedName("UF_WEEK_NUMBER")
     * @Serializer\Type("array_or_false<int>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $weekNumbers;

    /**
     * @var int[]
     * @Serializer\SerializedName("UF_DAY_OF_WEEK")
     * @Serializer\Type("array_or_false<int>")
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
     * @Serializer\Type("array_or_false<DateTime<'d.m.Y'>>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $deliveryDates;

    /**
     * @var string
     * @Serializer\SerializedName("UF_TYPE")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $type = '';

    /**
     * @var Store
     */
    protected $sender;

    /**
     * @var Store
     */
    protected $receiver;

    /**
     * @var DeliveryScheduleCollection
     */
    protected $receiverSchedules;

    /**
     * @var DeliveryScheduleCollection
     */
    protected $senderSchedules;


    /**
     * @var DeliveryScheduleService
     */
    protected $scheduleService;

    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * DeliverySchedule constructor.
     * @throws ApplicationCreateException
     */
    public function __construct()
    {
        $this->storeService = Application::getInstance()->getContainer()->get('store.service');
        $this->scheduleService = Application::getInstance()->getContainer()->get(DeliveryScheduleService::class);
    }

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
    public function getSenderCode(): string
    {
        return $this->senderCode;
    }

    /**
     * @param string $senderCode
     *
     * @return DeliverySchedule
     */
    public function setSenderCode(string $senderCode): DeliverySchedule
    {
        $this->senderCode = $senderCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getReceiverCode(): string
    {
        return $this->receiverCode;
    }

    /**
     * @param string $receiverCode
     *
     * @return DeliverySchedule
     */
    public function setReceiverCode(string $receiverCode): DeliverySchedule
    {
        $this->receiverCode = $receiverCode;

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
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type ;
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
     * @return array
     */
    public function getWeekNumbers(): array
    {
        return $this->weekNumbers;
    }

    /**
     * @param array $weekNumbers
     * @return DeliverySchedule
     */
    public function setWeekNumbers(array $weekNumbers): DeliverySchedule
    {
        $this->weekNumbers = $weekNumbers;
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
     * @return DeliverySchedule
     */
    public function setDaysOfWeek(array $daysOfWeek): DeliverySchedule
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
     * @return DeliverySchedule
     */
    public function setDeliveryNumber(string $deliveryNumber): DeliverySchedule
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
     * @return DeliverySchedule
     */
    public function setDeliveryDates(array $deliveryDates): DeliverySchedule
    {
        $this->deliveryDates = $deliveryDates;

        return $this;
    }

    /**
     * @noinspection MultipleReturnStatementsInspection
     *
     * @param DateTime $date
     * @return bool
     */
    public function isActiveForDate(DateTime $date): bool
    {
        if ($this->activeFrom && $this->activeFrom > $date) {
            return false;
        }

        if ($this->activeTo && $this->activeTo < $date) {
            return false;
        }

        return true;
    }

    /**
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @return Store
     */
    public function getReceiver(): Store
    {
        if (null === $this->receiver) {
            $this->setReceiver($this->storeService->getByXmlId($this->getReceiverCode()));
        }
        return $this->receiver;
    }

    /**
     * @param Store $receiver
     * @return DeliverySchedule
     */
    public function setReceiver(Store $receiver): DeliverySchedule
    {
        $this->receiver = $receiver;
        return $this;
    }

    /**
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @return Store
     */
    public function getSender(): Store
    {
        if (null === $this->sender) {
            $this->setSender($this->storeService->getByXmlId($this->getSenderCode()));
        }
        return $this->sender;
    }

    /**
     * @param Store $sender
     * @return DeliverySchedule
     */
    public function setSender(Store $sender): DeliverySchedule
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return string
     */
     public function getXmlId(): string
     {
        return $this->xmlId;
     }

    /**
     * @param string $xmlId
     *
     * @return DeliverySchedule
     */
    public function setXmlId(string $xmlId): DeliverySchedule
    {
        $this->xmlId = $xmlId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTypeCode(): ?string
    {
        return $this->scheduleService->getTypeCodeById((int)$this->getType());
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws NotFoundException
     * @return DeliveryScheduleCollection
     */
    public function getReceiverSchedules(): DeliveryScheduleCollection
    {
        if (null === $this->receiverSchedules) {
            $this->setReceiverSchedules($this->scheduleService->findBySender($this->getReceiver()));
        }
        return $this->receiverSchedules;
    }

    /**
     * @param DeliveryScheduleCollection $receiverSchedules
     * @return DeliverySchedule
     */
    public function setReceiverSchedules(DeliveryScheduleCollection $receiverSchedules): DeliverySchedule
    {
        $this->receiverSchedules = $receiverSchedules;
        return $this;
    }

    /**
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @return DeliveryScheduleCollection
     */
    public function getSenderSchedules(): DeliveryScheduleCollection
    {
        if (null === $this->senderSchedules) {
            $this->setSenderSchedules($this->scheduleService->findByReceiver($this->getSender()));
        }

        return $this->senderSchedules;
    }

    /**
     * @param DeliveryScheduleCollection $senderSchedules
     * @return DeliverySchedule
     */
    public function setSenderSchedules(DeliveryScheduleCollection $senderSchedules): DeliverySchedule
    {
        $this->senderSchedules = $senderSchedules;
        return $this;
    }

    /**
     * @param DateTime $from
     * @return null|DateTime
     */
    public function getNextDelivery(DateTime $from = null): ?DateTime
    {
        if (!$from instanceof DateTime) {
            $from = new DateTime();
        }

        if (!$this->isActiveForDate($from)) {
            return null;
        }

        /**
         * @param DateTime $from
         * @return null|DateTime
         */
        $getByDay = function (DateTime $from): ?DateTime {
            $fromDay = (int)$from->format('N');
            /** @var DateTime[] $results */
            $results = [];
            /** @var int $day */
            foreach ($this->getDaysOfWeek() as $day) {
                $date = clone $from;
                $diff = $day - $fromDay;
                $days = ($diff >= 0) ? $diff : $diff + 7;

                $date->modify(sprintf('+%s days', $days));
                $results[] = $date;
            }

            if (!empty($results)) {
                return max($results);
            }

            return null;
        };

        $result = null;
        $date = clone $from;
        switch ($this->getTypeCode()) {
            case self::TYPE_MANUAL:
                $results = [];
                $date->setTime(0, 0, 0, 0);

                /** @var \DateTime $deliveryDate */
                foreach ($this->deliveryDates as $deliveryDate) {
                    if (!$deliveryDate instanceof DateTime) {
                        continue;
                    }

                    if ($deliveryDate > $date) {
                        $results[] = $deliveryDate;
                    }
                }

                if (!empty($results)) {
                    $result = min($results);
                }
                break;
            case self::TYPE_BY_WEEK:
                $weekNumbers = $this->getWeekNumbers();
                $weekDates = [];
                $weekNumbers[] = 0;
                foreach ($weekNumbers as $weekNumber) {
                    $weekDate = clone $date;
                    $weekDate->setISODate($date->format('Y'), $weekNumber - 1);
                    if ($weekDate->format('W') < $from->format('W')) {
                        $weekDate->modify('+1 year');
                    }

                    $weekDates[] = ($weekDate > $from) ? $weekDate : $from;
                }

                $result = !empty($weekDates) ? $getByDay(min($weekDates)) : null;
                break;
            case self::TYPE_WEEKLY:
                $result =  $getByDay($from);
                break;
        }

        /** Результат за пределами активности графика */
        if ($result && !$this->isActiveForDate($result)) {
            return null;
        }

        return $result;
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @return string
     */
    public function serialize(): string
    {
        return \serialize([
            $this->id,
            $this->name,
            $this->xmlId,
            $this->senderCode,
            $this->receiverCode,
            $this->activeFrom,
            $this->activeTo,
            $this->weekNumbers,
            $this->daysOfWeek,
            $this->deliveryNumber,
            $this->deliveryDates,
            $this->type
        ]);
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param string $serialized
     * @throws ApplicationCreateException
     */
    public function unserialize($serialized): void
    {
        [
            $this->id,
            $this->name,
            $this->xmlId,
            $this->senderCode,
            $this->receiverCode,
            $this->activeFrom,
            $this->activeTo,
            $this->weekNumbers,
            $this->daysOfWeek,
            $this->deliveryNumber,
            $this->deliveryDates,
            $this->type
        ] = \unserialize($serialized, ['allowed_classes' => true]);

        $this->__construct();
    }
}
