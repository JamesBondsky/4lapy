<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 25.03.2019
 * Time: 17:25
 */

namespace FourPaws\PersonalBundle\Service;


use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\Error;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Result;
use Bitrix\Main\Security\SecurityException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserFieldTable;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Shipment;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Collection\UserFieldEnumCollection;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Service\IntervalService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\AppBundle\Service\UserFieldEnumService;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\HighloadHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Entity\OrderSubscribeCopyParams;
use FourPaws\PersonalBundle\Entity\OrderSubscribeCopyResult;
use FourPaws\PersonalBundle\Entity\OrderSubscribeItem;
use FourPaws\PersonalBundle\Exception\NotFoundException;
use FourPaws\PersonalBundle\Exception\OrderSubscribeException;
use FourPaws\PersonalBundle\Repository\OrderSubscribeItemRepository;
use FourPaws\PersonalBundle\Repository\OrderSubscribeRepository;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Helper\PriceHelper;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\NotificationService;
use FourPaws\SaleBundle\Service\OrderPropertyService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\SapBundle\Consumer\ConsumerRegistry;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use http\Exception\InvalidArgumentException;
use mysql_xdevapi\Exception;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Exception\NotImplementedException;

class OrderSubscribeService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    const UPCOMING_DAYS_DELIVERY_MESS = 3;

    // при запуске из консоли SITE_ID определяется как 's2'
    const SITE_ID = 's1';

    /**
     * Интервалы доставки
     * @var array $frequencies
     */
    private $frequencies;

    /** @var OrderSubscribeRepository $orderSubscribeRepository */
    private $orderSubscribeRepository;

    /** @var OrderSubscribeItemRepository $orderSubscribeRepository */
    private $orderSubscribeItemRepository;

    /** @var CurrentUserProviderInterface $currentUser */
    private $currentUser;

    /** @var LocationService $locationService */
    private $locationService;

    /** @var OrderService $personalOrderService */
    private $personalOrderService;

    /** @var DeliveryService $deliveryService */
    private $deliveryService;

    /** @var \FourPaws\SaleBundle\Service\OrderService $saleOrderService */
    private $saleOrderService;

    /** @var BasketService $basketService */
    private $basketService;

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
        // [LP22-37] информация об операторах, создавших заказ
        'OPERATOR_EMAIL', 'OPERATOR_SHOP',
    ];


    /**
     * OrderSubscribeService constructor.
     *
     * @param OrderSubscribeRepository $orderSubscribeRepository
     * @param CurrentUserProviderInterface $currentUserProvider
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(
        OrderSubscribeRepository $orderSubscribeRepository,
        OrderSubscribeItemRepository $orderSubscribeItemRepository,
        CurrentUserProviderInterface $currentUserProvider,
        LocationService $locationService,
        BasketService $basketService
    )
    {
        $this->orderSubscribeRepository = $orderSubscribeRepository;
        $this->orderSubscribeItemRepository = $orderSubscribeItemRepository;
        $this->currentUser = $currentUserProvider;
        $this->locationService = $locationService;
        $this->basketService = $basketService;
    }


    /**
     * @param OrderSubscribe $subscribe
     * @return bool
     * @throws \Bitrix\Main\ObjectException
     */
    public function add(OrderSubscribe $subscribe): Result
    {
        $result = new Result();

        try {
            if (empty($subscribe->getUserId())) {
                try {
                    $subscribe->setUserId($this->currentUser->getCurrentUserId());
                } catch (NotAuthorizedException $e) {
                    // можем привязать пользователя позже, т.к. доступно для неавторизованных
                }
            }
            if (empty($subscribe->getLocationId())) {
                $subscribe->setLocationId($this->locationService->getCurrentLocation());
            }

            $this->countNextDate($subscribe);
            $this->orderSubscribeRepository->setEntity($subscribe);
            if (!$this->orderSubscribeRepository->create()) {
                $result->addError(new Error('Неизвестная ошибка при создании подписки', 'orderSubscriveService::add'));
            }

            $result->setData(['ID' => $subscribe->getId()]);
        } catch (\Exception $e) {
            $result->addError(new Error($e->getMessage(), 'orderSubscriveService::add'));
            $this->log()->error(sprintf('failed to create order subscribe: %s', $e->getMessage()), [
                'userId' => $this->currentUser->getCurrentUser()->getId(),
            ]);
        }

        return $result;
    }


    public function update(OrderSubscribe $subscribe): UpdateResult
    {
        $result = new UpdateResult();

        /** @var OrderSubscribe $updateEntity */
        try{
            $updateEntity = $this->orderSubscribeRepository->findById($subscribe->getId());
            // Обход подписок запускается из консоли и здесь возникает ошибка, когда юзер не авторизован
            // if ($updateEntity->getUserId() !== $this->currentUser->getCurrentUserId()) {
            //     throw new SecurityException('не хватает прав доступа для совершения данной операции');
            // }
        } catch (\Exception $e) {
            $result->addError(new Error($e->getMessage(), $e->getCode()));
        }


        if ($subscribe->getUserId() === 0) {
            $subscribe->setUserId($updateEntity->getUserId());
        }

        $subscribe->setDateUpdate(new DateTime());

        try{
            $this->orderSubscribeRepository->setEntity($subscribe)->update();
        } catch (\Exception $e) {
            $result->addError(new Error($e->getMessage(), $e->getCode()));
        }

        return $result;
    }

    /**
     * @param OrderSubscribe $orderSubscribe
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function isCurrentDeliveryDateOrderAlreadyCreated(OrderSubscribe $orderSubscribe): bool
    {
        $orderSubscribeHistoryService = $this->getOrderSubscribeHistoryService();
        $result = $orderSubscribeHistoryService->wasOrderCreated(
            $orderSubscribe->getOrderId(),
            $orderSubscribe->getDeliveryDate()
        );

        return $result;
    }

    /**
     * @param int $id
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SecurityException
     * @throws SystemException
     * @throws ApplicationCreateException
     */
    public function delete(int $id): bool
    {
        try {
            /** @var OrderSubscribe $orderSubscribe */
            $orderSubscribe = $this->orderSubscribeRepository->findById($id);
        } catch (\FourPaws\AppBundle\Exception\NotFoundException $e) {
            return true;
        }

//        if ($orderSubscribe->getUserId() !== $this->currentUser->getCurrentUserId()) {
//            throw new SecurityException('не хватает прав доступа для совершения данной операции');
//        }

        $deleteEntityItems = $this->orderSubscribeItemRepository->findBySubscribe($id);
        /** @var OrderSubscribeItem $item */
        foreach ($deleteEntityItems as $item) {
            $this->deleteSubscribeItem($item->getId());
        }

        // удалим созданные по подписке заказы
        $orderIdsForDelete = $this->getOrderSubscribeHistoryService()->getNotDeliveredOrderIds($orderSubscribe);
        if(count($orderIdsForDelete) > 0){
            foreach($orderIdsForDelete as $orderIdForDelete){
                $orderService = $this->getOrderService();
                $order = $orderService->getOrderById($orderIdForDelete);
                $order->setField('CANCELED', 'Y');
                $order->save();
            }
        }

        return $this->orderSubscribeRepository->delete($id);
    }

    /**
     * @param OrderSubscribe $orderSubscribe
     * @param OrderSubscribeItem $orderSubscribeItem
     * @return bool
     * @throws \Exception
     */
    public function addSubscribeItem(OrderSubscribe $orderSubscribe, OrderSubscribeItem $orderSubscribeItem): bool
    {
        if (!$orderSubscribe->getId()) {
            throw new OrderSubscribeException('Добавлять товары можно только на существующую подписку');
        }

        $orderSubscribeItem->setSubscribeId($orderSubscribe->getId());
        $this->orderSubscribeItemRepository->setEntity($orderSubscribeItem);

        return $this->orderSubscribeItemRepository->create();
    }

    /**
     * @param OrderSubscribeItem $orderSubscribeItem
     * @return bool
     * @throws \Exception
     */
    public function updateSubscribeItem(OrderSubscribeItem $orderSubscribeItem): bool
    {
        if (!$orderSubscribeItem->getId()) {
            throw new Exception('Обновлять можно только существующие товары');
        }
        return $this->orderSubscribeItemRepository->setEntity($orderSubscribeItem)->update();
    }

    /**
     * @param OrderSubscribeItem $orderSubscribeItem
     * @return bool
     * @throws \Exception
     */
    public function deleteSubscribeItem($id): bool
    {
        return $this->orderSubscribeItemRepository->delete($id);
    }

    /**
     * @param int $orderSubscribeId
     * @return bool
     * @throws \Exception
     */
    public function deleteAllItems($orderSubscribeId): bool
    {
        $items = $this->orderSubscribeItemRepository->findBySubscribe($orderSubscribeId);
        foreach($items as $item){
            $this->orderSubscribeItemRepository->delete($item->getId());
        }
        return true;
    }


    /**
     * @param OrderSubscribe $orderSubscribe
     * @throws \Bitrix\Main\ObjectException
     * @throws \Exception
     */
    public function countNextDate(OrderSubscribe &$orderSubscribe)
    {
        $freqs = $this->getFrequencies();
        $nextDate = $orderSubscribe->getNextDate();
        if(null === $nextDate){
            $nextDate = new DateTime();
        }

        switch ($orderSubscribe->getFrequency()){
            case $freqs['WEEK_1']['ID']:
                $nextDate->add("+1 week");
                break;
            case $freqs['WEEK_2']['ID']:
                $nextDate->add("+2 week");
                break;
            case $freqs['WEEK_3']['ID']:
                $nextDate->add("+3 week");
                break;
            case $freqs['WEEK_4']['ID']:
                $nextDate->add("+4 week");
                break;
            case $freqs['WEEK_5']['ID']:
                $nextDate->add("+5 week");
                break;
            case $freqs['WEEK_6']['ID']:
                $nextDate->add("+6 week");
                break;
            default:
                throw new \Exception('Не найдена подходящая периодичность');
        }

        $orderSubscribe->setNextDate($nextDate);
        return $nextDate;
    }

    /**
     * @param OrderSubscribe $orderSubscribe
     * @throws \Bitrix\Main\ObjectException
     * @throws \Exception
     */
    public function getPreviousDate(OrderSubscribe $orderSubscribe)
    {
        $freqs = $this->getFrequencies();
        $nextDate = clone $orderSubscribe->getNextDate();
        if(null === $nextDate){
            $nextDate = new DateTime();
        }

        switch ($orderSubscribe->getFrequency()){
            case $freqs['WEEK_1']['ID']:
                $nextDate->add("-1 week");
                break;
            case $freqs['WEEK_2']['ID']:
                $nextDate->add("-2 week");
                break;
            case $freqs['WEEK_3']['ID']:
                $nextDate->add("-3 week");
                break;
            case $freqs['WEEK_4']['ID']:
                $nextDate->add("-4 week");
                break;
            case $freqs['WEEK_5']['ID']:
                $nextDate->add("-5 week");
                break;
            case $freqs['WEEK_6']['ID']:
                $nextDate->add("-6 week");
                break;
            default:
                throw new Exception('Не найдена подходящая периодичность');
        }

        return $nextDate;
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Bitrix\Main\LoaderException
     */
    public function getFrequencies(): array
    {
        if(null === $this->frequencies){
            $userFieldId = UserFieldTable::query()->setSelect(['ID', 'XML_ID'])->setFilter(
                [
                    'FIELD_NAME' => 'UF_FREQUENCY',
                    'ENTITY_ID' => 'HLBLOCK_' . HighloadHelper::getIdByName('OrderSubscribe'),
                ]
            )->exec()->fetch()['ID'];
            $userFieldEnum = new \CUserFieldEnum();
            $res = $userFieldEnum->GetList([], ['USER_FIELD_ID' => $userFieldId]);
            while ($item = $res->Fetch()) {
                $this->frequencies[$item['XML_ID']] = $item;
            }
        }

        return $this->frequencies;
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     * @return bool
     */
    public function isWeekFrequency($id): bool
    {
        foreach($this->getFrequencies() as $frequency){
            if($frequency['ID'] == $id){
                return strpos($frequency['XML_ID'], 'WEEK') !== false;
            }
        }

        return false;
    }


    /**
     * @param $frequency
     * @return string|null
     * @throws \Exception
     */
    public function getFrequencyType($frequency)
    {
        $arSlice = explode("_", $frequency['XML_ID']);
        return count($arSlice) > 1 ? (string)$arSlice[0] : null;
    }

    /**
     * @param $frequency
     * @return int|null
     * @throws \Exception
     */
    public function getFrequencyValue($frequency)
    {
        $arSlice = explode("_", $frequency['XML_ID']);
        return count($arSlice) > 1 ? (int)$arSlice[1] : null;
    }

    /**
     * @param $id
     * @return BaseEntity|OrderSubscribe|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\NotFoundException
     */
    public function getById($id)
    {
        return $this->orderSubscribeRepository->findById($id);
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
        $result = !$order->getManzanaId();

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
        $params['filter']['!UF_ORDER_ID'] = false;

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

        return $this->getPersonalOrderService()->getUserOrdersOld($params);
    }

    /**
     * @return OrderSubscribeRepository
     */
    public function getOrderSubscribeRepository(): OrderSubscribeRepository
    {
        return $this->orderSubscribeRepository;
    }

    /**
     * @return OrderSubscribeItemRepository
     */
    public function getOrderSubscribeItemRepository(): OrderSubscribeItemRepository
    {
        return $this->orderSubscribeItemRepository;
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
     * @param OrderSubscribe $subscribe
     * @return CalculationResultInterface
     * @throws NotFoundException
     */
    public function getDeliveryCalculationResult(OrderSubscribe $subscribe)
    {
        $calculationResult = null;
        try {
            $deliveryService = $this->getDeliveryService();
            /** @var StoreService $storeService */
            $storeService = Application::getInstance()->getContainer()->get('store.service');

            $deliveryCode = $deliveryService->getDeliveryCodeById($subscribe->getDeliveryId());
            $basket = $this->getBasketBySubscribeId($subscribe->getId());
            $arCalculationResult = $deliveryService->getByBasket($basket, $subscribe->getLocationId(), [$deliveryCode]);
            $calculationResult = reset($arCalculationResult);

            if($deliveryService->isPickup($calculationResult)){
                $calculationResult->setSelectedStore($storeService->getStoreByXmlId($subscribe->getDeliveryPlace()));
            }
        } catch (\Exception $e) {
            throw new NotFoundException($e->getMessage());
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

        // 08.06.2018 Убрал - слишком много вопросов задается
        // добавляем 1 день для подстраховки
        //$deliveryDate->add(new \DateInterval('P1D'));

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
     * @param $id
     * @return ArrayCollection
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function getItemsBySubscribeId($id): ArrayCollection
    {
        return $this->orderSubscribeItemRepository->findBySubscribe($id);
    }

    /**
     * Метод возвращает объект корзины
     * !!! Чтобы создать заказ необходимо после сохранить корзину в БД !!!
     *
     * @param $id
     * @return BasketBase
     * @throws OrderSubscribeException
     */
    public function getBasketBySubscribeId($id): Basket
    {
        try {
            $subscribe = $this->getById($id);

            if($this->getCurrentUserService()->getCurrentUserId() != $subscribe->getUserId()){
                throw new OrderSubscribeException('Для корректного расчёта цен необходимо быть авторизованным за пользователя из подписки');
            }

            $subscribeItems = $this->orderSubscribeItemRepository->findBySubscribe($id);
            $basket = Basket::create(self::SITE_ID);
            $items = [];
            /** @var OrderSubscribeItem $item */
            foreach($subscribeItems as $item){
                $items[$item->getOfferId()] = [
                    'OFFER_ID' => $item->getOfferId(),
                    'QUANTITY' => $item->getQuantity()
                ];
            }

            $offers = (new OfferQuery())
                ->withFilter(["ID" => array_column($items, 'OFFER_ID')])
                ->exec();

            /** @var Offer $offer */
            foreach($offers as $offer){
                $items[$offer->getId()]['PRICE'] = $offer->getSubscribePrice();
                $items[$offer->getId()]['BASE_PRICE'] = $offer->getPrice();
                $items[$offer->getId()]['NAME'] = $offer->getName();
                $items[$offer->getId()]['WEIGHT'] = $offer->getCatalogProduct()->getWeight();
                $items[$offer->getId()]['DETAIL_PAGE_URL'] = $offer->getDetailPageUrl();
                $items[$offer->getId()]['PRODUCT_XML_ID'] = $offer->getXmlId();
            }

            foreach($items as $item){
                $basketItem = BasketItem::create($basket, 'sale', $item['OFFER_ID']);
                $basketItem->setFields([
                    'PRICE'                  => $item['PRICE'],
                    'BASE_PRICE'             => $item['BASE_PRICE'],
                    'CUSTOM_PRICE'           => BitrixUtils::BX_BOOL_TRUE,
                    'QUANTITY'               => $item['QUANTITY'],
                    'CURRENCY'               => CurrencyManager::getBaseCurrency(),
                    'NAME'                   => $item['NAME'],
                    'WEIGHT'                 => $item['WEIGHT'],
                    'DETAIL_PAGE_URL'        => $item['DETAIL_PAGE_URL'],
                    'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
                    'CATALOG_XML_ID'         => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS),
                    'PRODUCT_XML_ID'         => $item['PRODUCT_XML_ID'],
                    'CAN_BUY'                => "Y",
                ]);

                /** @noinspection PhpInternalEntityUsedInspection */
                $basket->addItem($basketItem);
            }

            //$basket->save();

            return $basket;
        } catch(\Exception $e) {
            throw new OrderSubscribeException($e->getMessage(), $e->getCode());
        }
    }

    public function getPaymentCodes()
    {
        return [OrderPayment::PAYMENT_CASH, OrderPayment::PAYMENT_CASH_OR_CARD];
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
        // текущая дата без времени
        $currentDateNoTime = new \DateTime($currentDate->format('d.m.Y'));

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

        // деактивация подписки
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
                    // дата, когда нужно создать заказ, чтобы доставить его к сроку,
                    // дата возвращается без времени (00:00:00)
                    // (результат может быть меньше текущей даты)
                    $orderDeliveryDate = clone $deliveryCalculationResult->getDeliveryDate();
                    $subscribeDeliveryDate = new \DateTime($orderSubscribe->getNextDate()->format('d.m.Y'));
                    $daysDiff = $this->compareInDays($orderDeliveryDate, $subscribeDeliveryDate);
                    if ($daysDiff < 0) {
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

                            if ($tmpOrderCreate < $currentDateNoTime) {
                                $products[] = '['.$offer->getXmlId().'] '.$offer->getName();
                                // работаем через внутренний метод, т.к. в нем учитывается дополнительное время по подписке
                                $tmpDeliveryDate = $this->getOrderDeliveryDate(
                                    $tmpDeliveryCalculationResult,
                                    $currentDate
                                );
                                if ($tmpDeliveryDate > $resultDeliveryDate) {
                                    $resultDeliveryDate = $tmpDeliveryDate;
                                }
                            }
                        }
                        $orderComments .= 'Заказ должен быть готов к выдаче '.$deliveryDate->format('d.m.Y H:i:s').', ';
                        $orderComments .= 'плановая дата готовности '.$resultDeliveryDate->format('d.m.Y H:i:s').' ';
                        $orderComments .= '\r\n Причина: '.implode(";\r\n ", $products);
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
                    str_replace(" ", "", $orderSubscribe->getDeliveryTime())
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
//        if ($result->isSuccess()) {
//            $cashPaySystemService = $this->getPayments();
//            if ($cashPaySystemService) {
//                try {
//                    $params->getOrderCopyHelper()->setPayment($cashPaySystemService);
//                } catch (\Exception $exception) {
//                    $result->addError(
//                        new Error(
//                            $exception->getMessage(),
//                            'orderSetPaymentException'
//                        )
//                    );
//                }
//            } else {
//                $result->addError(
//                    new Error(
//                        'Не удалось получить платежную систему "Оплата наличными"',
//                        'orderCashPaymentNotFound'
//                    )
//                );
//            }
//        }

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
                    $this->doNewOrderIntegration(
                        $params->getOrderCopyHelper()->getNewOrder()
                    );
                } catch (\Exception $exception) {
                    $result->addError(
                        new Error(
                            $exception->getMessage(),
                            'newOrderIntegrationException'
                        )
                    );
                }
            }

            // установка следующей даты доставки
            if ($result->isSuccess()) {
                try {
                    $this->countNextDate($orderSubscribe);
                    $this->update($orderSubscribe);
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
                    implode("\n", $result->getErrorMessagesEx())
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

                // устанавливаем текущего пользователя и местоположение
                // для расчёта цен и даты доставки
                global $USER;
                if(!$USER->IsAuthorized()){
                    $USER->authorize($orderSubscribe->getUserId());

                    /** @var UserService $currentUser */
                    $currentUser = $this->getCurrentUserService();
                    $currentUser->setSelectedCity($orderSubscribe->getLocationId());
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
    public function deactivateSubscription(OrderSubscribe $orderSubscribe, bool $sendNotifications = true)
    {
        $updateResult = $this->update($orderSubscribe->setActive(false));
        if ($updateResult->isSuccess()) {

            $orderIdsForDelete = $this->getOrderSubscribeHistoryService()->getNotDeliveredOrderIds($orderSubscribe);
            if(count($orderIdsForDelete) > 0){
                foreach($orderIdsForDelete as $orderIdForDelete){
                    $orderService = $this->getOrderService();
                    $order = $orderService->getOrderById($orderIdForDelete);
                    $order->setField('CANCELED', 'Y');
                    $order->save();
                }
            }

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
     * @param \Bitrix\Sale\Order|int $order
     * @throws ApplicationCreateException
     * @throws ArgumentNullException
     * @throws InvalidArgumentException
     * @throws NotImplementedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     */
    protected function doNewOrderIntegration($order)
    {
        if (is_numeric($order)) {
            $order = \Bitrix\Sale\Order::load((int)$order);
        }

        if (!($order instanceof \Bitrix\Sale\Order)) {
            throw new InvalidArgumentException('Argument "order" is not valid', 100);
        }

        $saleOrderService = $this->getOrderService();
        if ($saleOrderService->isOnlinePayment($order)) {
            // у заказа онлайн-оплата (чего, вообще-то, не должно быть у заказа по подписке),
            // а значит, отправка во внешние системы будет после получения оплаты
            return;
        }

        // передача заказа в SAP (upd: отправляется штатными средставми)
//        /** @var ConsumerRegistry $consumerRegistry */
//        $consumerRegistry = Application::getInstance()->getContainer()->get(
//            ConsumerRegistry::class
//        );
//        $consumerRegistry->consume($order);

        // отправка email
        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()->getContainer()->get(
            NotificationService::class
        );
        $notificationService->sendOrderSubscribeOrderNewMessage($order);
    }

    /**
     * Создание подписки
     *
     * @param OrderStorage $storage
     * @param $data
     * @return Result
     */
    public function createSubscriptionByRequest(OrderStorage $storage, Request $request): Result
    {
        $result = new Result();
        $data = $request->request->all();

        try {
            /** @var OrderStorageService $orderStorageService */
            $orderStorageService = Application::getInstance()->getContainer()->get(OrderStorageService::class);

            $deliveryId = $storage->getDeliveryId();

            if(!$storage->getSubscribeId()) {
                $subscribe = (new OrderSubscribe());
            }
            else{
                $subscribe = $this->getById($storage->getSubscribeId());
            }

            $selectedDelivery = $this->getDeliveryService()->getNextDeliveries($orderStorageService->getSelectedDelivery($storage), 10)[$storage->getDeliveryDate()];
            $deliveryDate = new DateTime($selectedDelivery->getDeliveryDate()->format('d.m.Y H:i:s'));
            if(!$deliveryDate){
                throw new OrderSubscribeException("Некорректная дата первой доставки");
            }
            $subscribe->setNextDate($deliveryDate)
                ->setCheckDays((new \DateTime($deliveryDate->toString())));

            if (($intervalIndex = $storage->getDeliveryInterval() - 1) >= 0) {
                /** @var Interval $interval */
                $interval = $selectedDelivery->getAvailableIntervals()[$intervalIndex];
            }

            $subscribe->setDeliveryId($deliveryId)
                ->setFrequency($data['subscribeFrequency'])
                ->setDeliveryTime((string)$interval)
                ->setActive(false);

            if($this->getDeliveryService()->isDelivery($orderStorageService->getSelectedDelivery($storage))){
                $subscribe->setDeliveryPlace($data['addressId']);
            }
            elseif($this->getDeliveryService()->isPickup($orderStorageService->getSelectedDelivery($storage))){
                if(!$data['deliveryPlaceCode'] && !$data['shopId']){
                    throw new OrderSubscribeException("Не выбран магазин для самовывоза");
                }
                $subscribe->setDeliveryPlace($data['deliveryPlaceCode'] ?: $data['shopId']);
            }

            if($subscribe->getId() > 0){
                $this->update($subscribe);
            }
            else{
                $result = $this->add($subscribe);

                if(!$result->isSuccess()){
                    throw new OrderSubscribeException(sprintf('Failed to create order subscribe: %s', print_r($result->getErrorMessages(),true)));
                }

                $items = $this->basketService->getBasket()->getOrderableItems();
                /** @var BasketItem $basketItem */
                foreach($items as $basketItem){
                    $subscribeItem = (new OrderSubscribeItem())
                        ->setOfferId($basketItem->getProductId())
                        ->setQuantity($basketItem->getQuantity());

                    if(!$this->addSubscribeItem($subscribe, $subscribeItem)){
                        throw new OrderSubscribeException(sprintf('Failed to create order subscribe item: %s', print_r($data,true)));
                    }
                }
            }

            $result->setData(['subscribeId' => $subscribe->getId()]);
        } catch (\Exception $e) {
            $result->addError(new Error($e->getMessage(), 'createSubscription'));
        }

        return $result;
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


    /**
     * @param OrderSubscribe $orderSubscribe
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     */
    public function isDelivery(OrderSubscribe $orderSubscribe)
    {
        $deliveryId = $orderSubscribe->getDeliveryId();
        $deliveryService = $this->getDeliveryService();
        $deliveryCode = $deliveryService->getDeliveryCodeById($deliveryId);
        return $deliveryService->isDeliveryCode($deliveryCode);
    }

    /**
     * @param \DateTime $date1
     * @param \DateTime $date2
     * @return int
     */
    public function compareInDays(\DateTime $date1, \DateTime $date2)
    {
        $date1->setTime(0,0,0,0);
        $date2->setTime(0,0,0,0);
        return $date1->diff($date2)->format('%r%d');
    }

    public function countBasketPriceDiff(BasketBase $basketSubscribe): float
    {
        $priceDiff = 0;
        $offerIds = [];
        foreach ($basketSubscribe as $basketItem){
            $offerIds[] = $basketItem->getProductId();
        }

        /** @var OfferCollection $offerCollection */
        $offerCollection = (new OfferQuery())->withFilter(['ID' => $offerIds])->exec();

        /** @var BasketItem $basketItem */
        foreach ($basketSubscribe as $basketItem){
            /** @var Offer $offer */
            $offer = $offerCollection->getById($basketItem->getProductId());
            $percent = $offer->getSubscribeDiscount();
            $priceSubscribe = $basketItem->getPrice();
            $priceDefault = $this->countSubscribePrice($priceSubscribe, $percent, true);
            $priceDiff += $priceDefault - $priceSubscribe;
        }

        return (float)$priceDiff;
    }


    /**
     * Считает цену по подписке
     *
     * @param $price
     * @param $percent
     * @param bool $reverse
     * @return int
     */
    public function countSubscribePrice($price, $percent, $reverse = false): float
    {
        // такое мудрёное округление цены нужно для того,
        // чтобы после перерасчёта корзины манзаной не было расхождения
        // т.к. там цена округялется через PriceHelper::roundPrice
        if ($reverse) {
            $price = (PriceHelper::roundPrice($price) * 100) / (100 - $percent);
        } else {
            $price = PriceHelper::roundPrice($price) * ((100 - $percent) / 100);
        }

        return $price = PriceHelper::roundPrice($price);;
    }

}