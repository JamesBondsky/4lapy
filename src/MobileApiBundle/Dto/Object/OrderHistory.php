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
     * @Serializer\Accessor(setter="setDate", getter="getDate")
     * @Serializer\SerializedName("date")
     * @Serializer\Type("DateTime<'d.m.Y'>")
     * @var string
     */
    protected $date = '';

    /**
     * @internal
     * @Serializer\Accessor(setter="setTime", getter="getTime")
     * @Serializer\SerializedName("time")
     * @Serializer\Type("string")
     * @var string
     */
    protected $time = '';

    /**
     * @Serializer\SerializedName("extra_info")
     * @Serializer\Type("string")
     * @var string
     */
    protected $extraInfo = '';

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
     *
     * @param string $time
     *
     * @return OrderHistory
     */
    public function setTime(string $time = '00:00'): OrderHistory
    {
        $this->dateChange = $this->dateChange instanceof \DateTime ? $this->dateChange : new \DateTime();
        $this->dateChange->setTime(... explode(':', $time));
        return $this;
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
     * @internal
     *
     * @param \DateTime $date
     *
     * @return OrderHistory
     */
    public function setDate(\DateTime $date): OrderHistory
    {
        $this->dateChange = $this->dateChange instanceof \DateTime ? $this->dateChange : $date;
        $this->dateChange->setDate(
            $date->format('Y'),
            $date->format('n'),
            $date->format('j')
        );
        return $this;
    }
}
