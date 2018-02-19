<?php

namespace FourPaws\PersonalBundle\Entity;

use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\StatusLangTable;
use Bitrix\Sale\Internals\StatusTable;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\Helpers\DateHelper;
use FourPaws\StoreBundle\Entity\Store;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class OrderSubscribe extends BaseEntity
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_ORDER_ID")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $orderId;
    /**
     * @var Date
     * @Serializer\Type("bitrix_date")
     * @Serializer\SerializedName("UF_DATE_CREATE")
     * @Serializer\Groups(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateCreate;
    /**
     * @var Date
     * @Serializer\Type("bitrix_date")
     * @Serializer\SerializedName("UF_DATE_START")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateStart;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_FREQUENCY")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $deliveryFrequency;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_DELIVERY_TIME")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $deliveryTime;
    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("UF_ACTIVE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $active;

    /**
     * @return int
     */
    public function getOrderId() : int
    {
        return $this->orderId ?? 0;
    }

    /**
     * @param int $orderId
     *
     * @return self
     */
    public function setOrderId(int $orderId) : self
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @return null|Date
     */
    public function getDateCreate()
    {
        return $this->dateCreate ?? null;
    }

    /**
     * @param null|Date|string $dateCreate
     *
     * @return self
     */
    public function setDateCreate($dateCreate) : self
    {
        if ($dateCreate instanceof Date) {
            $this->dateCreate = $dateCreate;
        } else {
            if (is_scalar($dateCreate)) {
                $this->dateCreate = new Date($dateCreate, 'd.m.Y H:i:s');
            } else {
                $this->dateCreate = new Date('', 'd.m.Y H:i:s');
            }
        }

        return $this;
    }

    /**
     * @return null|Date
     */
    public function getDateStart()
    {
        return $this->dateStart ?? null;
    }

    /**
     * @param null|Date|string $dateStart
     *
     * @return self
     */
    public function setDateStart($dateStart) : self
    {
        if ($dateStart instanceof Date) {
            $this->dateStart = $dateStart;
        } else {
            if (is_scalar($dateStart)) {
                $this->dateStart = new Date($dateStart, 'd.m.Y');
            } else {
                $this->dateStart = null;
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryFrequency() : string
    {
        return $this->deliveryFrequency ?? '';
    }

    /**
     * @param string $deliveryFrequency
     *
     * @return self
     */
    public function setDeliveryFrequency(string $deliveryFrequency) : self
    {
        $this->deliveryFrequency = $deliveryFrequency;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryTime() : string
    {
        return $this->deliveryTime ?? '';
    }

    /**
     * @param string $deliveryTime
     *
     * @return self
     */
    public function setDeliveryTime(string $deliveryTime) : self
    {
        $this->deliveryTime = $deliveryTime;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive() : bool
    {
        return $this->active ?? true;
    }

    /**
     * @param bool $active
     *
     * @return self
     */
    public function setActive(bool $active) : self
    {
        $this->active = $active;

        return $this;
    }
}
