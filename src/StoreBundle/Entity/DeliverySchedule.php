<?php

namespace FourPaws\StoreBundle\Entity;

use FourPaws\App\Application;
use FourPaws\StoreBundle\Collection\DeliveryScheduleCollection;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Service\DeliveryScheduleService;
use FourPaws\StoreBundle\Service\StoreService;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

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
     * @var bool
     * @Serializer\SerializedName("UF_ACTIVE")
     * @Serializer\Type("bool")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $active = true;

    /**
     * @var array
     * @Serializer\SerializedName("UF_WEEK_NUMBER")
     * @Serializer\Type("array<int>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $weekNumbers = [];

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
     * @return Store
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getReceiver(): Store
    {
        if (null === $this->receiver) {
            /** @var StoreService $storeService */
            $storeService = Application::getInstance()->getContainer()->get('store.service');
            $this->setReceiver($storeService->getByXmlId($this->getReceiverCode()));
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
     * @return Store
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getSender(): Store
    {
        if (null === $this->receiver) {
            /** @var StoreService $storeService */
            $storeService = Application::getInstance()->getContainer()->get('store.service');
            $this->setReceiver($storeService->getByXmlId($this->getSenderCode()));
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
     * @return DeliveryScheduleCollection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws NotFoundException
     */
    public function getReceiverSchedules(): DeliveryScheduleCollection
    {
        if (null === $this->receiverSchedules) {
            /** @var DeliveryScheduleService $scheduleService */
            $scheduleService = Application::getInstance()->getContainer()->get(DeliveryScheduleService::class);
            $this->setReceiverSchedules($scheduleService->findByReceiver($this->getReceiver()));
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
     * @return DeliveryScheduleCollection
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getSenderSchedules(): DeliveryScheduleCollection
    {
        if (null === $this->senderSchedules) {
            /** @var DeliveryScheduleService $scheduleService */
            $scheduleService = Application::getInstance()->getContainer()->get(DeliveryScheduleService::class);
            $this->setSenderSchedules($scheduleService->findBySender($this->getSender()));
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
}
