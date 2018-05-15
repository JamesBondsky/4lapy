<?php

namespace FourPaws\PersonalBundle\Entity;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\Internals\StatusLangTable;
use Bitrix\Sale\Internals\StatusTable;
use Bitrix\Sale\Payment;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\Helpers\DateHelper;
use FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException;
use FourPaws\PersonalBundle\Service\OrderService;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\OrderService as SaleOrderService;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreBundleNotFoundException;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class Order extends BaseEntity
{
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ACCOUNT_NUMBER")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $accountNumber = '';

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("PAY_SYSTEM_ID")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $paySystemId = 0;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("DELIVERY_ID")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $deliveryId = 0;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PERSON_TYPE_ID")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $personTypeID = 0;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("USER_ID")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $userId = 0;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("PAYED")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $payed = 'N';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("STATUS_ID")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $statusId = '';

    /**
     * @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("PRICE")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $price = 0;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("CURRENCY")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $currency = 'RUB';

    /**
     * @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("SUM_PAID")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $sumPaid = 0;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("DATE_INSERT")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $dateInsert;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("DATE_UPDATE")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $dateUpdate;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("DATE_PAYED")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $datePayed;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("DATE_STATUS")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $dateStatus;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("DATE_CANCELED")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $dateCanceled;

    /** @var ArrayCollection */
    protected $items;

    /** @var bool */
    protected $manzana = false;

    /** @var float */
    protected $allWeight ;

    /** @var Store */
    protected $store;

    /** @var float */
    protected $itemsSum;

    /** @var OrderPayment */
    protected $payment;

    /** @var OrderDelivery */
    protected $delivery;

    /** @var ArrayCollection */
    protected $props;

    /** @var array */
    protected $statusLang = [];

    /** @var array */
    protected $statusMain = [];

    /** @var string */
    protected $manzanaId = [];

    /** @var array $orderItems */
    protected $orderItems = [];

    /** @var \Bitrix\Sale\Order $bitrixOrder */
    protected $bitrixOrder;

    /** @var bool */
    protected $newManzana = false;

    /** @var string */
    protected $deliveryAddress;

    /** @var float|null */
    protected $bonusPay;

    /** @var array|null */
    protected $allPayments;

    /**
     * @return string
     */
    public function getAccountNumber(): string
    {
        return $this->accountNumber ?? '';
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
        return $this->paySystemId ?? 0;
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
        return $this->deliveryId ?? 0;
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
        return $this->personTypeID ?? '';
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
        return $this->userId ?? 0;
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
        return $this->payed ?? false;
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
        return $this->statusId ?? '';
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

    /**
     * @return string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getStatus(): string
    {
        if (empty($this->getStatusLang())) {
            $res = StatusLangTable::query()
                ->where('STATUS_ID', $this->getStatusId())
                ->where('LID', 'ru')
                ->setSelect(['NAME'])
                ->setLimit(1)
                ->setCacheTtl(360000)
                ->exec();
            if ($res->getSelectedRowsCount() > 0) {
                $this->setStatusLang($res->fetch());
            }
        }

        return $this->getStatusLang()['NAME'] ?? '';
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
        return $this->price ?? 0;
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
        return $this->currency ?? 'RUB';
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
        return $this->sumPaid ?? 0;
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
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     * @throws SystemException
     * @throws ArgumentException
     * @throws IblockNotFoundException
     * @throws ServiceNotFoundException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     * @throws \Exception
     */
    public function getItems(): ArrayCollection
    {
        if (!$this->items && $this->getId()) {
            $orderItems = $this->getOrderItems();
            $this->items = $orderItems[0];
        }

        return $this->items ?? new ArrayCollection();
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
     * @return array
     * @throws \Exception
     */
    public function getItemIdsQuantity(): array
    {
        $items = $this->getItems();
        $itemIds = [];
        /** @var OrderItem $item */
        foreach ($items as $item) {
            $itemIds[] = ['ID' => $item->getId(), 'QUANTITY' => $item->getQuantity()];
        }
        return $itemIds;
    }

    /**
     * @return bool
     */
    public function isManzana(): bool
    {
        return $this->manzana ?? false;
    }

    /**
     * @param bool $manzana
     */
    public function setManzana(bool $manzana): void
    {
        $this->manzana = $manzana;
    }

    /**
     * @return string
     */
    public function getPayPrefixText(): string
    {
        return $this->isPayed() ? 'Оплачено' : 'Итого к оплате';
    }

    /**
     * @return string
     */
    public function getFormatedDateInsert(): string
    {
        return DateHelper::replaceRuMonth($this->getDateInsert()->format('j #n# Y'), DateHelper::GENITIVE, true);
    }

    /**
     * @return string
     */
    public function getFormatedDateStatus(): string
    {
        return DateHelper::replaceRuMonth($this->getDateStatus()->format('j #n# Y'), DateHelper::GENITIVE, true);
    }

    /**
     * @return string
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws NotFoundException
     * @throws ApplicationCreateException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws ObjectNotFoundException
     */
    public function getFormatedPrice(): string
    {
        return number_format(round($this->getPrice() - $this->getBonusPay(), 2), 2, '.', ' ');
    }

    /**
     * @return float
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function getAllWeight(): float
    {
        if ($this->allWeight === null && $this->getId()) {
            $orderItems = $this->getOrderItems();
            $this->allWeight = (float)$orderItems[1];
        }

        return $this->allWeight ?? 0;
    }

    /**
     * @param float $allWeight
     *
     * @return Order
     */
    public function setAllWeight(float $allWeight): Order
    {
        $this->allWeight = $allWeight;

        return $this;
    }

    /**
     * @return float
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function getFormatedAllWeight(): float
    {
        $allWeight = $this->getAllWeight();

        return $allWeight > 0 ? number_format(round($allWeight / 1000, 2), 2, '.', ' ') : 0;
    }

    /**
     * @return float
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function getItemsSum(): float
    {
        if ($this->itemsSum === null && $this->getId()) {
            $orderItems = $this->getOrderItems();
            $this->itemsSum = (float)$orderItems[2];
        }

        return $this->itemsSum ?? 0;
    }

    /**
     * @param float $itemsSum
     */
    public function setItemsSum(float $itemsSum): void
    {
        $this->itemsSum = $itemsSum;
    }

    /**
     * @return string
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws EmptyEntityClass
     * @throws IblockNotFoundException
     * @throws SystemException
     * @throws \Exception
     */
    public function getFormattedItemsSum(): string
    {
        return number_format(round($this->getItemsSum(), 2), 2, '.', ' ');
    }

    /**
     * @return OrderPayment
     * @throws NotFoundException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws EmptyEntityClass
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws ObjectNotFoundException
     */
    public function getPayment(): OrderPayment
    {
        if (!$this->payment) {
            $paymentId = $this->getPaymentIdByPayments();
            if ($paymentId === null) {
                $paymentId = $this->getPaySystemId();
            }
            /** @todo сделать конвертер или использовать сток */
            $this->payment = $this->getPersonalOrderService()->getPayment($paymentId);
        }

        return $this->payment;
    }

    /**
     * @param OrderPayment $payment
     */
    public function setPayment(OrderPayment $payment): void
    {
        $this->payment = $payment;
    }

    /**
     * @return int|null
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws NotFoundException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws ObjectNotFoundException
     */
    public function getPaymentIdByPayments(): ?int
    {
        if ($this->getId() > 0) {
            /** @var Payment $payment */
            foreach ($this->getAllPayments() as $payment) {
                if ($payment->isInner()) {
                    continue;
                }
                return (int)$payment->getPaymentSystemId();
            }
        }
        return null;
    }

    /**
     * @return array
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws NotFoundException
     * @throws ApplicationCreateException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     */
    public function getAllPayments(): array
    {
        if ($this->allPayments === null) {
            $this->setAllPayments();
        }
        return $this->allPayments;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws NotFoundException
     * @throws ApplicationCreateException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     */
    public function setAllPayments(): void
    {
        $this->allPayments = [];
        if ($this->getId() > 0) {
            $bitrixOrder = $this->getOrderService()->getOrderById($this->getId());
            /** @var Payment $payment */
            foreach ($bitrixOrder->getPaymentCollection()->getIterator() as $payment) {
                $this->allPayments[] = $payment;
            }
        }
    }

    /**
     * @return OrderDelivery
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     */
    public function getDelivery(): OrderDelivery
    {
        if (!$this->delivery) {
            $this->delivery = $this->getPersonalOrderService()->getDelivery($this->getId());
        }

        return $this->delivery;
    }

    /**
     * @param OrderDelivery $delivery
     */
    public function setDelivery(OrderDelivery $delivery): void
    {
        $this->delivery = $delivery;
    }

    /**
     * @return string
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     * @throws ObjectException
     */
    public function getDateDelivery(): string
    {
        $formattedDate = '';
        if ($this->getDelivery()->isDeducted()) {
            $formattedDate = $this->getDelivery()->getFormatedDateDeducted();
        } else {
            /** @todo рассчитанная дата доставки */
            $propVal = $this->getPropValue('DELIVERY_DATE');
            if ($propVal) {
                /** @var Date|null $date */
                $date = new Date($propVal);
                if ($date instanceof Date) {
                    $formattedDate = DateHelper::replaceRuMonth($date->format('j #n# Y'), DateHelper::GENITIVE, true);
                }
            }
        }

        return $formattedDate;
    }

    /**
     * @return ArrayCollection
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     */
    public function getProps(): ArrayCollection
    {
        if (!$this->props && $this->getId()) {
            $this->props = $this->getPersonalOrderService()->getOrderProps($this->getId());
        }

        return $this->props ?? new ArrayCollection();
    }

    /**
     * @param ArrayCollection $props
     */
    public function setProps(ArrayCollection $props): void
    {
        $this->props = $props;
    }

    /**
     * @return Store|null
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws EmptyEntityClass
     * @throws ApplicationCreateException
     * @throws \Exception
     * @throws StoreBundleNotFoundException
     */
    public function getStore(): ?Store
    {
        if (!$this->store && $this->getId()) {
            $this->store = $this->getPersonalOrderService()->getStore($this);
        }

        return $this->store;
    }

    /**
     * @param Store $store
     */
    public function setStore(Store $store): void
    {
        $this->store = $store;
    }

    /**
     * @return array
     */
    public function getStatusLang(): array
    {
        return $this->statusLang ?? [];
    }

    /**
     * @param array $statusLang
     */
    public function setStatusLang(array $statusLang): void
    {
        $this->statusLang = $statusLang;
    }

    /**
     * @return array
     */
    public function getStatusMain(): array
    {
        return $this->statusMain ?? [];
    }

    /**
     * @param array $statusMain
     */
    public function setStatusMain(array $statusMain): void
    {
        $this->statusMain = $statusMain;
    }

    /**
     * @return mixed
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getStatusSort()
    {
        if (empty($this->getStatusMain())) {
            $this->setStatusMain(StatusTable::query()
                ->where('ID', $this->getStatusId())
                ->setCacheTtl(360000)
                ->setSelect(['SORT'])
                ->exec()
                ->fetch());
        }

        return $this->getStatusMain()['SORT'];
    }

    /**
     * @return bool
     */
    public function isClosed(): bool
    {
        return \in_array($this->getStatusId(), OrderService::$finalStatuses, true);
    }

    /**
     * @return string
     */
    public function getManzanaId(): string
    {
        return $this->manzanaId;
    }

    /**
     * @param string $manzanaId
     */
    public function setManzanaId(string $manzanaId): void
    {
        $this->manzanaId = $manzanaId;
    }

    /**
     * @param string $propCode
     *
     * @return mixed
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     */
    public function getPropValue(string $propCode)
    {
        $orderProp = $this->getProps()->get($propCode);

        return $orderProp ? $orderProp->getValue() : '';
    }

    /**
     * @return \Bitrix\Sale\Order
     * @throws BitrixOrderNotFoundException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     */
    public function getBitrixOrder(): \Bitrix\Sale\Order
    {
        if (!isset($this->bitrixOrder)) {
            $this->bitrixOrder = \Bitrix\Sale\Order::load($this->getId());
        }
        if (!$this->bitrixOrder) {
            throw new BitrixOrderNotFoundException('Заказ не найден');
        }

        return $this->bitrixOrder;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isItemsEmpty(): bool
    {
        return $this->getItems()->isEmpty();
    }

    /**
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     */
    public function isFastOrder(): bool
    {
        return $this->getPropValue('COM_WAY') === '04';
    }

    /**
     * @param string $code
     *
     * @return OrderProp|null
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     */
    public function getProperty(string $code): ?OrderProp
    {
        return $this->getProps()->get($code);
    }

    /**
     * @return bool
     */
    public function isNewManzana(): bool
    {
        return $this->newManzana;
    }

    /**
     * @param bool $newManzana
     */
    public function setNewManzana(bool $newManzana): void
    {
        $this->newManzana = $newManzana;
    }

    /**
     * @return float
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws NotFoundException
     * @throws ApplicationCreateException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws ObjectNotFoundException
     */
    public function getBonusPay(): float
    {
        if ($this->bonusPay === null) {
            $bonusPay = (float)0;
            /** @var Payment $payment */
            foreach ($this->getAllPayments() as $payment) {
                if ($payment->isInner()) {
                    $bonusPay = $payment->getSum();
                    break;
                }
            }
            $this->setBonusPay($bonusPay);
        }
        return $this->bonusPay ?? (float)0;
    }

    /**
     * @param float $bonusPay
     */
    public function setBonusPay(float $bonusPay): void
    {
        $this->bonusPay = $bonusPay;
    }

    /**
     * @return string
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws NotFoundException
     * @throws ApplicationCreateException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws ObjectNotFoundException
     */
    public function getBonusPayFormatted(): string
    {
        return $this->getBonusPay() > 0 ? number_format(round($this->getBonusPay(), 2), 2, '.', ' ') : '';
    }

    /**
     * @return string
     */
    public function getDeliveryAddress(): string
    {
        if (!isset($this->deliveryAddress)) {
            try {
                $this->deliveryAddress = $this->getOrderService()->getOrderDeliveryAddress(
                    $this->getBitrixOrder()
                );
            } catch (\Exception $exception) {
                $this->deliveryAddress = '';
            }
        }

        return $this->deliveryAddress;
    }

    /**
     * @return array
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    protected function getOrderItems(): array
    {
        if (!$this->orderItems) {
            $this->orderItems = $this->getPersonalOrderService()->getOrderItems(
                $this->getId()
            );
        }

        return $this->orderItems;
    }

    /**
     * @return OrderService
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    private function getPersonalOrderService(): OrderService
    {
        $appCont = Application::getInstance()->getContainer();
        return $appCont->get('order.service');
    }

    /**
     * @return SaleOrderService
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    private function getOrderService(): SaleOrderService
    {
        $appCont = Application::getInstance()->getContainer();
        return $appCont->get(SaleOrderService::class);
    }
}
