<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

/**
 * ОбъектЗаказИстория
 * Class OrderHistory
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class OrderHistory
{
    /**
     * Статус заказа
     * @Serializer\SerializedName("status")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\OrderStatus")
     * @var OrderStatus
     */
    protected $status;

    /**
     * Дата и время изменения
     * @Serializer\Exclude()
     * @var \DateTime
     */
    protected $dateChange;

    /**
     * @internal
     * @Serializer\SerializedName("date")
     * @Serializer\Type("DateTime<'d.m.Y'>")
     * @var \DateTime
     */
    protected $date;

    /**
     * @internal
     * @Serializer\SerializedName("time")
     * @Serializer\Type("DateTime<'H:i'>")
     * @var \DateTime
     */
    protected $time;

    /**
     * @return \DateTime
     */
    public function getDateChange(): \DateTime
    {
        return $this->dateChange;
    }

    /**
     * @param \DateTime $dateChange
     *
     * @return OrderHistory
     */
    public function setDateChange(\DateTime $dateChange): OrderHistory
    {
        $this->dateChange = $dateChange;
        $this->date = $this->dateChange;
        $this->time = $this->dateChange;
        return $this;
    }

    /**
     * @internal
     * @return string
     */
    public function getTime()
    {
        return $this->dateChange ? $this->dateChange->format('H:i') : '';
    }

    /**
     * @internal
     * @return \DateTime|string
     */
    public function getDate()
    {
        return $this->dateChange ?? '';
    }

    /**
     * @return OrderStatus
     */
    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    /**
     * @param OrderStatus $status
     * @return OrderHistory
     */
    public function setStatus(OrderStatus $status): OrderHistory
    {
        $this->status = $status;
        return $this;
    }
}
