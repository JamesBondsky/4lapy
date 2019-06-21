<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 20.06.2019
 * Time: 19:15
 */

namespace FourPaws\MobileApiBundle\Dto\Object\OrderSubscribe;
use FourPaws\App\Application;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use JMS\Serializer\Annotation as Serializer;

class OrderSubscribe
{
    use PropertiesFillingTrait;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     */
    protected $id;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("userId")
     */
    protected $userId;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("deliveryId")
     */
    protected $deliveryId;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("frequency")
     */
    protected $frequency;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("deliveryTime")
     */
    protected $deliveryTime;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("deliveryPlace")
     */
    protected $deliveryPlace;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("locationId")
     */
    protected $locationId;

    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("active")
     */
    protected $active = true;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("orderId")
     */
    protected $orderId;

    /**
     * @var \DateTime
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("nextDate")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $nextDate;

    /**
     * @var \DateTime
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("dateCreate")
     * @Serializer\Groups(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateCreate;

    /**
     * @var \DateTime
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("dateUpdate")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateUpdate;

    /**
     * @var \DateTime
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("lastCheck")
     * @Serializer\Groups(groups={"create","read","update"})
     */
    protected $lastCheck;

    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("payWithbonus")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $payWithbonus = false;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("checkDays")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $checkDays;

    /**
     * @var \DateTime
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("dateCheck")
     * @Serializer\Groups(groups={"create","read","update"})
     */
    protected $dateCheck;

    /**
     * @var OrderSubscribeItem[]
     * @Serializer\Type("array")
     * @Serializer\SerializedName("items")
     * @Serializer\Groups(groups={"create","read","update"})
     */
    protected $items;

    /**
     * OrderSubscribe constructor.
     * @param $transferObjectType
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function __construct($transferObjectType)
    {
        $this->fillProperties($transferObjectType);
        $this->fillItems();
    }

    /**
     * @return int
     */
    public function getUserId(): int
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
    public function getDeliveryId(): int
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
    public function getFrequency(): int
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
    public function getDeliveryTime(): string
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
    public function getDeliveryPlace(): string
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
    public function getLocationId(): string
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
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     * @return OrderSubscribe
     */
    public function setOrderId(int $orderId): OrderSubscribe
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getNextDate(): \DateTime
    {
        return $this->nextDate;
    }

    /**
     * @param \DateTime $nextDate
     * @return OrderSubscribe
     */
    public function setNextDate(\DateTime $nextDate): OrderSubscribe
    {
        $this->nextDate = $nextDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreate(): \DateTime
    {
        return $this->dateCreate;
    }

    /**
     * @param \DateTime $dateCreate
     * @return OrderSubscribe
     */
    public function setDateCreate(\DateTime $dateCreate): OrderSubscribe
    {
        $this->dateCreate = $dateCreate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdate(): \DateTime
    {
        return $this->dateUpdate;
    }

    /**
     * @param \DateTime $dateUpdate
     * @return OrderSubscribe
     */
    public function setDateUpdate(\DateTime $dateUpdate): OrderSubscribe
    {
        $this->dateUpdate = $dateUpdate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastCheck(): \DateTime
    {
        return $this->lastCheck;
    }

    /**
     * @param \DateTime $lastCheck
     * @return OrderSubscribe
     */
    public function setLastCheck(\DateTime $lastCheck): OrderSubscribe
    {
        $this->lastCheck = $lastCheck;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPayWithbonus(): bool
    {
        return $this->payWithbonus;
    }

    /**
     * @param bool $payWithbonus
     * @return OrderSubscribe
     */
    public function setPayWithbonus(bool $payWithbonus): OrderSubscribe
    {
        $this->payWithbonus = $payWithbonus;
        return $this;
    }

    /**
     * @return int
     */
    public function getCheckDays(): int
    {
        return $this->checkDays;
    }

    /**
     * @param int $checkDays
     * @return OrderSubscribe
     */
    public function setCheckDays(int $checkDays): OrderSubscribe
    {
        $this->checkDays = $checkDays;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCheck(): \DateTime
    {
        return $this->dateCheck;
    }

    /**
     * @param \DateTime $dateCheck
     * @return OrderSubscribe
     */
    public function setDateCheck(\DateTime $dateCheck): OrderSubscribe
    {
        $this->dateCheck = $dateCheck;
        return $this;
    }

    /**
     * @param int $id
     * @return OrderSubscribe
     */
    public function setId(int $id): OrderSubscribe
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function getItems(): array
    {
        if(null === $this->items){
            $this->fillItems();
        }
        return $this->items;
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function fillItems()
    {
        /** @var OrderSubscribeService $orderSubscribeService */
        $orderSubscribeService = Application::getInstance()->getContainer()->get('order_subscribe.service');
        $items = $orderSubscribeService->getItemsBySubscribeId($this->getId());
        foreach($items as $item){
            $this->items[] = new OrderSubscribeItem($item);
        }
    }


}