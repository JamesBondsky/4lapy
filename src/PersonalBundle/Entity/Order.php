<?php

namespace FourPaws\PersonalBundle\Entity;


use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\StatusLangTable;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\Helpers\DateHelper;
use FourPaws\StoreBundle\Entity\Store;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class Order extends BaseEntity
{
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ACCOUNT_NUMBER")
     * @Serializer\Groups(groups={"read","update", "create")
     */
    protected $accountNumber = '';

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("PAY_SYSTEM_ID")
     * @Serializer\Groups(groups={"read","update", "create")
     */
    protected $paySystemId = 0;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("DELIVERY_ID")
     * @Serializer\Groups(groups={"read","update", "create")
     */
    protected $deliveryId = 0;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PERSON_TYPE_ID")
     * @Serializer\Groups(groups={"read","update", "create")
     */
    protected $personTypeID = 0;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("USER_ID")
     * @Serializer\Groups(groups={"read","update", "create")
     */
    protected $userId = 0;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("PAYED")
     * @Serializer\Groups(groups={"read","update", "create")
     */
    protected $payed = 'N';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("STATUS_ID")
     * @Serializer\Groups(groups={"read","update", "create")
     */
    protected $statusId = '';

    /**
     * @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("PRICE")
     * @Serializer\Groups(groups={"read","update", "create")
     */
    protected $price = 0;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("CURRENCY")
     * @Serializer\Groups(groups={"read","update", "create")
     */
    protected $currency = 'RUB';

    /**
     * @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("SUM_PAID")
     * @Serializer\Groups(groups={"read","update", "create")
     */
    protected $sumPaid = 0;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("DATE_INSERT")
     * @Serializer\Groups(groups={"read","update", "create")
     */
    protected $dateInsert;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("DATE_UPDATE")
     * @Serializer\Groups(groups={"read","update", "create")
     */
    protected $dateUpdate;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("DATE_PAYED")
     * @Serializer\Groups(groups={"read","update", "create")
     */
    protected $datePayed;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("DATE_STATUS")
     * @Serializer\Groups(groups={"read","update", "create")
     */
    protected $dateStatus;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("DATE_CANCELED")
     * @Serializer\Groups(groups={"read","update", "create")
     */
    protected $dateCanceled;

    /** @var ArrayCollection */
    protected $items;

    /** @var bool */
    protected $manzana = false;

    /** @var int */
    protected $allWeight = 0;

    /** @var Store */
    protected $store;

    /** @var float */
    protected $itemsSum = 0;

    /** @var OrderPayment */
    protected $payment;

    /** @var OrderDelivery */
    protected $delivery;

    /** @var ArrayCollection */
    protected $props;

    /**
     * @return string
     */
    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    /**
     * @param string $accountNumber
     *
     * @return Order
     */
    public function setAccountNumber(string $accountNumber): Order
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    /**
     * @return int
     */
    public function getPaySystemId(): int
    {
        return $this->paySystemId;
    }

    /**
     * @param int $paySystemId
     *
     * @return Order
     */
    public function setPaySystemId(int $paySystemId): Order
    {
        $this->paySystemId = $paySystemId;
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
     *
     * @return Order
     */
    public function setDeliveryId(int $deliveryId): Order
    {
        $this->deliveryId = $deliveryId;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateInsert(): DateTime
    {
        return $this->dateInsert;
    }

    /**
     * @param DateTime $dateInsert
     *
     * @return Order
     */
    public function setDateInsert(DateTime $dateInsert): Order
    {
        $this->dateInsert = $dateInsert;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateUpdate(): DateTime
    {
        return $this->dateUpdate;
    }

    /**
     * @param DateTime $dateUpdate
     *
     * @return Order
     */
    public function setDateUpdate(DateTime $dateUpdate): Order
    {
        $this->dateUpdate = $dateUpdate;
        return $this;
    }

    /**
     * @return string
     */
    public function getPersonTypeID(): string
    {
        return $this->personTypeID;
    }

    /**
     * @param string $personTypeID
     *
     * @return Order
     */
    public function setPersonTypeID(string $personTypeID): Order
    {
        $this->personTypeID = $personTypeID;
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
     *
     * @return Order
     */
    public function setUserId(int $userId): Order
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPayed(): bool
    {
        return $this->payed;
    }

    /**
     * @param bool $payed
     *
     * @return Order
     */
    public function setPayed(bool $payed): Order
    {
        $this->payed = $payed;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDatePayed(): DateTime
    {
        return $this->datePayed;
    }

    /**
     * @param DateTime $datePayed
     *
     * @return Order
     */
    public function setDatePayed(DateTime $datePayed): Order
    {
        $this->datePayed = $datePayed;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatusId(): string
    {
        return $this->statusId;
    }

    /**
     * @param string $statusId
     *
     * @return Order
     */
    public function setStatusId(string $statusId): Order
    {
        $this->statusId = $statusId;
        return $this;
    }

    public function getStatus(): string
    {
        return StatusLangTable::query()
            ->where('STATUS_ID', $this->getStatusId())
            ->where('LID', 'ru')
            ->setCacheTtl(360000)
            ->exec()
            ->fetch()['NAME'];
    }

    /**
     * @return DateTime
     */
    public function getDateStatus(): DateTime
    {
        return $this->dateStatus;
    }

    /**
     * @param DateTime $dateStatus
     *
     * @return Order
     */
    public function setDateStatus(DateTime $dateStatus): Order
    {
        $this->dateStatus = $dateStatus;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return Order
     */
    public function setPrice(float $price): Order
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return Order
     */
    public function setCurrency(string $currency): Order
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return float
     */
    public function getSumPaid(): float
    {
        return $this->sumPaid;
    }

    /**
     * @param float $sumPaid
     *
     * @return Order
     */
    public function setSumPaid(float $sumPaid): Order
    {
        $this->sumPaid = $sumPaid;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCanceled(): DateTime
    {
        return $this->dateCanceled;
    }

    /**
     * @param DateTime $dateCanceled
     *
     * @return Order
     */
    public function setDateCanceled(DateTime $dateCanceled): Order
    {
        $this->dateCanceled = $dateCanceled;
        return $this;
    }

    /**
     * @return ArrayCollection
     * @throws ServiceNotFoundException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     * @throws SystemException
     * @throws ArgumentException
     * @throws IblockNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \Exception
     */
    public function getItems(): ArrayCollection
    {
        return $this->items;
    }

    /**
     * @param ArrayCollection $items
     *
     * @return Order
     */
    public function setItems(ArrayCollection $items): Order
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @return bool
     */
    public function isManzana(): bool
    {
        return $this->manzana;
    }

    /**
     * @param bool $manzana
     */
    public function setManzana(bool $manzana)
    {
        $this->manzana = $manzana;
    }

    public function getPayPrefixText(): string
    {
        return $this->isPayed() ? 'Оплачено' : 'Итого к оплате';
    }

    public function getFormatedDateInsert(): string
    {
        return DateHelper::replaceRuMonth($this->getDateInsert()->format('d #m# Y'));
    }

    public function getFormatedDateStatus(): string
    {
        return DateHelper::replaceRuMonth($this->getDateStatus()->format('d #m# Y'));
    }

    public function getFormatedPrice()
    {
        return number_format($this->getPrice(), 0, '.', ' ');
    }

    /**
     * @return int
     */
    public function getAllWeight(): int
    {
        return $this->allWeight;
    }

    /**
     * @param int $allWeight
     *
     * @return Order
     */
    public function setAllWeight(int $allWeight): Order
    {
        $this->allWeight = $allWeight;
        return $this;
    }

    /**
     * @return float
     */
    public function getFormatedAllWeight(): float
    {
        return $this->getAllWeight() > 0 ? round($this->getAllWeight() / 1000, 2) : 0;
    }

    /**
     * @return float
     */
    public function getItemsSum(): float
    {
        return $this->itemsSum;
    }

    /**
     * @param float $itemsSum
     */
    public function setItemsSum(float $itemsSum)
    {
        $this->itemsSum = $itemsSum;
    }

    /**
     * @return float
     */
    public function getFormatedItemsSum(): float
    {
        return number_format(round($this->getItemsSum(), 2), 2, '.', ' ');
    }

    /**
     * @return OrderPayment
     */
    public function getPayment(): OrderPayment
    {
        return $this->payment;
    }

    /**
     * @param OrderPayment $payment
     */
    public function setPayment(OrderPayment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return OrderDelivery
     */
    public function getDelivery(): OrderDelivery
    {
        return $this->delivery;
    }

    /**
     * @param OrderDelivery $delivery
     */
    public function setDelivery(OrderDelivery $delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * @return string
     */
    public function getDateDelivery(): string
    {
        $formatedDate = '';
        if ($this->getDelivery()->isDeducted()) {
            $formatedDate = $this->getDelivery()->getFormatedDateDeducted();
        } else {
            /** @todo рассчитанная дата доставки */
            /** @var OrderProp $prop */
            $prop = $this->getProps()->get('DELIVERY_DATE');
            /** @var Date|null $date */
            $date = $prop->getValue();
            if ($date !== null && !empty($date)) {
                $formatedDate = DateHelper::replaceRuMonth($date->format('d #m# Y'), DateHelper::GENITIVE);
            }
        }

        return $formatedDate;
    }

    /**
     * @return ArrayCollection
     */
    public function getProps(): ArrayCollection
    {
        return $this->props;
    }

    /**
     * @param ArrayCollection $props
     */
    public function setProps(ArrayCollection $props)
    {
        $this->props = $props;
    }

    /**
     * @return Store
     */
    public function getStore(): Store
    {
        return $this->store;
    }

    /**
     * @param Store $store
     */
    public function setStore(Store $store)
    {
        $this->store = $store;
    }
}