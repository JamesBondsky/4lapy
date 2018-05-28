<?php

namespace FourPaws\PersonalBundle\Entity;

use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Exception\RuntimeException;
use FourPaws\PersonalBundle\Service\OrderSubscribeHistoryService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\SaleBundle\Helper\OrderCopy;

class OrderSubscribeCopyParams
{
    /** @var OrderSubscribe $orderSubscribe */
    protected $orderSubscribe;
    /** @var \DateTimeImmutable $deliveryDate */
    private $deliveryDate;
    /** @var \DateTimeImmutable $realDeliveryDate */
    private $realDeliveryDate;
    /** @var \DateTimeImmutable $currentDate */
    private $currentDate;
    /** @var \DateTimeImmutable $dateForOrderCreate */
    private $dateForOrderCreate;
    /** @var int $copyOrderId */
    private $copyOrderId;
    /** @var BaseResult $copyOrderDeliveryCalculationResult */
    private $copyOrderDeliveryCalculationResult;
    /** @var BaseResult $newOrderDeliveryCalculationResult */
    private $newOrderDeliveryCalculationResult;
    /** @var OrderCopy $orderCopyHelper */
    private $orderCopyHelper;
    /** @var array $copyOrderParams */
    private $copyOrderParams;

    /**
     * CopyOrderParams constructor.
     *
     * @param OrderSubscribe $orderSubscribe
     * @param array $copyOrderParams
     */
    public function __construct(OrderSubscribe $orderSubscribe, array $copyOrderParams = [])
    {
        $this->orderSubscribe = $orderSubscribe;
        $this->copyOrderParams = $copyOrderParams;
    }

    /**
     * @return OrderSubscribeService
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getOrderSubscribeService(): OrderSubscribeService
    {
        /** @var OrderSubscribeService $orderSubscribeService */
        $orderSubscribeService = Application::getInstance()->getContainer()->get(
            'order_subscribe.service'
        );

        return $orderSubscribeService;
    }

    /**
     * @return OrderSubscribeHistoryService
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getOrderSubscribeHistoryService(): OrderSubscribeHistoryService
    {
        /** @var OrderSubscribeHistoryService $orderSubscribeHistoryService */
        $orderSubscribeHistoryService = Application::getInstance()->getContainer()->get(
            'order_subscribe_history.service'
        );

        return $orderSubscribeHistoryService;
    }

    /**
     * @return OrderSubscribe
     */
    public function getOrderSubscribe(): OrderSubscribe
    {
        return $this->orderSubscribe;
    }

    /**
     * @return OrderCopy
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getOrderCopyHelper(): OrderCopy
    {
        if (!$this->orderCopyHelper) {
            $this->orderCopyHelper = new OrderCopy(
                $this->getCopyOrderId()
            );

            // принудительно отключаем применение скидок - на данный момент они работают только в контексте текущего юзера
            $this->orderCopyHelper->setDisabledExtendedDiscounts(true);

            if (isset($this->copyOrderParams['orderExcludeProps'])) {
                $this->orderCopyHelper->appendOrderExcludeProps(
                    $this->copyOrderParams['orderExcludeProps']
                );
            }
            if (isset($this->copyOrderParams['basketItemExcludeProps'])) {
                $this->orderCopyHelper->appendBasketItemExcludeProps(
                    $this->copyOrderParams['basketItemExcludeProps']
                );
            }
            if (isset($this->copyOrderParams['orderCopyFields'])) {
                $this->orderCopyHelper->appendOrderCopyFields(
                    $this->copyOrderParams['orderCopyFields']
                );
            }
        }

        return $this->orderCopyHelper;
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\SaleBundle\Exception\OrderCopyBasketException
     * @throws \FourPaws\SaleBundle\Exception\OrderCopyShipmentsException
     */
    public function doCopyOrder()
    {
        if (!$this->getOrderCopyHelper()->isBasketCopied()) {
            $this->getOrderCopyHelper()->doBasicCopy();
        }
    }

    /**
     * @return \Bitrix\Sale\Order
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\SaleBundle\Exception\OrderCopyBasketException
     * @throws \FourPaws\SaleBundle\Exception\OrderCopyShipmentsException
     */
    public function getNewOrder(): \Bitrix\Sale\Order
    {
        $this->doCopyOrder();

        return $this->getOrderCopyHelper()->getNewOrder();
    }

    /**
     * @return \Bitrix\Sale\Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\SaleBundle\Exception\OrderCopyBasketException
     * @throws \FourPaws\SaleBundle\Exception\OrderCopyShipmentsException
     * @throws \FourPaws\SaleBundle\Exception\OrderCreateException
     */
    public function saveNewOrder(): \Bitrix\Sale\Result
    {
        $this->doCopyOrder();
        $this->getOrderCopyHelper()->doFinalAction();

        $this->realDeliveryDate = null;

        return $this->getOrderCopyHelper()->save();
    }

    /**
     * Устанавливает текущую дату.
     * Текущее время может повлиять на определение кол-ва дней доставки, поэтому его игнорировать нельзя
     *
     * @param string|\DateTimeInterface $currentDate
     * @return OrderSubscribeCopyParams
     * @throws InvalidArgumentException
     */
    public function setCurrentDate($currentDate): self
    {
        $currentDate = $currentDate ?: '';
        if (is_string($currentDate)) {
            $this->currentDate = new \DateTimeImmutable($currentDate);
        } elseif ($currentDate instanceof \DateTimeInterface) {
            $this->currentDate = new \DateTimeImmutable(
                $currentDate->format('d.m.Y H:i:s')
            );
        } else {
            throw new InvalidArgumentException('Дата задана некорректно');
        }

        // дата доставки зависит от текущей даты, обнуляем ее
        $this->deliveryDate = null;
        $this->dateForOrderCreate = null;
        $this->realDeliveryDate = null;

        return $this;
    }

    /**
     * Возвращает текущую дату.
     *
     * @return \DateTimeImmutable
     */
    public function getCurrentDate(): \DateTimeImmutable
    {
        if (!$this->currentDate) {
            $this->currentDate = new \DateTimeImmutable();
        }

        return $this->currentDate;
    }

    /**
     * @return string
     */
    public function getCurrentDateFormatted(): string
    {
        return $this->getCurrentDate()->format('d.m.Y');
    }

    /**
     * Устанавливает очередную дату, на которую необходимо доставить заказ по подписке.
     *
     * @param string|\DateTimeInterface $deliveryDate
     * @return OrderSubscribeCopyParams
     * @throws InvalidArgumentException
     */
    public function setDeliveryDate($deliveryDate): self
    {
        // Принудительное приведение к требуемому формату - время нам здесь не нужно
        // Принимаем, что ровно в 00:00:00 указанного дня заказ уже должен быть готов к выдаче клиенту.
        $deliveryDate = $deliveryDate ?: '';
        if (is_string($deliveryDate)) {
            $dateValue = (new \DateTime($deliveryDate))->format('d.m.Y');
        } elseif ($deliveryDate instanceof \DateTimeInterface) {
            $dateValue = $deliveryDate->format('d.m.Y');
        } else {
            throw new InvalidArgumentException('Дата задана некорректно');
        }
        $this->deliveryDate = new \DateTimeImmutable($dateValue);

        // от даты доставки зависит дата создания заказа
        $this->dateForOrderCreate = null;
        $this->realDeliveryDate = null;

        return $this;
    }

    /**
     * Рассчитывает и возвращает очередную дату, на которую необходимо доставить заказ по подписке.
     * Если текущий день (getCurrentDate()) совпадает с расчетной очередной датой,
     * то вплоть до 23:59:59 он будет считаться очередной датой.
     *
     * @return \DateTimeImmutable
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\PersonalBundle\Exception\RuntimeException
     */
    public function getDeliveryDate(): \DateTimeImmutable
    {
        if (!$this->deliveryDate) {
            $this->setDeliveryDate(
                $this->getOrderSubscribe()->getNextDeliveryDate(
                    $this->getCurrentDate()
                )
            );
        }

        return $this->deliveryDate;
    }

    /**
     * Возвращает дату доставки заказа с учетом уже возможно созданного заказа для даты из getDeliveryDate()
     *
     * @param bool $refresh
     * @return \DateTimeImmutable
     * @throws RuntimeException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getRealDeliveryDate(bool $refresh = false): \DateTimeImmutable
    {
        if (!$this->realDeliveryDate || $refresh) {
            // Получим дату доставки из свойства уже созданного заказа (если таковой есть).
            // Например, операторы после созвона могли изменить дату.
            $orderSubscribeHistoryService = $this->getOrderSubscribeHistoryService();
            $orderId = $orderSubscribeHistoryService->getCreatedOrderId(
                $this->getOriginOrderId(),
                $this->getDeliveryDate()
            );
            if ($orderId > 0) {
                $order = \Bitrix\Sale\Order::load($orderId);
                if ($order) {
                    /** @var \FourPaws\SaleBundle\Service\OrderService $orderService */
                    $orderService = Application::getInstance()->getContainer()->get(
                        \FourPaws\SaleBundle\Service\OrderService::class
                    );
                    $value = $orderService->getOrderDeliveryDate($order);
                    if ($value) {
                        $this->realDeliveryDate = new \DateTimeImmutable($value->format('d.m.Y'));
                    }
                }
            }
            // если заказа еще нет или не удалось получить для него дату доставки, то вернем очередную
            if (!$this->realDeliveryDate) {
                $this->realDeliveryDate = $this->getDeliveryDate();
            }
        }

        return $this->realDeliveryDate;
    }

    /**
     * @return int
     */
    public function getOriginOrderId(): int
    {
        return $this->getOrderSubscribe()->getOrderId();
    }

    /**
     * Возвращает id заказа, копия которого будет создаваться по подписке
     *
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getCopyOrderId(): int
    {
        if (!$this->copyOrderId) {
            $orderSubscribeHistoryService = $this->getOrderSubscribeHistoryService();
            // Система должна создавать заказ по подписке автоматически с учетом условий подписки на
            // доставку путем копирования заказа:
            // − Исходного заказа, если создается первый заказ по подписке;
            // − Предыдущего заказа по подписке, если создается не первый заказ по подписке.
            $originOrderId = $this->getOriginOrderId();
            $this->copyOrderId = $orderSubscribeHistoryService->getLastCreatedOrderId($originOrderId);
            if ($this->copyOrderId <= 0) {
                $this->copyOrderId = $originOrderId;
            }
        }

        return $this->copyOrderId;
    }

    /**
     * Возвращает CalculationResult заказа, который будет копироваться по подписке
     * CalculationResult берется от клона копируемого заказа.
     *
     * @return BaseResult
     * @throws BitrixOrderNotFoundException
     * @throws RuntimeException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\PersonalBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getCopyOrderDeliveryCalculationResult(): BaseResult
    {
        if (!$this->copyOrderDeliveryCalculationResult) {
            $bitrixOrder = $this->getCopyOrder();
            if (!$bitrixOrder) {
                throw new BitrixOrderNotFoundException('Копируемый заказ не найден');
            }

            $orderSubscribeService = $this->getOrderSubscribeService();
            $calculationResult = $orderSubscribeService->getDeliveryCalculationResult($bitrixOrder);
            if (!$calculationResult || !$calculationResult->isSuccess()) {
                throw new RuntimeException('Не удалось получить расчет доставки копируемого заказа');
            }

            $this->copyOrderDeliveryCalculationResult = $calculationResult;
        }

        return $this->copyOrderDeliveryCalculationResult;
    }

    /**
     * Возвращает заказ, который будет копироваться
     *
     * @return \Bitrix\Sale\Order
     * @throws BitrixOrderNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\PersonalBundle\Exception\NotFoundException
     */
    public function getCopyOrder(): \Bitrix\Sale\Order
    {
        $bitrixOrder = null;
        $copyOrderId = $this->getCopyOrderId();
        if ($copyOrderId == $this->getOriginOrderId()) {
            $bitrixOrder = $this->getOrderSubscribe()->getOrder()->getBitrixOrder();
        } else {
            $bitrixOrder = \Bitrix\Sale\Order::load($copyOrderId);
        }

        return $bitrixOrder;
    }

    /**
     * Возвращает CalculationResult для нового заказа
     * CalculationResult берется от клона копируемого заказа.
     *
     * @return BaseResult
     * @throws RuntimeException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\SaleBundle\Exception\OrderCopyBasketException
     * @throws \FourPaws\SaleBundle\Exception\OrderCopyShipmentsException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getNewOrderDeliveryCalculationResult(): BaseResult
    {
        if (!$this->newOrderDeliveryCalculationResult) {
            $bitrixOrder = $this->getNewOrder();
            $orderSubscribeService = $this->getOrderSubscribeService();
            $calculationResult = $orderSubscribeService->getDeliveryCalculationResult($bitrixOrder);
            if (!$calculationResult || !$calculationResult->isSuccess()) {
                throw new RuntimeException('Не удалось получить расчет доставки нового заказа');
            }

            $this->newOrderDeliveryCalculationResult = $calculationResult;
        }

        return $this->newOrderDeliveryCalculationResult;
    }

    /**
     * Определение даты, когда должен быть создан заказ, чтобы его доставили к заданному сроку.
     * Дата определяется в контексте нового заказа.
     * Расчетная дата может быть меньше текущей.
     *
     * @return \DateTimeImmutable
     * @throws BitrixOrderNotFoundException
     * @throws RuntimeException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getDateForOrderCreate(): \DateTimeImmutable
    {
        if (!$this->dateForOrderCreate) {
            $calculationResult = $this->getNewOrderDeliveryCalculationResult();
            $orderSubscribeService = $this->getOrderSubscribeService();
            $dateForOrderCreate = $orderSubscribeService->getDateForOrderCreate(
                $calculationResult,
                $this->getDeliveryDate(),
                $this->getCurrentDate()
            );
            $this->dateForOrderCreate = new \DateTimeImmutable();
            $this->dateForOrderCreate->setTimestamp($dateForOrderCreate->getTimestamp());
        }

        return $this->dateForOrderCreate;
    }

    /**
     * @return bool
     * @throws RuntimeException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function isCurrentDeliveryDateOrderAlreadyCreated(): bool
    {
        $orderSubscribeHistoryService = $this->getOrderSubscribeHistoryService();
        $result = $orderSubscribeHistoryService->wasOrderCreated(
            $this->getOriginOrderId(),
            $this->getDeliveryDate()
        );

        return $result;
    }
}
