<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 20.06.2019
 * Time: 19:15
 */

namespace FourPaws\MobileApiBundle\Dto\Object\OrderSubscribe;

use FourPaws\App\Application;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use FourPaws\MobileApiBundle\Services\Api\ProductService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\PersonalBundle\Entity\OrderSubscribeItem as PersonalOrderSubscribeItem;
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
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("active")
     */
    protected $active = 1;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("orderId")
     */
    protected $orderId;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("nextDate")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $nextDate;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("dateCreate")
     * @Serializer\Groups(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateCreate;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("dateUpdate")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateUpdate;

    /**
     * @var string
     * @Serializer\Type("string")
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
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("dateCheck")
     * @Serializer\Groups(groups={"create","read","update"})
     */
    protected $dateCheck;

    /**
     * @var ShortProduct[]
     * @Serializer\Type("array")
     * @Serializer\SerializedName("goods")
     * @Serializer\Groups(groups={"create","read","update"})
     */
    protected $goods;

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
        $this->fillGoods();
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
     * @return int
     */
    public function getActive(): int
    {
        return $this->active;
    }

    /**
     * @param int $active
     * @return OrderSubscribe
     */
    public function setActive(int $active): OrderSubscribe
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
     * @return string
     */
    public function getNextDate(): string
    {
        return $this->nextDate;
    }

    /**
     * @param string $nextDate
     * @return OrderSubscribe
     */
    public function setNextDate(string $nextDate): OrderSubscribe
    {
        $this->nextDate = $nextDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getDateCreate(): string
    {
        return $this->dateCreate;
    }

    /**
     * @param string $dateCreate
     * @return OrderSubscribe
     */
    public function setDateCreate(string $dateCreate): OrderSubscribe
    {
        $this->dateCreate = $dateCreate;
        return $this;
    }

    /**
     * @return string
     */
    public function getDateUpdate(): string
    {
        return $this->dateUpdate;
    }

    /**
     * @param string $dateUpdate
     * @return OrderSubscribe
     */
    public function setDateUpdate(string $dateUpdate): OrderSubscribe
    {
        $this->dateUpdate = $dateUpdate;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastCheck(): string
    {
        return $this->lastCheck;
    }

    /**
     * @param string $lastCheck
     * @return OrderSubscribe
     */
    public function setLastCheck(string $lastCheck): OrderSubscribe
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
     * @return string
     */
    public function getDateCheck(): string
    {
        return $this->dateCheck;
    }

    /**
     * @param string $dateCheck
     * @return OrderSubscribe
     */
    public function setDateCheck(string $dateCheck): OrderSubscribe
    {
        $this->dateCheck = $dateCheck;
        return $this;
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function getGoods(): array
    {
        if(null === $this->goods){
            $this->fillGoods();
        }
        return $this->goods;
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function fillGoods()
    {
        /** @var OrderSubscribeService $orderSubscribeService */
        $orderSubscribeService = Application::getInstance()->getContainer()->get('order_subscribe.service');

        $items = $orderSubscribeService->getItemsBySubscribeId($this->getId());

        /** @var PersonalOrderSubscribeItem $item */
        foreach($items as $item){
            $orderSubscribeItem = new OrderSubscribeItem($item);
            $this->goods[] = $orderSubscribeItem;
        }
    }
}