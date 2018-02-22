<?php

namespace FourPaws\PersonalBundle\Entity;

use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class OrderSubscribe extends BaseEntity
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_ORDER_ID")
     * @Serializer\Groups(groups={"create","read"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $orderId;
    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("UF_DATE_CREATE")
     * @Serializer\Groups(groups={"create","read"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateCreate;
    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("UF_DATE_EDIT")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateEdit;
    /**
     * @var Date
     * @Serializer\Type("bitrix_date")
     * @Serializer\SerializedName("UF_DATE_START")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateStart;
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_FREQUENCY")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
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
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("UF_ACTIVE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $active;

    /** @var  string */
    private $deliveryFrequencyXmlId;

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
     * @return null|DateTime
     */
    public function getDateCreate()
    {
        return $this->dateCreate ?? null;
    }

    /**
     * @param null|DateTime|string $dateCreate
     *
     * @return self
     */
    public function setDateCreate($dateCreate) : self
    {
        if ($dateCreate instanceof DateTime) {
            $this->dateCreate = $dateCreate;
        } else {
            if (is_scalar($dateCreate)) {
                $this->dateCreate = new DateTime($dateCreate, 'd.m.Y H:i:s');
            } elseif($dateCreate === null) {
                $this->dateCreate = null;
            }
        }

        return $this;
    }

    /**
     * @return null|DateTime
     */
    public function getDateEdit()
    {
        return $this->dateEdit ?? null;
    }

    /**
     * @param null|DateTime|string $dateEdit
     *
     * @return self
     */
    public function setDateEdit($dateEdit) : self
    {
        if ($dateEdit instanceof DateTime) {
            $this->dateEdit = $dateEdit;
        } else {
            if (is_scalar($dateEdit)) {
                $this->dateEdit = new DateTime($dateEdit, 'd.m.Y H:i:s');
            } elseif($dateEdit === null) {
                $this->dateEdit = null;
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
     * @return int
     */
    public function getDeliveryFrequency() : int
    {
        return $this->deliveryFrequency ?? 0;
    }

    /**
     * @param int $deliveryFrequency
     *
     * @return self
     */
    public function setDeliveryFrequency(int $deliveryFrequency) : self
    {
        $this->deliveryFrequency = (int)$deliveryFrequency;
        unset($this->deliveryFrequencyXmlId);

        return $this;
    }

    /**
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getDeliveryFrequencyXmlId() : string
    {
        if (!isset($this->deliveryFrequencyXmlId)) {
            $appCont = Application::getInstance()->getContainer();
            /** @var OrderSubscribeService $orderSubscribeService */
            $orderSubscribeService = $appCont->get('order_subscribe.service');
            $this->deliveryFrequencyXmlId = $orderSubscribeService->getFrequencyXmlId(
                $this->getDeliveryFrequency()
            );
        }

        return $this->deliveryFrequencyXmlId;
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
