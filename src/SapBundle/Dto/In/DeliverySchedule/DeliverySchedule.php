<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\DeliverySchedule;

use Doctrine\Common\Collections\Collection;
use FourPaws\SapBundle\Dto\In\Orders\Order;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class DeliverySchedule
 *
 * @package FourPaws\SapBundle\Dto\In
 */
class DeliverySchedule
{
    /**
     * Ключ выгрузки. Генерируется из отправителя/получателя.
     *
     * @Serializer\Exclude()
     *
     * @var string
     */
    protected $xmlId;

    /**
     * Отправитель остатка.
     * Содержит код склада или код поставщика.
     * Формат кода склада: DCХХ, формат кода поставщика: 9-значный цифровой код.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("sender")
     *
     * @var string
     */
    protected $senderCode = '';

    /**
     * Получатель остатка.
     * Содержит код склада или магазина.
     * Формат кода склада: DCХХ, формат кода магазина: RХХХ.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("recipient")
     *
     * @var string
     */
    protected $recipientCode = '';

    /**
     * Дата с.
     * Содержит дату начала действия графика, формат: ГГГГММДД.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("DateTime<'Ymd'>")
     * @Serializer\SerializedName("dateFrom")
     *
     * @var \DateTime
     */
    protected $dateFrom;

    /**
     * Дата по.
     * Содержит дату окончания действия графика, формат: ГГГГММДД.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("DateTime<'Ymd'>")
     * @Serializer\SerializedName("dateTo")
     *
     * @var \DateTime
     */
    protected $dateTo;

    /**
     * Тип графика поставки.
     * Единственный выбор из списка значений:
     *   1 – еженедельный;
     *   2 – по определенным неделям;
     *   8 – ручной.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("slType")
     *
     * @var int
     */
    protected $scheduleType;

    /**
     * Индикатор удаления.
     * Содержит индикатор удаления графика поставки. При значении «Х» Система должна удалить график поставки
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("sap_bool")
     * @Serializer\SerializedName("Deleted")
     *
     * @var bool
     */
    protected $deleted = false;

    /**
     * Дни недели.
     * Данные о днях недели для типов графика поставки 1, 2.
     *
     * @Serializer\XmlList(inline=true, entry="weekdays")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\DeliverySchedule\WeekDayItem>")
     *
     * @var Collection|WeekDayItem[]
     */
    protected $weekDays;

    /**
     * @Serializer\SerializedName("weeknums")
     * @Serializer\XmlElement()
     * @Serializer\Type("FourPaws\SapBundle\Dto\In\DeliverySchedule\WeekNums")
     *
     * @var WeekNums
     */
    protected $weekNums;

    /**
     * Дни недели.
     * Данные о днях недели для типов графика поставки 1, 2.
     *
     * @Serializer\XmlList(inline=true, entry="orderdays")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\DeliverySchedule\OrderDayItem>")
     *
     * @var Collection|OrderDayItem[]
     */
    protected $orderDays;

    /**
     * Даты поставки.
     * Даты поставки для типа графика поставки 8.
     *
     * @Serializer\XmlList(inline=true, entry="manualdays")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\DeliverySchedule\ManualDayItem>")
     *
     * @var Collection|ManualDayItem[]
     */
    protected $manualDays;

    /**
     * Регулярность расписания
     * Z1 - регулярное
     * Z2 - нерегулярное
     * Z3 - ТПЗ
     * Z9 - Исключения
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ShedType")
     *
     * @var string
     */
    protected $regular;

    /**
     * @return string
     */
    public function getXmlId(): string
    {
        if (null === $this->xmlId) {
            $this->xmlId = \md5(
                \sprintf(
                    '%s|%s',
                    $this->getSenderCode(),
                    $this->getRecipientCode()
                )
            );
        }

        return $this->xmlId;
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
    public function getRecipientCode(): string
    {
        return $this->recipientCode;
    }

    /**
     * @param string $recipientCode
     * @return DeliverySchedule
     */
    public function setRecipientCode(string $recipientCode): DeliverySchedule
    {
        $this->recipientCode = $recipientCode;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateFrom(): \DateTime
    {
        return $this->dateFrom;
    }

    /**
     * @param \DateTime $dateFrom
     * @return DeliverySchedule
     */
    public function setDateFrom(\DateTime $dateFrom): DeliverySchedule
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateTo(): \DateTime
    {
        return $this->dateTo;
    }

    /**
     * @param \DateTime $dateTo
     * @return DeliverySchedule
     */
    public function setDateTo(\DateTime $dateTo): DeliverySchedule
    {
        $this->dateTo = $dateTo;

        return $this;
    }

    /**
     * @return int
     */
    public function getScheduleType(): int
    {
        return $this->scheduleType;
    }

    /**
     * @param int $scheduleType
     * @return DeliverySchedule
     */
    public function setScheduleType(int $scheduleType): DeliverySchedule
    {
        $this->scheduleType = $scheduleType;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     * @return DeliverySchedule
     */
    public function setDeleted(bool $deleted): DeliverySchedule
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return Collection|WeekDayItem[]
     */
    public function getWeekDays(): Collection
    {
        return $this->weekDays;
    }

    /**
     * @param Collection $weekDays
     * @return DeliverySchedule
     */
    public function setWeekdays(Collection $weekDays): DeliverySchedule
    {
        $this->weekDays = $weekDays;

        return $this;
    }

    /**
     * @return WeekNums
     */
    public function getWeekNums(): ?WeekNums
    {
        return $this->weekNums;
    }

    /**
     * @param WeekNums $weekNums
     * @return DeliverySchedule
     */
    public function setWeekNums(WeekNums $weekNums): DeliverySchedule
    {
        $this->weekNums = $weekNums;

        return $this;
    }

    /**
     * @return Collection|OrderDayItem[]
     */
    public function getOrderDays()
    {
        return $this->orderDays;
    }

    /**
     * @param Collection|OrderDayItem[] $orderDays
     * @return DeliverySchedule
     */
    public function setOrderDays($orderDays): DeliverySchedule
    {
        $this->orderDays = $orderDays;

        return $this;
    }

    /**
     * @return Collection|ManualDayItem[]
     */
    public function getManualDays(): Collection
    {
        return $this->manualDays;
    }

    /**
     * @param Collection $manualDays
     * @return DeliverySchedule
     */
    public function setManualDays(Collection $manualDays): DeliverySchedule
    {
        $this->manualDays = $manualDays;

        return $this;
    }

    /**
     * @param string $regular
     * @return DeliverySchedule
     */
    public function setRegular(string $regular): DeliverySchedule
    {
        $this->regular = $regular;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegular(): string
    {
        return $this->regular;
    }
}
