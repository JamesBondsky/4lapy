<?php

namespace FourPaws\PersonalBundle\Entity;

use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Exception\RuntimeException;
use FourPaws\PersonalBundle\Service\OrderSubscribeHistoryService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;

class OrderSubscribeCopyParams
{
    /** @var OrderSubscribe $orderSubscribe */
    protected $orderSubscribe;
    /** @var \DateTime $deliveryDate */
    private $deliveryDate;
    /** @var \DateTime $currentDate */
    private $currentDate;
    /** @var \DateTime $dateForOrderCreate */
    private $dateForOrderCreate;
    /** @var int $copyOrderId */
    private $copyOrderId;
    /** @var OrderSubscribeService $orderSubscribeService */
    private $orderSubscribeService;
    /** @var BaseResult $orderSubscribeService */
    private $copyOrderDeliveryCalculationResult;

    /**
     * CopyOrderParams constructor.
     *
     * @param OrderSubscribe $orderSubscribe
     */
    public function __construct(OrderSubscribe $orderSubscribe)
    {
        $this->orderSubscribe = $orderSubscribe;
    }

    /**
     * @return OrderSubscribeService|object
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function getOrderSubscribeService()
    {
        if (!$this->orderSubscribeService) {
            $this->orderSubscribeService = Application::getInstance()->getContainer()->get(
                'order_subscribe.service'
            );
        }

        return $this->orderSubscribeService;
    }

    /**
     * @return OrderSubscribe
     */
    public function getOrderSubscribe()
    {
        return $this->orderSubscribe;
    }

    /**
     * @param string|\DateTime $currentDate
     * @return OrderSubscribeCopyParams
     * @throws InvalidArgumentException
     */
    public function setCurrentDate($currentDate): self
    {
        // принудительное приведение к требуемому формату - время нам здесь не нужно
        $currentDate = $currentDate ?: '';
        if (is_string($currentDate)) {
            $dateValue = (new \DateTime($currentDate))->format('d.m.Y');
        } elseif ($currentDate instanceof \DateTime) {
            $dateValue = $currentDate->format('d.m.Y');
        } else {
            throw new InvalidArgumentException('Дата задана некорректно');
        }
        $this->currentDate = new \DateTime($dateValue);
        // дата доставки зависит от текущей даты, обнуляем ее
        $this->deliveryDate = null;

        return $this;
    }

    /**
     * Возвращает текущую дату
     *
     * @return \DateTime
     */
    public function getCurrentDate(): \DateTime
    {
        if (!$this->currentDate) {
            $this->currentDate = new \DateTime();
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
     * @param string|\DateTime $deliveryDate
     * @return OrderSubscribeCopyParams
     * @throws InvalidArgumentException
     */
    public function setDeliveryDate($deliveryDate): self
    {
        // принудительное приведение к требуемому формату - время нам здесь не нужно
        $deliveryDate = $deliveryDate ?: '';
        if (is_string($deliveryDate)) {
            $dateValue = (new \DateTime($deliveryDate))->format('d.m.Y');
        } elseif ($deliveryDate instanceof \DateTime) {
            $dateValue = $deliveryDate->format('d.m.Y');
        } else {
            throw new InvalidArgumentException('Дата задана некорректно');
        }
        $this->deliveryDate = new \DateTime($dateValue);

        return $this;
    }

    /**
     * Следующая дата, на которую необходимо доставить заказ по подписке
     *
     * @return \DateTime
     * @throws \Exception
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\PersonalBundle\Exception\RuntimeException
     */
    public function getDeliveryDate(): \DateTime
    {
        if (!$this->deliveryDate) {
            $this->deliveryDate = $this->getOrderSubscribe()->getNextDeliveryDate(
                $this->getCurrentDate()
            );
        }

        return $this->deliveryDate;
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
            /** @var OrderSubscribeHistoryService $orderSubscribeHistoryService */
            $orderSubscribeHistoryService = Application::getInstance()->getContainer()->get(
                'order_subscribe_history.service'
            );
            // Система должна создавать заказ по подписке автоматически с учетом условий подписки на
            // доставку путем копирования заказа:
            // − Исходного заказа, если создается первый заказ по подписке;
            // − Предыдущего заказа по подписке, если создается не первый заказ по подписке.
            $originOrderId = $this->getOriginOrderId();
            $this->copyOrderId = $orderSubscribeHistoryService->getLastCopyOrderId($originOrderId);
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
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getCopyOrderDeliveryCalculationResult(): BaseResult
    {
        if (!$this->copyOrderDeliveryCalculationResult) {
            $copyOrderId = $this->getCopyOrderId();
            if ($copyOrderId == $this->getOriginOrderId()) {
                $personalOrder = $this->getOrderSubscribe()->getOrder();
                $bitrixOrder = $personalOrder ? $personalOrder->getBitrixOrder() : null;
            } else {
                $bitrixOrder = \Bitrix\Sale\Order::load($copyOrderId);
            }
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
     * Определение даты, когда должен быть создан заказ, чтобы его доставили к заданному сроку.
     * Расчетная дата может быть меньше текущей
     *
     * @return \DateTime
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
    public function getDateForOrderCreate(): \DateTime
    {
        if (!$this->dateForOrderCreate) {
            $calculationResult = $this->getCopyOrderDeliveryCalculationResult();
            $orderSubscribeService = $this->getOrderSubscribeService();
            $this->dateForOrderCreate = $orderSubscribeService->getDateForOrderCreate(
                $calculationResult,
                $this->getDeliveryDate(),
                $this->getCurrentDate()
            );
        }

        return $this->dateForOrderCreate;
    }
}
