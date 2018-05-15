<?php

namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
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
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Shipment;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Collection\UserFieldEnumCollection;
use FourPaws\AppBundle\Service\UserFieldEnumService;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Entity\OrderSubscribeCopyParams;
use FourPaws\PersonalBundle\Entity\OrderSubscribeCopyResult;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Exception\NotFoundException;
use FourPaws\PersonalBundle\Repository\OrderSubscribeRepository;
use FourPaws\SaleBundle\Service\NotificationService;
use FourPaws\SaleBundle\Service\OrderPropertyService;
use FourPaws\SapBundle\Consumer\ConsumerRegistry;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;

/**
 * Class OrderSubscribeService
 *
 * @package FourPaws\PersonalBundle\Service
 */
class OrderSubscribeService
{
    use LazyLoggerAwareTrait;

    // За сколько дней до предстоящей доставки должно отправляться напоминание
    const UPCOMING_DAYS_DELIVERY_MESS = 3;

    /** @var OrderSubscribeRepository $orderSubscribeRepository */
    private $orderSubscribeRepository;
    /** @var CurrentUserProviderInterface $currentUser */
    private $currentUser;
    /** @var OrderService $personalOrderService */
    private $personalOrderService;
    /** @var \FourPaws\SaleBundle\Service\OrderService $saleOrderService */
    private $saleOrderService;
    /** @var DeliveryService $deliveryService */
    private $deliveryService;
    /** @var UserFieldEnumService $userFieldEnumService */
    private $userFieldEnumService;
    /** @var OrderSubscribeHistoryService $orderSubscribeHistoryService */
    private $orderSubscribeHistoryService;

    /** @var array $miscData */
    private $miscData = [];

    /** @var array Исключаемые свойства корзины */
    private $basketItemExcludeProps = [];
    /** @var array Исключаемые свойства заказа */
    private $orderExcludeProps = [
        // заказ выгружен в SAP, всегда N
        'IS_EXPORTED',
        // из приложения
        'FROM_APP',
        // ???
        //'SHIPMENT_PLACE_CODE',
        // сообщения по заказу отправлены
        'COMPLETE_MESSAGE_SENT',
        'BONUS_COUNT',
        // эти свойства задаются при копировании заказа
        'DELIVERY_DATE', 'DELIVERY_INTERVAL', 'COM_WAY',
        'IS_SUBSCRIBE', 'COPY_ORDER_ID',
    ];

    /**
     * OrderSubscribeService constructor.
     *
     * @param OrderSubscribeRepository $orderSubscribeRepository
     *
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
    public function getPersonalOrderService(): OrderService
    {
        if (!isset($this->orderService)) {
            $this->personalOrderService = Application::getInstance()->getContainer()->get(
                'order.service'
            );
        }

        return $this->personalOrderService;
    }

    /**
     * @return \FourPaws\SaleBundle\Service\OrderService
     * @throws ApplicationCreateException
     */
    public function getOrderService(): \FourPaws\SaleBundle\Service\OrderService
    {
        if (!isset($this->saleOrderService)) {
            $this->saleOrderService = Application::getInstance()->getContainer()->get(
                \FourPaws\SaleBundle\Service\OrderService::class
            );
        }

        return $this->saleOrderService;
    }

    /**
     * @return CurrentUserProviderInterface
     * @throws ApplicationCreateException
     */
    public function getCurrentUserService(): CurrentUserProviderInterface
    {
        if (!isset($this->currentUser)) {
            $this->currentUser = Application::getInstance()->getContainer()->get(
                CurrentUserProviderInterface::class
            );
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
            $this->deliveryService = Application::getInstance()->getContainer()->get(
                'delivery.service'
            );
        }

        return $this->deliveryService;
    }

    /**
     * @return UserFieldEnumService
     * @throws ApplicationCreateException
     */
    protected function getUserFieldEnumService(): UserFieldEnumService
    {
        if (!$this->userFieldEnumService) {
            $this->userFieldEnumService = Application::getInstance()->getContainer()->get(
                'userfield_enum.service'
            );
        }

        return $this->userFieldEnumService;
    }

    /**
     * @return OrderSubscribeHistoryService
     * @throws ApplicationCreateException
     */
    protected function getOrderSubscribeHistoryService(): OrderSubscribeHistoryService
    {
        if (!$this->orderSubscribeHistoryService) {
            $this->orderSubscribeHistoryService = Application::getInstance()->getContainer()->get(
                'order_subscribe_history.service'
            );
        }

        return $this->orderSubscribeHistoryService;
    }

    /**
     * Может ли быть заказ подписан
     *
     * @param Order $order
     * @return bool
     */
    public function canBeSubscribed(Order $order): bool
    {
        //$result = $order->isPayed() && (!$order->isManzana() || $order->isNewManzana());
        // LP12-26: Сделать подписку на доставку и повтор заказа возможными для любых заказов.
        $result = !$order->isManzana() || $order->isNewManzana();

        return $result;
    }

    /**
     * @return UserFieldEnumCollection
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    public function getFrequencyEnum(): UserFieldEnumCollection
    {
        if (!isset($this->miscData['FREQUENCY_ENUM'])) {
            $this->miscData['FREQUENCY_ENUM'] = new UserFieldEnumCollection();
            $hlBlockEntityFields = $this->orderSubscribeRepository->getHlBlockEntityFields();
            if (isset($hlBlockEntityFields['UF_FREQUENCY'])) {
                if ($hlBlockEntityFields['UF_FREQUENCY']['USER_TYPE_ID'] === 'enumeration') {
                    $this->miscData['FREQUENCY_ENUM'] = $this->getUserFieldEnumService()->getEnumValueCollection(
                        $hlBlockEntityFields['UF_FREQUENCY']['ID']
                    );
                }
            }
        }

        return $this->miscData['FREQUENCY_ENUM'];
    }

    /**
     * @param int|array $orderId
     * @param bool $filterActive
     * @return ArrayCollection
     * @throws \Exception
     */
    public function getSubscriptionsByOrder($orderId, bool $filterActive = true): ArrayCollection
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
     * @param int $subscribeId
     * @return OrderSubscribe|null
     */
    public function getSubscribeById(int $subscribeId): ?OrderSubscribe
    {
        $orderSubscribe = null;
        try {
            /** @var OrderSubscribe $orderSubscribe */
            $orderSubscribe = $this->orderSubscribeRepository->findById($subscribeId);
        } catch (\Exception $exception) {
            // не нашлась запись и ладно
        }

        return $orderSubscribe;
    }

    /**
     * @param int|array $userId
     * @param bool $filterActive
     * @return ArrayCollection
     * @throws \Exception
     */
    public function getSubscriptionsByUser($userId, $filterActive = true): ArrayCollection
    {
        $params = [];
        if ($filterActive) {
            $params['filter']['=UF_ACTIVE'] = 1;
        }

        return $this->orderSubscribeRepository->findByUser($userId, $params);
    }

    /**
     * @param int $orderId
     * @return Order|null
     * @throws ApplicationCreateException
     * @throws \Exception
     */
    public function getOrderById(int $orderId)
    {
        return $this->getPersonalOrderService()->getOrderById($orderId);
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

        return $this->getPersonalOrderService()->getUserOrders($params);
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
    protected function convertCalcResultPeriodValue($periodValue, string $periodType): int
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
     * Возвращает CalculationResult для уже созданного заказа.
     * CalculationResult берется от клона заказа.
     *
     * @param \Bitrix\Sale\Order $bitrixOrder
     * @return BaseResult|null
     * @throws ApplicationCreateException
     * @throws ArgumentOutOfRangeException
     * @throws NotSupportedException
     * @throws \Exception
     */
    public function getDeliveryCalculationResult(\Bitrix\Sale\Order $bitrixOrder)
    {
        $calculationResult = null;
        if (!$bitrixOrder->isClone()) {
            // !!! делаем клон заказа, чтобы методы расчета доставки не изменили оригинальный заказ !!!
            $bitrixOrderCloned = $bitrixOrder->createClone();

            $deliveryService = $this->getDeliveryService();
            $shipmentCollect = $bitrixOrderCloned->getShipmentCollection();
            foreach ($shipmentCollect as $shipment) {
                /** @var Shipment $shipment */
                if ($shipment->isSystem()) {
                    continue;
                }

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
     * Возвращает дату, когда заказ может быть доставлен
     *
     * @param BaseResult $calculationResult
     * @param \DateTimeInterface|null $currentDate
     * @return \DateTime
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws SystemException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getOrderDeliveryDate(BaseResult $calculationResult, \DateTimeInterface $currentDate = null): \DateTime
    {
        $tmpCalculationResult = clone $calculationResult;
        if ($currentDate) {
            $tmpCalculationResult->setCurrentDate(
                (new \DateTime($currentDate->format('d.m.Y H:i:s')))
            );
        }
        $tmpDeliveryDate = $tmpCalculationResult->getDeliveryDate();
        $deliveryDate = clone $tmpDeliveryDate;

        // добавляем 1 день для подстраховки
        $deliveryDate->add(new \DateInterval('P1D'));

        return $deliveryDate;
    }

    /**
     * Определение даты, когда должен быть создан заказ, чтобы его доставили к заданному сроку.
     * Расчетная дата может быть меньше текущей
     *
     * @param BaseResult $calculationResult
     * @param \DateTimeInterface $deliveryDate Дата, на которую должен быть готов заказ
     * @param \DateTimeInterface|null $currentDate Текущая дата
     * @return \DateTime
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws SystemException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getDateForOrderCreate(
        BaseResult $calculationResult,
        \DateTimeInterface $deliveryDate,
        \DateTimeInterface $currentDate = null
    ): \DateTime
    {
        $calculatedDeliveryDate = $this->getOrderDeliveryDate($calculationResult, $currentDate);
        // сколько дней займет доставка
        $currentDate = $currentDate ?? new \DateTimeImmutable();
        $diff = $currentDate->diff($calculatedDeliveryDate);
        $deliveryDays = (int)$diff->days;
        // принимаем, что доставить заказ нужно к 00:00:00 указанного дня,
        // поэтому, если на доставку нужно n дней и n часов, то увеличиваем время доставки еще на день
        if ($diff->h || $diff->i || $diff->s) {
            ++$deliveryDays;
        }

        // принудительно отбрасываем время
        $resultDate = new \DateTime($deliveryDate->format('d.m.Y'));
        $resultDate->sub(new \DateInterval('P'.$deliveryDays.'D'));

        return $resultDate;
    }

    /**
     * Возвращает кол-во дней, оставшихся до очередной доставки
     *
     * @param \DateTimeInterface $deliveryDate
     * @param \DateTimeInterface|null $currentDate
     * @return int
     */
    public function getDeliveryDateUpcomingDays(\DateTimeInterface $deliveryDate, \DateTimeInterface $currentDate = null): int
    {
        $currentDate = $currentDate ?? new \DateTimeImmutable();
        // Чтобы правильно определялось кол-во дней, у даты доставки нужно обнулять время
        $deliveryDate = new \DateTime($deliveryDate->format('d.m.Y'));
        $interval = $currentDate->diff($deliveryDate);

        return (int)$interval->format('%r%a');
    }

    /**
     * @return array
     */
    protected function getOrderCopyParams(): array
    {
        return [
            'orderExcludeProps' => $this->orderExcludeProps,
            'basketItemExcludeProps' => $this->basketItemExcludeProps,
        ];
    }

    /**
     * @param int $originOrderId
     * @param bool $checkActiveSubscribe
     * @return OrderSubscribeCopyResult
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Exception
     */
    public function copyOrderById(int $originOrderId, bool $checkActiveSubscribe = true): OrderSubscribeCopyResult
    {
        $orderSubscribe = $this->getSubscribeByOrderId($originOrderId, $checkActiveSubscribe);
        if (!$orderSubscribe) {
            throw new NotFoundException('Подписка на заказ не найдена', 100);
        }
        $params = new OrderSubscribeCopyParams($orderSubscribe, $this->getOrderCopyParams());

        return $this->copyOrder($params);
    }

    /**
     * @param OrderSubscribeCopyParams $params
     * @param bool $deactivateIfEmpty Деактивировать подписку, если заказ пустой
     * @return OrderSubscribeCopyResult
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Exception
     * @throws \FourPaws\SaleBundle\Exception\OrderCopyBasketException
     * @throws \FourPaws\SaleBundle\Exception\OrderCopyShipmentsException
     */
    public function copyOrder(OrderSubscribeCopyParams $params, bool $deactivateIfEmpty = true): OrderSubscribeCopyResult
    {
        $result = new OrderSubscribeCopyResult();
        $result->setOrderSubscribeCopyParams($params);

        $orderSubscribeHistoryService = $this->getOrderSubscribeHistoryService();
        $orderSubscribe = $params->getOrderSubscribe();

        // текущая дата
        $currentDate = $params->getCurrentDate();
        // значение свойтсва COM_WAY заказа (SAP: Communic)
        $comWayValue = OrderPropertyService::COMMUNICATION_SUBSCRIBE;
        // id заказа, копия которого будет создаваться
        $copyOrderId = $params->getCopyOrderId();
        // флаг необходимости выполнения деактивации подписки
        $deactivateSubscription = false;

        // Следующая ближайшая дата доставки по подписке
        $deliveryDate = null;
        try {
            $deliveryDate = $params->getDeliveryDate();
        } catch (\Exception $exception) {
            $result->addError(
                new Error(
                    $exception->getMessage(),
                    'getDeliveryDateException'
                )
            );
        }

        // Ловим возможные исключения на раннем этапе
        if ($result->isSuccess()) {
            try {
                $params->doCopyOrder();
            } catch (\Exception $exception) {
                $result->addError(
                    new Error(
                        $exception->getMessage(),
                        'doCopyOrderException'
                    )
                );
            }
        }

        // Проверка заполненности корзины
        if ($result->isSuccess()) {
            // 1. Если товара нет в наличии, но товар есть на Сайте, при оформлении подписки передавать товар
            // в заказе без анализа остатка товара.
            //  *- это по умолчанию будет делаться при флаге "Разрешить покупку при отсутствии товара" у торгового предложения*
            // 2. Если товара нет на Сайте (из SAP получен признак не отображать товар на Сайте,
            // для случая вывода товара из ассортимента), то удаляем этот товар из заказа.
            // 3. Если всех товаров заказа по подписке нет,
            // нужно деактировать подписку и отправить пользователю уведомление.
            $newOrderBasket = $params->getNewOrder()->getBasket();
            //if ($newOrderBasket->getOrderableItems()->isEmpty()) {
            if ($newOrderBasket->count() <= 0) {
                if ($deactivateIfEmpty) {
                    // Если всех товаров заказа по подписки нет, нужно деактировать подписку и отправить пользователю уведомление.
                    // Фактическая отписка будет ниже, вне блока транзакции
                    $deactivateSubscription = true;
                }
                $result->addError(
                    new Error(
                        'Корзина заказа пуста',
                        'orderBasketEmpty'
                    )
                );
            }
        }

        // Деактивация подписки
        if ($deactivateSubscription) {
            try {
                $deactivateResult = $this->deactivateSubscription($orderSubscribe, true);
                $result->offsetSetData('deactivateResult', $deactivateResult);
                if (!$deactivateResult->isSuccess()) {
                    $result->addError(
                        new Error(
                            'Ошибка деактивации подписки',
                            'subscriptionDeactivateError'
                        )
                    );
                }
            } catch (\Exception $exception) {
                $result->addError(
                    new Error(
                        $exception->getMessage(),
                        'deactivateSubscriptionException'
                    )
                );
            }
        }

        // Уточнение даты возможной доставки заказа
        $resultDeliveryDate = $deliveryDate;
        if ($result->isSuccess()) {
            /*
            Если на дату создания заказа по подписке в составе заказа есть товар А,
            для которого срок доставки изменился и который не может быть доставлен к дате готовности заказа к выдаче,
            Система должна выполнить следующие действия:
            − Установить для атрибута заказа «Communic» значение «07 – Подписка». Требования к параметрам заказа
            определены в документе «4lapy_Интеграция_SAP»;
            − Добавить к заказу комментарий для оператора с текстом:
            «Заказ должен быть готов к выдаче <дата готовности заказа к выдаче без товара А>,
            плановая дата готовности <дата, в которую может быть доступен к выдаче заказ с товаром А>. Причина: <товар А>»;
            − Изменить дату исполнения заказа на дату, в которую заказ с товаром А может быть доступен к выдаче.

            Оператор должен позвонить пользователю и предложить варианты:
            − Доставить заказ с товаром А в дату, когда может быть доступен к выдаче заказ с товаром А;
            − Доставить заказ без товара А в плановую дату доставки заказа по подписке.
            При выборе этого варианта оператор должен удалить товар А из заказа и изменить дату исполнения
            заказа на дату готовности заказа к выдаче.

            Независимо от выбранного пользователем варианта Система не должна изменять параметры подписки на доставку,
            по которой был создан текущий заказ.
            */
            $deliveryCalculationResult = null;
            try {
                // Результат расчета сроков и определения возможности доставки нового заказа.
                // !!! Метод работает с клоном объекта заказа !!!
                $deliveryCalculationResult = $params->getNewOrderDeliveryCalculationResult();
            } catch (\Exception $exception) {
                $result->addError(
                    new Error(
                        $exception->getMessage(),
                        'getNewOrderDeliveryCalculationResultException'
                    )
                );
            }

            if ($deliveryCalculationResult) {
                $notGetInTime = false;
                try {
                    // дата, когда нужно создать заказ, чтобы доставить его к сроку
                    // (результат может быть меньше текущей даты)
                    $orderCreateDate = $params->getDateForOrderCreate();
                    if ($orderCreateDate < $currentDate) {
                        // заказ не может быть доставлен к установленному сроку
                        $notGetInTime = true;
                    }
                } catch (\Exception $exception) {
                    $result->addError(
                        new Error(
                            $exception->getMessage(),
                            'getDateForOrderCreateException'
                        )
                    );
                }

                if ($notGetInTime) {
                    // Комментарий для оператора
                    $orderComments = '';
                    try {
                        // делаем расчет даты доставки по каждой позиции отдельно
                        $offersList = $deliveryCalculationResult->getStockResult()->getOffers(true);
                        $products = [];
                        foreach ($offersList as $offer) {
                            /** @var \FourPaws\Catalog\Model\Offer $offer */
                            $tmpDeliveryCalculationResult = clone $deliveryCalculationResult;
                            $tmpDeliveryCalculationResult->setCurrentDate(
                                (new \DateTime($currentDate->format('d.m.Y H:i:s')))
                            );
                            $tmpDeliveryCalculationResult->setStockResult(
                                $deliveryCalculationResult->getStockResult()->filterByOffer($offer)
                            );
                            $tmpOrderCreate = $this->getDateForOrderCreate(
                                $tmpDeliveryCalculationResult,
                                $deliveryDate,
                                $currentDate
                            );
                            if ($tmpOrderCreate < $currentDate) {
                                $products[] = '['.$offer->getXmlId().'] '.$offer->getName();
                                // работаем через внутренний метод, т.к. в нем учитывается дополнительное время по подписке
                                $tmpDeliveryDate = $this->getOrderDeliveryDate(
                                    $tmpDeliveryCalculationResult,
                                    $currentDate
                                );
                                if ($tmpDeliveryDate > $deliveryDate) {
                                    $resultDeliveryDate = $tmpDeliveryDate;
                                }
                            }
                        }
                        $orderComments .= 'Заказ должен быть готов к выдаче '.$deliveryDate->format('d.m.Y').', ';
                        $orderComments .= 'плановая дата готовности '.$resultDeliveryDate->format('d.m.Y').' ';
                        $orderComments .= 'Причина: '.implode('; ', $products);
                    } catch (\Exception $exception) {
                        $result->addError(
                            new Error(
                                $exception->getMessage(),
                                'offerCalculationDeliveryDateException'
                            )
                        );
                    }

                    if ($orderComments !== '') {
                        try {
                            $params->getNewOrder()->setField('COMMENTS', $orderComments);
                        } catch (\Exception $exception) {
                            $result->addError(
                                new Error(
                                    $exception->getMessage(),
                                    'newOrderSetCommentException'
                                )
                            );
                        }
                    }
                }
            }
        }

        // Заполнение специальных свойств в новом заказе
        if ($result->isSuccess()) {
            try {
                $orderCopyHelper = $params->getOrderCopyHelper();
                $orderCopyHelper->setPropValueByCode(
                    'DELIVERY_INTERVAL',
                    $orderSubscribe->getDeliveryTime()
                );
                $orderCopyHelper->setPropValueByCode(
                    'DELIVERY_DATE',
                    $resultDeliveryDate->format('d.m.Y')
                );
                $orderCopyHelper->setPropValueByCode(
                    'IS_SUBSCRIBE',
                    'Y'
                );
                $orderCopyHelper->setPropValueByCode(
                    'COPY_ORDER_ID',
                    $copyOrderId
                );
                $orderCopyHelper->setPropValueByCode(
                    'COM_WAY',
                    $comWayValue
                );
            } catch (\Exception $exception) {
                $result->addError(
                    new Error(
                        $exception->getMessage(),
                        'orderSetPropsException'
                    )
                );
            }
        }

        // В заказах по подписке только оплата наличными может быть
        if ($result->isSuccess()) {
            $cashPaySystemService = $this->getOrderService()->getCashPaySystemService();
            if ($cashPaySystemService) {
                try {
                    $params->getOrderCopyHelper()->setPayment($cashPaySystemService);
                } catch (\Exception $exception) {
                    $result->addError(
                        new Error(
                            $exception->getMessage(),
                            'orderSetPaymentException'
                        )
                    );
                }
            } else {
                $result->addError(
                    new Error(
                        'Не удалось получить платежную систему "Оплата наличными"',
                        'orderCashPaymentNotFound'
                    )
                );
            }
        }

        // Финальные операции
        if ($result->isSuccess()) {
            $connection = \Bitrix\Main\Application::getConnection();
            // !!! Старт транзакции !!!
            $connection->startTransaction();

            // финализация заказа и сохранение в БД
            try {
                $saveResult = $params->saveNewOrder();
                $result->setOrderSaveResult($saveResult);
                if (!$saveResult->isSuccess()) {
                    $result->addError(
                        new Error(
                            'Ошибка сохранения заказа',
                            'orderSaveError'
                        )
                    );
                }
            } catch (\Exception $exception) {
                $result->addError(
                    new Error(
                        $exception->getMessage(),
                        'saveNewOrderException'
                    )
                );
            }

            // добавление записи о созданном заказе в историю
            if ($result->isSuccess()) {
                try {
                    $historyAddResult = $orderSubscribeHistoryService->add(
                        $orderSubscribe,
                        $result->getNewOrderId(),
                        $deliveryDate
                    );
                    $result->offsetSetData('historyAddResult', $historyAddResult);
                    if (!$historyAddResult->isSuccess()) {
                        $result->addError(
                            new Error(
                                'Ошибка сохранения записи в истории',
                                'orderHistoryAddError'
                            )
                        );
                    }
                } catch (\Exception $exception) {
                    $result->addError(
                        new Error(
                            $exception->getMessage(),
                            'orderSubscribeHistoryAddException'
                        )
                    );
                }
            }

            // отправка уведомлений во внешние системы
            if ($result->isSuccess()) {
                try {
                    $this->doNewOrderIntegration($result->getNewOrderId());
                } catch (\Exception $exception) {
                    $result->addError(
                        new Error(
                            $exception->getMessage(),
                            'newOrderIntegrationException'
                        )
                    );
                }
            }

            // !!! Конец транзакции !!!
            if ($result->isSuccess()) {
                $connection->commitTransaction();
            } else {
                $connection->rollbackTransaction();
            }
        }

        if (!$result->isSuccess()) {
            $this->log()->critical(
                sprintf(
                    'Ошибка копирования заказа по подписке - %s',
                    implode("\n", $result->getErrorMessages())
                ),
                [
                    'ORIGIN_ORDER_ID' => $params->getOriginOrderId(),
                    'COPY_ORDER_ID' => $params->getCopyOrderId(),
                ]
            );
        }

        return $result;
    }

    /**
     * Обрабатывает подписку на заказ и если пришло время создания очередного заказа - создает его
     *
     * @param OrderSubscribe $orderSubscribe
     * @param bool $deactivateIfEmpty
     * @param string|\DateTimeInterface $currentDate
     * @return Result
     * @throws InvalidArgumentException
     */
    public function processOrderSubscribe(
        OrderSubscribe $orderSubscribe,
        bool $deactivateIfEmpty = true,
        $currentDate = ''
    ): Result
    {
        $result = new Result();

        if (!$orderSubscribe->isActive()) {
            $result->addError(
                new Error(
                    'Подписка отменена',
                    'orderSubscribeNotActive'
                )
            );
        }

        if ($result->isSuccess()) {
            try {
                // сохраняем текущую дату и время проверки
                $orderSubscribe->setLastCheck((new DateTime()));
                $updateResult = $this->update($orderSubscribe);
                if (!$updateResult->isSuccess()) {
                    $result->addError(
                        new Error(
                            'Ошибка обновления даты проверки подписки',
                            'orderSubscribeUpdateError'
                        )
                    );
                }
            } catch (\Exception $exception) {
                $result->addError(
                    new Error(
                        $exception->getMessage(),
                        'orderSubscribeUpdateException'
                    )
                );
            }
        }

        $copyParams = new OrderSubscribeCopyParams($orderSubscribe, $this->getOrderCopyParams());
        $copyParams->setCurrentDate($currentDate);

        $data = [
            'copyParams' => $copyParams,
            'copyResult' => null,
            'alreadyCreated' => false,
            'canCopyOrder' => false,
        ];

        if ($result->isSuccess()) {
            try {
                // проверим, не создавался ли уже заказ для этой даты
                $data['alreadyCreated'] = $copyParams->isCurrentDeliveryDateOrderAlreadyCreated();
            } catch (\Exception $exception) {
                $result->addError(
                    new Error(
                        $exception->getMessage(),
                        'isCurrentDeliveryDateOrderAlreadyCreatedException'
                    )
                );
            }
        }

        if ($result->isSuccess() && !$data['alreadyCreated']) {
            try {
                // дата, когда нужно создать заказ, чтобы доставить его к сроку
                // (результат может быть меньше текущей даты)
                $orderCreateDate = $copyParams->getDateForOrderCreate();
                if ($orderCreateDate <= $copyParams->getCurrentDate()) {
                    // наступила или уже прошла дата создания заказа
                    // (если дата меньше текущей, то заказ будет создан с комментариями для оператора)
                    $data['canCopyOrder'] = true;
                }
            } catch (\Exception $exception) {
                $result->addError(
                    new Error(
                        $exception->getMessage(),
                        'getDateForOrderCreateException'
                    )
                );
            }
        }

        if ($result->isSuccess() && $data['canCopyOrder']) {
            try {
                $data['copyResult'] = $this->copyOrder($copyParams, $deactivateIfEmpty);
                if (!$data['copyResult']->isSuccess()) {
                    $result->addErrors(
                        $data['copyResult']->getErrors()
                    );
                }
            } catch (\Exception $exception) {
                $result->addError(
                    new Error(
                        $exception->getMessage(),
                        'copyOrderException'
                    )
                );
            }
        }

        $result->setData($data);

        return $result;
    }

    /**
     * Обход подписок и генерация заказов
     *
     * @param int $limit Лимит подписок за шаг
     * @param int $checkIntervalHours Время, вычитаемое от текущей даты, для запроса подписок
     * @param string|\DateTimeInterface $currentDate Дата, которая будет установлена в качестве сегодняшней
     * @param bool $extResult
     * @return Result
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Exception
     * @throws \FourPaws\PersonalBundle\Exception\RuntimeException
     */
    public function sendOrders(int $limit = 50, int $checkIntervalHours = 3, $currentDate = '', bool $extResult = false): Result
    {
        $result = new Result();
        $resultData = [];

        // запрашиваем подписки с последней датой проверки меньше $checkIntervalHours часов назад
        $lastCheckDateTime = (new \DateTime())->sub((new \DateInterval('PT'.$checkIntervalHours.'H')));

        $params = [];
        $params['limit'] = $limit;
        $params['filter']['=UF_ACTIVE'] = 1;
        $params['filter'][] = [
            'LOGIC' => 'OR',
            [
                'UF_LAST_CHECK' => false
            ],
            [
                '<UF_LAST_CHECK' => new DateTime($lastCheckDateTime->format('d.m.Y H:i:s'), 'd.m.Y H:i:s')
            ]
        ];
        $params['order'] = [
            'UF_LAST_CHECK' => 'ASC',
            'ID' => 'ASC',
        ];

        $checkOrdersList = $this->orderSubscribeRepository->findBy($params);
        foreach ($checkOrdersList as $orderSubscribe) {
            /** @var OrderSubscribe $orderSubscribe */
            $curResult = $this->processOrderSubscribe($orderSubscribe, true, $currentDate);
            if ($extResult) {
                $resultData[] = $curResult;
            }
            if ($curResult->isSuccess()) {
                // Отправка sms: "Через 3 дня Вам будет доставлен заказ по подписке..."
                /** @var OrderSubscribeCopyParams $copyParams */
                $copyParams = $curResult->getData()['copyParams'];
                if ($copyParams) {
                    $upcomingDays = $this->getDeliveryDateUpcomingDays(
                        $copyParams->getRealDeliveryDate(), // это дата с учетом даты доставки уже возможно созданного заказа
                        $copyParams->getCurrentDate()
                    );
                    if ($upcomingDays <= static::UPCOMING_DAYS_DELIVERY_MESS) {
                        $this->sendOrderSubscribeUpcomingDeliveryMessage($copyParams);
                    }
                }
            }
            if (!$curResult->isSuccess() && $result->isSuccess()) {
                $result->addError(
                    new Error(
                        'Имеются подписки с ошибками',
                        'errorItem'
                    )
                );
            }
        }

        $result->setData($resultData);

        return $result;
    }

    /**
     * @param OrderSubscribe $orderSubscribe
     * @param bool $sendNotifications
     * @return UpdateResult
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws NotImplementedException
     * @throws SystemException
     * @throws \Exception
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     */
    protected function deactivateSubscription(OrderSubscribe $orderSubscribe, bool $sendNotifications = true)
    {
        $updateResult = $this->update(
            $orderSubscribe->getId(),
            [
                'UF_ACTIVE' => 0
            ]
        );
        if ($updateResult->isSuccess()) {
            $orderSubscribe->setActive(false);
            TaggedCacheHelper::clearManagedCache(
                [
                    'order:item:'.$orderSubscribe->getOrderId()
                ]
            );
            if ($sendNotifications) {
                $this->sendAutoUnsubscribeOrderNotification($orderSubscribe);
            }
        }

        return $updateResult;
    }

    /**
     * Отправка уведомлений во внешние системы о созданном заказе по подписке
     *
     * @param int $orderId
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectNotFoundException
     */
    protected function doNewOrderIntegration(int $orderId)
    {
        $order = \Bitrix\Sale\Order::load($orderId);

        $saleOrderService = $this->getOrderService();
        if ($saleOrderService->isOnlinePayment($order)) {
            // у заказа онлайн-оплата (чего, вообще-то, не должно быть у заказа по подписке),
            // а значит, отправка во внешние системы будет после получения оплаты
            return;
        }

        // передача заказа в SAP
        /** @var ConsumerRegistry $consumerRegistry */
        $consumerRegistry = Application::getInstance()->getContainer()->get(
            ConsumerRegistry::class
        );
        $consumerRegistry->consume($order);

        // отправка email
        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()->getContainer()->get(
            NotificationService::class
        );
        $notificationService->sendOrderSubscribeOrderNewMessage($order);
    }

    /**
     * Отправка уведомлений об автоматической отмене подписки (админам)
     *
     * @param OrderSubscribe $orderSubscribe
     * @throws ApplicationCreateException
     * @throws ArgumentNullException
     * @throws NotFoundException
     * @throws NotImplementedException
     * @throws \Exception
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     */
    public function sendAutoUnsubscribeOrderNotification(OrderSubscribe $orderSubscribe)
    {
        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()->getContainer()->get(
            NotificationService::class
        );
        $notificationService->sendAutoUnsubscribeOrderMessage($orderSubscribe);
    }

    /**
     * Отправка уведомлений о создании подписки
     *
     * @param OrderSubscribe $orderSubscribe
     * @throws ApplicationCreateException
     */
    public function sendOrderSubscribedNotification(OrderSubscribe $orderSubscribe)
    {
        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()->getContainer()->get(
            NotificationService::class
        );
        $notificationService->sendOrderSubscribedMessage($orderSubscribe);
    }

    /**
     * Информация о предстоящей доставке заказа по подписке (за N дней до доставки).
     *
     * @param OrderSubscribeCopyParams $copyParams
     * @throws ApplicationCreateException
     */
    public function sendOrderSubscribeUpcomingDeliveryMessage(OrderSubscribeCopyParams $copyParams)
    {
        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()->getContainer()->get(
            NotificationService::class
        );
        $notificationService->sendOrderSubscribeUpcomingDeliveryMessage($copyParams);
    }
}
