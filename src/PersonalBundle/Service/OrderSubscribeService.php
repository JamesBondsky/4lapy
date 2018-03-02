<?php

namespace FourPaws\PersonalBundle\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\DeleteResult;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\Error;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Shipment;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Exception\NotFoundException;
use FourPaws\PersonalBundle\Exception\RuntimeException;
use FourPaws\PersonalBundle\Repository\OrderSubscribeRepository;
use FourPaws\SaleBundle\Helper\OrderCopy;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class OrderSubscribeService
 *
 * @package FourPaws\PersonalBundle\Service
 */
class OrderSubscribeService
{
    /** @var OrderSubscribeRepository $orderSubscribeRepository */
    private $orderSubscribeRepository;
    /** @var CurrentUserProviderInterface $currentUser */
    private $currentUser;
    /** @var OrderService $orderService */
    private $orderService;
    /** @var DeliveryService $deliveryService */
    private $deliveryService;
    /** @var array $miscData */
    private $miscData = [];

    /** @var array Исключаемые свойства корзины */
    private $basketItemExcludeProps = [
        /** @todo: Уточнить какие свойства позиции корзины следует исключать */
    ];
    /** @var array Исключаемые свойства заказа */
    private $orderExcludeProps = [
        'IS_EXPORTED', 'DELIVERY_DATE', 'DELIVERY_INTERVAL',
        /** @todo: Уточнить насчет этих свойств */
        'FROM_APP',
    ];

    /**
     * OrderSubscribeService constructor.
     *
     * @param OrderSubscribeRepository $orderSubscribeRepository
     *
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(OrderSubscribeRepository $orderSubscribeRepository)
    {
        $this->orderSubscribeRepository = $orderSubscribeRepository;
    }

    /**
     * @return OrderService
     * @throws ApplicationCreateException
     */
    public function getOrderService(): OrderService
    {
        if (!isset($this->orderService)) {
            $this->orderService = Application::getInstance()->getContainer()->get('order.service');
        }

        return $this->orderService;
    }

    /**
     * @return CurrentUserProviderInterface
     * @throws ApplicationCreateException
     */
    public function getCurrentUserService(): CurrentUserProviderInterface
    {
        if (!isset($this->currentUser)) {
            $this->currentUser = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
        }

        return $this->currentUser;
    }

    /**
     * @return DeliveryService
     * @throws ApplicationCreateException
     */
    public function getDeliveryService(): DeliveryService
    {
        if (!isset($this->deliveryService)) {
            $this->deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        }

        return $this->deliveryService;
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function getFrequencyEnum(): array
    {
        if (!isset($this->miscData['FREQUENCY_ENUM'])) {
            $this->miscData['FREQUENCY_ENUM'] = [];
            $hlBlockEntityFields = $this->orderSubscribeRepository->getHlBlockEntityFields();
            if (isset($hlBlockEntityFields['UF_FREQUENCY'])) {
                if ($hlBlockEntityFields['UF_FREQUENCY']['USER_TYPE_ID'] === 'enumeration') {
                    // результат выборки кешируется внутри метода
                    $enumItems = (new \CUserFieldEnum())->GetList(
                        [
                            'SORT' => 'ASC'
                        ],
                        [
                            'USER_FIELD_ID' => $hlBlockEntityFields['UF_FREQUENCY']['ID']
                        ]
                    );
                    while ($item = $enumItems->Fetch()) {
                        $this->miscData['FREQUENCY_ENUM'][$item['ID']] = $item;
                    }
                }
            }
        }

        return $this->miscData['FREQUENCY_ENUM'];
    }

    /**
     * @param int $enumId
     * @return string
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function getFrequencyXmlId(int $enumId): string
    {
        $enum = $this->getFrequencyEnum();

        return isset($enum[$enumId]) ? $enum[$enumId]['XML_ID'] : '';
    }

    /**
     * @param int $enumId
     * @return string
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function getFrequencyValue(int $enumId): string
    {
        $enum = $this->getFrequencyEnum();

        return isset($enum[$enumId]) ? $enum[$enumId]['VALUE'] : '';
    }

    /**
     * @param int|array $orderId
     * @param bool $filterActive
     * @return ArrayCollection
     * @throws \Exception
     */
    public function getSubscriptionsByOrder($orderId, bool $filterActive = true)
    {
        $params = [];
        if ($filterActive) {
            $params['=UF_ACTIVE'] = 1;
        }

        return $this->orderSubscribeRepository->findByOrder($orderId, $params);
    }

    /**
     * @param int $orderId
     * @param bool $filterActive
     * @return OrderSubscribe|null
     * @throws \Exception
     */
    public function getSubscribeByOrderId(int $orderId, bool $filterActive = true)
    {
        $subscriptionsCollect = $this->getSubscriptionsByOrder($orderId, $filterActive);
        $orderSubscribe = $subscriptionsCollect->count() ? $subscriptionsCollect->first() : null;

        return $orderSubscribe;
    }

    /**
     * @param int|array $userId
     * @param bool $filterActive
     * @return ArrayCollection
     * @throws \Exception
     */
    public function getSubscriptionsByUser($userId, $filterActive = true)
    {
        $params = [];
        if ($filterActive) {
            $params['=UF_ACTIVE'] = 1;
        }

        return $this->orderSubscribeRepository->findByUser($userId, $params);
    }

    /**
     * @param int $orderId
     * @return Order|null
     * @throws \Exception
     */
    public function getOrderById(int $orderId)
    {
        return $this->getOrderService()->getOrderById($orderId);
    }

    /**
     * @param int $userId
     * @param bool $filterActive
     * @return ArrayCollection
     * @throws \Exception
     */
    public function getUserSubscribedOrders(int $userId, $filterActive = true): ArrayCollection
    {
        $params = [
            'filter' => [
                'USER_ID' => $userId,
                '!=ORDER_SUBSCRIBE.ID' => false,
            ],
            'runtime' => [
                new ReferenceField(
                    'ORDER_SUBSCRIBE',
                    $this->orderSubscribeRepository->getHlBlockEntityClass(),
                    [
                        '=this.ID' => 'ref.UF_ORDER_ID'
                    ]
                ),
            ]
        ];
        if ($filterActive) {
            $params['filter']['=ORDER_SUBSCRIBE.UF_ACTIVE'] = 1;
        }

        return $this->getOrderService()->getUserOrders($params);
    }

    /**
     * @return AddResult
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function add(): AddResult
    {
        $addResult = call_user_func_array([$this->orderSubscribeRepository, 'createEx'], func_get_args());

        return $addResult;
    }

    /**
     * @return UpdateResult
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function update(): UpdateResult
    {
        $updateResult = call_user_func_array([$this->orderSubscribeRepository, 'updateEx'], func_get_args());

        return $updateResult;
    }

    /**
     * @param int $id
     * @return DeleteResult
     */
    public function delete(int $id): DeleteResult
    {
        $deleteResult = $this->orderSubscribeRepository->deleteEx($id);

        return $deleteResult;
    }

    /**
     * @param $periodValue
     * @param string $periodType
     * @return int
     */
    protected function convertCalcResultPeriodValue($periodValue, string $periodType)
    {
        $result = 0;
        $periodValue = (float)$periodValue;
        switch ($periodType) {
            case CalculationResult::PERIOD_TYPE_MONTH:
                $result = $periodValue * 30;
                break;

            case CalculationResult::PERIOD_TYPE_DAY:
                $result = ceil($periodValue);
                break;

            case CalculationResult::PERIOD_TYPE_HOUR:
                $result = ceil($periodValue / 24);
                break;

            case CalculationResult::PERIOD_TYPE_MIN:
                $result = ceil($periodValue / 86400);
                break;
        }

        return (int)$result;
    }

    /**
     * Возвращает CalculationResult для уже созданного заказа
     *
     * @param \Bitrix\Sale\Order $bitrixOrder
     * @return CalculationResult|null
     * @throws ApplicationCreateException
     * @throws ArgumentOutOfRangeException
     * @throws NotSupportedException
     * @throws \Exception
     */
    public function getDeliveryCalculationResult(\Bitrix\Sale\Order $bitrixOrder)
    {
        $calculationResult = null;
        if ($bitrixOrder->getId() && !$bitrixOrder->isClone()) {
            // делаем клон заказа, чтобы методы расчета доставки не изменили оригинальный заказ
            $bitrixOrderCloned = $bitrixOrder->createClone();

            $deliveryService = $this->getDeliveryService();
            $shipmentCollect = $bitrixOrderCloned->getShipmentCollection();
            foreach ($shipmentCollect as $shipment) {
                /** @var Shipment $shipment */
                if ($shipment->isSystem()) {
                    continue;
                }

                /** @todo В идеале нужно получать результат непосредственно от обработчика службы доставки, а не через сервис */
                $calcResultList = $deliveryService->calculateDeliveries(
                    $shipment,
                    [
                        $shipment->getDelivery()->getCode(),
                    ]
                );
                if ($calcResultList) {
                    $calculationResult = reset($calcResultList);
                    break;
                }
            }
        } else {
            throw new InvalidArgumentException('Передан некорректный заказ', 100);
        }

        return $calculationResult;
    }

    /**
     * Возвращает дату, когда следует создать заказ,
     * чтобы его доставили к заданному сроку
     *
     * @param OrderSubscribe $orderSubscribe
     * @param \DateTime $deliveryDate
     * @return \DateTime
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws SystemException
     * @throws \Exception
     */
    public function getOrderNextCreateDate(OrderSubscribe $orderSubscribe, \DateTime $deliveryDate)
    {
        $order = $orderSubscribe->getOrder();
        $bitrixOrder = $order ? $order->getBitrixOrder() : null;
        if (!$bitrixOrder) {
            throw new NotFoundException('Заказ не найден', 100);
        }

        $calculationResult = $this->getDeliveryCalculationResult($bitrixOrder);
        if (!$calculationResult || !$calculationResult->isSuccess()) {
            throw new RuntimeException('Не удалось рассчитать дату доставки', 200);
        }

        $tmpVal = $this->convertCalcResultPeriodValue(
            $calculationResult->getPeriodFrom(),
            $calculationResult->getPeriodType()
        );
        $minDays = $tmpVal > 0 ? $tmpVal : 1;
        /*
        $tmpVal = $this->convertCalcResultPeriodValue(
            $calcResult->getPeriodTo(),
            $calcResult->getPeriodType()
        );
        $maxDays = $tmpVal > 0 ? $tmpVal : 1;
        */

        // для подстраховки прибавляем еще один день к минимальному сроку доставки
        $subDays = $minDays + 1;
        $resultDate = clone $deliveryDate;
        $resultDate->sub(new \DateInterval('P'.$subDays.'D'));

        return $resultDate;
    }

    /**
     * @param int $copyOrderId
     * @param bool $checkActiveSubscribe
     * @return Result
     * @throws NotFoundException
     * @throws \Exception
     */
    public function copyOrderById(int $copyOrderId, bool $checkActiveSubscribe = true)
    {
        $orderSubscribe = $this->getSubscribeByOrderId($copyOrderId, $checkActiveSubscribe);
        if (!$orderSubscribe) {
            throw new NotFoundException('Подписка на заказ не найдена', 100);
        }

        return $this->copyOrder($orderSubscribe);
    }

    /**
     * @param OrderSubscribe $orderSubscribe
     * @return Result
     */
    public function copyOrder(OrderSubscribe $orderSubscribe)
    {
        $result = new Result();

        $copyOrderId = $orderSubscribe->getOrderId();
        if ($result->isSuccess()) {
            try {
                $orderCopyHelper = new OrderCopy($copyOrderId);
                $orderCopyHelper->appendBasketItemExcludeProps(
                    $this->basketItemExcludeProps
                );
                $orderCopyHelper->appendOrderExcludeProps(
                    $this->orderExcludeProps
                );

                $orderCopyHelper->doFullCopy();
                $orderCopyHelper->setPropValueByCode(
                    'DELIVERY_INTERVAL',
                    $orderSubscribe->getDeliveryTime()
                );
                $deliveryDate = $orderSubscribe->getNextDeliveryDate();
                /** @todo: Если дата не определилась, то нужно ли создавать такой заказ? */
                $orderCopyHelper->setPropValueByCode(
                    'DELIVERY_DATE',
                    $deliveryDate ? $deliveryDate->format('d.m.Y') : ''
                );
                $orderCopyHelper->save();
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'orderCopyException')
                );
            }
        }

        return $result;
    }
}
