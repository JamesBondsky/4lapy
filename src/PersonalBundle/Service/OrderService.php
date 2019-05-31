<?php

namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\Services\Table as SaleDeliveryServiceTable;
use Bitrix\Sale\Internals\OrderPropsValueTable;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\Order as BitrixOrder;
use Bitrix\Sale\PropertyValue;
use Dadata\Response\Date;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Model\Cheque;
use FourPaws\External\Manzana\Model\ChequeItem;
use FourPaws\External\ManzanaService;
use FourPaws\MobileApiBundle\Dto\Object\OrderHistory;
use FourPaws\MobileApiBundle\Dto\Object\OrderStatus;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderDelivery;
use FourPaws\PersonalBundle\Entity\OrderItem;
use FourPaws\PersonalBundle\Entity\OrderPayment;
use FourPaws\PersonalBundle\Entity\OrderProp;
use FourPaws\PersonalBundle\Entity\OrderStatusChange;
use FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Exception\ManzanaCheque\ChequeItemArticleEmptyException;
use FourPaws\PersonalBundle\Exception\ManzanaCheque\ChequeItemNotActiveException;
use FourPaws\PersonalBundle\Exception\ManzanaCheque\ChequeItemNotExistsException;
use FourPaws\PersonalBundle\Exception\ManzanaCheque\ManzanaChequeItemExceptionInterface;
use FourPaws\PersonalBundle\Exception\ManzanaCheque\NoItemsInChequeException;
use FourPaws\PersonalBundle\Exception\ManzanaOrder\ManzanaNumberNotDefinedException;
use FourPaws\PersonalBundle\Exception\ManzanaOrder\ManzanaOrderExceptionInterface;
use FourPaws\PersonalBundle\Exception\ManzanaOrder\NoItemsException;
use FourPaws\PersonalBundle\Exception\ManzanaOrder\OrderAlreadyExistsException;
use FourPaws\PersonalBundle\Exception\ManzanaOrder\OrderCreateException;
use FourPaws\PersonalBundle\Repository\OrderRepository;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\EventController\Event;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Repository\OrderStatusRepository;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class OrderService
 *
 * @package FourPaws\PersonalBundle\Service
 */
class OrderService
{
    public const ORDER_PAGE_LIMIT = 10;

    public const STATUS_FINAL = [
        'G', // Заказ выполнен
        'J', // Оплачен(Доставлен)
    ];

    public const STATUS_CANCEL = [
        'A', // Отменен
        'K', // Отменен
    ];

    protected const MANZANA_FINAL_STATUS = 'G';

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var ManzanaService
     */
    protected $manzanaService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var Offer[]
     */
    protected $manzanaOrderOffers;

    /**
     * @var OrderStatusRepository
     */
    protected $orderStatusRepository;

    /**
     * OrderService constructor.
     *
     * @param OrderRepository $orderRepository
     * @param DeliveryService $deliveryService
     * @param ManzanaService $manzanaService
     * @param OrderStatusRepository $orderStatusRepository
     */
    public function __construct(
        OrderRepository $orderRepository,
        DeliveryService $deliveryService,
        ManzanaService $manzanaService,
        OrderStatusRepository $orderStatusRepository
    )
    {
        $this->orderRepository = $orderRepository;
        $this->manzanaService = $manzanaService;
        $this->deliveryService = $deliveryService;
        $this->orderStatusRepository = $orderStatusRepository;
    }

    /**
     * @param User $user
     * @param int  $page
     * @param int  $limit
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentTypeException
     * @throws ConstraintDefinitionException
     * @throws DeliveryNotFoundException
     * @throws EmptyEntityClass
     * @throws IblockNotFoundException
     * @throws ManzanaServiceContactSearchMoreOneException
     * @throws ManzanaServiceContactSearchNullException
     * @throws ManzanaServiceException
     * @throws NotAuthorizedException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \Exception
     *
     * @deprecated use \FourPaws\PersonalBundle\Service\OrderService::importOrdersFromManzana() instead
     */
    public function loadManzanaOrders(User $user, int $page = 1, int $limit = 20): void
    {
        $contactId = $this->manzanaService->getContactByUser($user)->contactId;
        $deliveryId = $this->deliveryService->getDeliveryIdByCode(DeliveryService::INNER_PICKUP_CODE);
        $offset = ($page - 1) * $limit;

        $allCheques = $this->manzanaService->getCheques($contactId);
        if ($cheques = \array_slice($allCheques, $offset, $limit)) {
            $existingManzanaOrders = $this->getSiteManzanaOrders($user->getId());

            /** @var Cheque $cheque */
            foreach ($cheques as $cheque) {
                if ($cheque->operationTypeCode === Cheque::OPERATION_TYPE_RETURN) {
                    continue;
                }

                if (\in_array($cheque->chequeNumber, $existingManzanaOrders, true)) {
                    continue;
                }

                if (!$cheque->hasItemsBool()) {
                    continue;
                }

                /** @var \DateTimeImmutable $date */
                $date = $cheque->date;
                $bitrixDate = DateTime::createFromTimestamp($date->getTimestamp());
                $order = (new Order())
                    ->setDateInsert($bitrixDate)
                    ->setDatePayed($bitrixDate)
                    ->setDateStatus($bitrixDate)
                    ->setDateUpdate($bitrixDate)
                    ->setManzana(true)
                    ->setUserId($user->getId())
                    ->setPayed(true)
                    ->setStatusId(static::MANZANA_FINAL_STATUS)
                    ->setPrice($cheque->sum)
                    ->setItemsSum($cheque->sum)
                    ->setManzanaId($cheque->chequeNumber)
                    ->setPaySystemId(PaySystemActionTable::query()->setFilter(['CODE' => 'cash'])
                                                         ->setSelect(['PAY_SYSTEM_ID'])->exec()
                                                         ->fetch()['PAY_SYSTEM_ID'])
                    ->setDeliveryId($deliveryId);

                try {
                    $items = $this->getItemsByCheque($cheque);
                } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ManzanaChequeItemExceptionInterface $e) {
                    continue;
                }
                $order->setItems(new ArrayCollection($items));

                try {
                    $this->addManzanaOrder($order);
                } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ManzanaOrderExceptionInterface $e) {
                }
            }
        }

        $user->setManzanaImportDateTime(new DateTime());
        App::getInstance()->getContainer()->get(UserRepository::class)->update($user);
    }

	/**
	 * @param User $user
	 * @throws \Exception
	 */
    public function importOrdersFromManzana(User $user): void
    {
        $contactId = $this->manzanaService->getContactByUser($user)->contactId;
        $deliveryId = $this->deliveryService->getDeliveryIdByCode(DeliveryService::INNER_PICKUP_CODE);

        $cheques = $this->manzanaService->getCheques($contactId);

        $existingManzanaOrders = $this->getSiteManzanaOrders($user->getId());

        $oldOrderNumbers = [];
        /** @var Cheque $cheque */
        foreach ($cheques as $cheque)
        {
            if (substr($cheque->chequeNumber, -3) === 'NEW')
            {
                $oldOrderNumbers[$cheque->chequeNumber] = substr($cheque->chequeNumber, 0, -3);
            }
        }

        $oldOrders = OrderTable::getList([
            'select' => [
                'ID',
                'ACCOUNT_NUMBER',
                'MANZANA_NUMBER' => 'PROPERTY.VALUE',
            ],
            'filter' => [
                'ACCOUNT_NUMBER' => $oldOrderNumbers,
            ],
            'runtime' => [
                'PROPERTY' => [
                    'data_type' => OrderPropsValueTable::class,
                    'reference' => [
                        '=this.ID' => 'ref.ORDER_ID',
                        '=ref.CODE' => new SqlExpression('?', 'MANZANA_NUMBER'),
                    ],
                    'join_type' => 'left',
                ],
            ],
        ])->fetchAll();

        $oldOrdersIds = [];
        foreach ($oldOrders as $order)
        {
            $oldOrdersIds[$order['ACCOUNT_NUMBER']] = [
                'ID' => $order['ID'],
                'MANZANA_NUMBER' => $order['MANZANA_NUMBER'],
                'ACCOUNT_NUMBER' => $order['ACCOUNT_NUMBER'],

            ];
        }

        /** @var Cheque $cheque */
        foreach ($cheques as $cheque) {
            if ($cheque->operationTypeCode === Cheque::OPERATION_TYPE_RETURN) {
                continue;
            }

            /**
             * Прекращение обработки, если заказ уже был импортирован из Manzana (и старый вариант, когда
             * создавался дубликат заказа, и новый вариант, когда номер чека указывается в исходном заказе)
             */
            if (\in_array($cheque->chequeNumber, $existingManzanaOrders, true)) {
                continue;
            }

            if (!$cheque->hasItemsBool()) {
                continue;
            }

            try {
                $items = $this->getItemsByCheque($cheque);
            } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ManzanaChequeItemExceptionInterface $e) {
                continue;
            }

            /**
             * Если исходный заказ есть на сайте, то обновление этого заказа данными из Manzana. В исходном заказе
             * проставляется ID чека из Manzana и при необходимости обновляется статус и состав товаров
             */
            $oldOrder = $oldOrdersIds[$oldOrderNumbers[$cheque->chequeNumber]];
            if ($oldOrder['ID'] && !$oldOrder['MANZANA_NUMBER']) {
                try {
                    $this->updateOrderFromManzana($oldOrder['ID'], $cheque, $items);
                } catch (\Exception $e) {
                    LoggerFactory::create('manzanaOrder')->error(sprintf('failed to update order. Order id: %s. %s', $oldOrder['ID'], $e->getMessage()));
                }
                continue;
            }


            /** @var \DateTimeImmutable $date */
            $date = $cheque->date;
            $bitrixDate = DateTime::createFromTimestamp($date->getTimestamp());
            $currentDate = new DateTime();
            $order = (new Order())
                ->setDateInsert($bitrixDate)
                ->setDatePayed($bitrixDate)
                ->setDateStatus($bitrixDate)
                ->setDateUpdate($currentDate)
                ->setManzana(true)
                ->setUserId($user->getId())
                ->setPayed(true)
                ->setStatusId(static::MANZANA_FINAL_STATUS)
                ->setPrice($cheque->sum)
                ->setItemsSum($cheque->sum)
                ->setManzanaId($cheque->chequeNumber)
                ->setPaySystemId(PaySystemActionTable::query()->setFilter(['CODE' => 'cash'])
                    ->setSelect(['PAY_SYSTEM_ID'])->exec()
                    ->fetch()['PAY_SYSTEM_ID'])
                ->setDeliveryId($deliveryId);

            $order->setItems(new ArrayCollection($items));

            try {
                $this->addManzanaOrder($order);
            } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ManzanaOrderExceptionInterface $e) {
            }
        }

        $user->setManzanaImportDateTime(new DateTime());

        App::getInstance()->getContainer()->get(UserRepository::class)->update($user);
    }

    /**
     * @param User $user
     * @param int  $page
     * @param int  $limit
     *
     * @return ArrayCollection
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws SystemException
     */
    public function getUserOrders(User $user, int $page = 1, int $limit = self::ORDER_PAGE_LIMIT): ArrayCollection
    {
        if ($page < 1) {
            throw new InvalidArgumentException('Page must be >= 1');
        }

        if ($limit < 0) {
            throw new InvalidArgumentException('Limit must be a positive value');
        }

        $offset = ($page - 1) * $limit;

        return $this->orderRepository->getUserOrders($user->getId(), $limit, $offset);
    }

    /**
     * @param User $user
     * @param int $orderId
     * @return Order
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getUserOrderById(User $user, int $orderId)
    {
        return $this->orderRepository->getUserOrderById($user->getId(), $orderId);
    }

    /**
     * @param User $user
     * @param int $orderNumber
     * @return Order
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getUserOrderByNumber(User $user, int $orderNumber)
    {
        return $this->orderRepository->getUserOrderByNumber($user->getId(), $orderNumber);
    }

    /**
     * @param User $user
     *
     * @return int
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getUserOrdersCount(User $user): int
    {
        return $this->orderRepository->getUserOrdersCount($user->getId());
    }

    /**
     * @deprecated
     *
     * @param array $params
     *
     * @return ArrayCollection
     * @throws ArgumentException
     */
    public function getUserOrdersOld(array $params): ArrayCollection
    {
        return $this->orderRepository->getUserOrdersOld($params);
    }

    /**
     * @param int $orderId
     *
     * @return array
     * @throws \Exception
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws SystemException
     * @throws EmptyEntityClass
     */
    public function getOrderItems(int $orderId): array
    {
        return $this->orderRepository->getOrderItems($orderId);
    }

    /**
     * @param int $paySystemId
     *
     * @return OrderPayment
     * @throws EmptyEntityClass
     */
    public function getPayment(int $paySystemId): OrderPayment
    {
        return $this->orderRepository->getPayment($paySystemId);
    }

    /**
     * @param int $orderId
     *
     * @return OrderDelivery
     * @throws EmptyEntityClass
     */
    public function getDelivery(int $orderId): OrderDelivery
    {
        return $this->orderRepository->getDelivery($orderId);
    }

    /**
     * @param int $orderId
     *
     * @return ArrayCollection|OrderProp[]
     * @throws EmptyEntityClass
     */
    public function getOrderProps(int $orderId): ArrayCollection
    {
        return $this->orderRepository->getOrderProps($orderId);
    }

    /**
     * @param Order $order
     *
     * @return Store|null
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws EmptyEntityClass
     * @throws NotImplementedException
     * @throws ObjectPropertyException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws BitrixOrderNotFoundException
     * @throws NotFoundException
     */
    public function getStore(Order $order): ?Store
    {
        /** @var OrderProp $prop */
        $props = $order->getProps();
        if (!$props->isEmpty()) {
            $deliveryCode  = '';

            try {
                /** получение и проверка доставки */
                $deliveryCode = $order->getOrderService()->getOrderDeliveryCode($order->getBitrixOrder());
            } catch (NotFoundException $e) {
            }
            /** если самовывоз */
            if (\in_array($deliveryCode, DeliveryService::PICKUP_CODES, true)) {
                $dpdTerminal = $props->get('DPD_TERMINAL_CODE');
                $cityCode = $props->get('CITY_CODE');
                if ($cityCode instanceof OrderProp && $dpdTerminal instanceof OrderProp && $dpdTerminal->getValue() && $cityCode->getValue()) {
                    try {
                        /** @var DeliveryService $deliveryService */
                        $deliveryService = App::getInstance()->getContainer()->get('delivery.service');

                        $terminals = $deliveryService->getDpdTerminalsByLocation($cityCode->getValue());
                        $store = $terminals[$dpdTerminal->getValue()];

                        if ($store !== null && !$store->isActive()) {
                            $store->setActive(true);
                        }

                        return $store;
                    } catch (\Exception $exception) {
                        return null;
                    }
                }
                $deliveryPlace = $props->get('DELIVERY_PLACE_CODE');
                if ($deliveryPlace instanceof OrderProp && $deliveryPlace->getValue()) {
                    try {
                        /** @var StoreService $storeService */
                        $storeService = App::getInstance()->getContainer()->get('store.service');

                        $store = $storeService->getStoreByXmlId($deliveryPlace->getValue());
                        if (!$store->isActive()) {
                            $store->setActive(true);
                        }

                        return $store;
                    } catch (\Exception $exception) {
                        return null;
                    }
                }
            } elseif (\in_array($deliveryCode, DeliveryService::DELIVERY_CODES, true)) {
                /** если не самовывоз значит доставка */

                $store = new Store();
                $address = [];
                $street = trim($order->getPropValue('STREET'));
                if (!empty($street)) {
                    $address[] = $street;
                }
                $house = trim($order->getPropValue('HOUSE'));
                $house = $house ? 'д.' . $house : '';
                if (!empty($house)) {
                    $address[] = $house;
                }
                $building = trim($order->getPropValue('BUILDING'));
                $building = !empty($building) ? 'корпус/строение ' . $building : '';
                if (!empty($building)) {
                    $address[] = $building;
                }
                $porch = trim($order->getPropValue('PORCH'));
                $porch = !empty($porch) ? 'подъезд. ' . $porch : '';
                if (!empty($porch)) {
                    $address[] = $porch;
                }
                $apartment = trim($order->getPropValue('APARTMENT'));
                $apartment = !empty($apartment) ? 'кв. ' . $apartment : '';
                if (!empty($apartment)) {
                    $address[] = $apartment;
                }
                $floor = trim($order->getPropValue('FLOOR'));
                $floor = !empty($floor) ? 'этаж ' . $floor : '';
                if (!empty($floor)) {
                    $address[] = $floor;
                }
                $city = trim($order->getPropValue('CITY'));
                $city = !empty($city) ? 'г. ' . $city : '';
                if (!empty($city)) {
                    $address[] = $city;
                }
                if (!empty($address)) {
                    $store->setAddress(trim(implode(', ', $address)));
                    $store->setActive(true);
                    $store->setIsShop(false);
                } else {
                    return null;
                }

                return $store;
            }
        }

        return null;
    }

    /**
     * @param int $orderId
     *
     * @return Order|null
     * @throws \Exception
     */
    public function getOrderById(int $orderId)
    {
        $params = [
            'filter' => [
                'ID' => $orderId,
            ],
        ];
        $collection = $this->orderRepository->findBy($params);

        return $collection->count() ? $collection->first() : null;
    }

    /**
     * @param int $orderNumber
     *
     * @return Order|null
     * @throws \Exception
     */
    public function getOrderByNumber(int $orderNumber)
    {
        $params = [
            'filter' => [
                'ACCOUNT_NUMBER' => $orderNumber,
            ],
        ];
        $collection = $this->orderRepository->findBy($params);

        return $collection->count() ? $collection->first() : null;
    }

    /**
     * @param User $user
     * @return bool
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws SystemException
     */
    public function isUserHasOrdersFromApp(User $user): bool
    {
        $ordersFromApp = $this->getUserOrders($user, 1, 0)->filter(function(Order $order) {
            return $order->isFromApp();
        });
        return $ordersFromApp->count() > 0;
    }

    /**
     * @param Order $order
     *
     * @return bool
     * @throws ArgumentException
     * @throws SystemException
     * @throws ObjectPropertyException
     */
    protected function isManzanaOrderExists(Order $order): bool
    {
        $filter = [
            'USER_ID'        => $order->getUserId(),
            'PROPERTY.CODE'  => 'MANZANA_NUMBER',
            'PROPERTY.VALUE' => $order->getManzanaId(),
        ];

        return (bool)OrderTable::query()->setFilter($filter)->exec()->fetch();
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws SystemException
     * @throws ObjectPropertyException
     */
    protected function getSiteManzanaOrders($userId): array
    {
        $result = [];
        $items = OrderTable::query()->setFilter([
            'USER_ID'         => $userId,
            'PROPERTY.CODE'   => 'MANZANA_NUMBER',
            '!PROPERTY.VALUE' => [
                null,
                '',
            ],
        ])->setSelect([
            'ID',
            'PROPERTY_CODE'  => 'PROPERTY.CODE',
            'PROPERTY_VALUE' => 'PROPERTY.VALUE',
        ])->exec();
        while ($item = $items->fetch()) {
            if ($item['PROPERTY_CODE'] === 'MANZANA_NUMBER') {
                $result[$item['ID']] = $item['PROPERTY_VALUE'];
            }
        }

        return $result;
    }

    /**
     * @param Order $order
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws EmptyEntityClass
     * @throws IblockNotFoundException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderCreateException
     * @throws SystemException
     * @throws ArgumentTypeException
     * @throws NotSupportedException
     * @throws \Exception
     */
    protected function addManzanaOrder(Order $order): bool
    {
        if (!$order->getManzanaId()) {
            throw new ManzanaNumberNotDefinedException('Order manzana id not defined');
        }

        if ($order->getItems()->isEmpty()) {
            throw new NoItemsException(\sprintf('No items in order %s', $order->getManzanaId()));
        }

        if ($this->isManzanaOrderExists($order)) {
            throw new OrderAlreadyExistsException(\sprintf('Order %s already exists', $order->getManzanaId()));
        }

        Manager::disableExtendsDiscount();

        $bitrixOrder = BitrixOrder::create(SITE_ID, $order->getUserId(), $order->getCurrency());
        $bitrixOrder->setFieldNoDemand('STATUS_ID', $order->getStatusId());
        $bitrixOrder->setFieldNoDemand('PAYED', BitrixUtils::BX_BOOL_TRUE);
        $bitrixOrder->setFieldNoDemand('DATE_PAYED', $order->getDateInsert());
        $bitrixOrder->setFieldNoDemand('DATE_STATUS', $order->getDateInsert());

        $items = $order->getItems();
        /** @var Basket $orderBasket */
        $orderBasket = $this->createBasketFromManzana($items);
        $bitrixOrder->setBasket($orderBasket);

        $allBonuses = $this->getItemsBonuses($items);

        $userCityService = App::getInstance()->getContainer()->get(UserCitySelectInterface::class);
        $selectedCity = $userCityService->getSelectedCity();
        /** @var PropertyValue $propertyValue */
        foreach ($bitrixOrder->getPropertyCollection() as $propertyValue) {
            switch ($propertyValue->getProperty()['CODE']) {
                case 'MANZANA_NUMBER':
                    $propertyValue->setValue($order->getManzanaId());
                    break;
                case 'IS_NEW_SITE_ORDER':
                    $propertyValue->setValue(BitrixUtils::BX_BOOL_FALSE);
                    break;
                case 'IS_MANZANA_ORDER':
                case 'USER_REGISTERED':
                case 'IS_EXPORTED':
                    $propertyValue->setValue(BitrixUtils::BX_BOOL_TRUE);
                    break;
                case 'BONUS_COUNT':
                    $propertyValue->setValue($allBonuses);
                    break;
                case 'SHIPMENT_PLACE_CODE':
                    $propertyValue->setValue('DC01');
                    break;
                case 'CITY_CODE':
                    $propertyValue->setValue($selectedCity['CODE']);
                    break;
                case 'CITY':
                    $propertyValue->setValue($selectedCity['NAME']);
                    break;
            }
        }

        /** доставка */
        $shipmentCollection = $bitrixOrder->getShipmentCollection();
        $shipment = $shipmentCollection->createItem();
        $shipmentItemCollection = $shipment->getShipmentItemCollection();
        $selectedDelivery = SaleDeliveryServiceTable::query()->setSelect([
            'ID',
            'NAME',
        ])->setFilter(['ID' => $order->getDeliveryId()])->setCacheTtl(360000)->exec()->fetch();
        try {
            /** @var BasketItem $item */
            foreach ($orderBasket as $item) {
                $shipmentItem = $shipmentItemCollection->createItem($item);
                $shipmentItem->setQuantity($item->getQuantity());
            }

            $shipment->setFields(
                [
                    'DELIVERY_ID'           => $selectedDelivery['ID'],
                    'DELIVERY_NAME'         => $selectedDelivery['NAME'],
                    'CURRENCY'              => $bitrixOrder->getCurrency(),
                    'PRICE_DELIVERY'        => 0,
                    'CUSTOM_PRICE_DELIVERY' => 'N',
                ]
            );
        } catch (\Exception $e) {
            LoggerFactory::create('manzanaOrder')->error(sprintf('failed to set shipment fields: %s', $e->getMessage()),
                [
                    'deliveryId' => $selectedDelivery['ID'],
                ]);
            throw new OrderCreateException('Ошибка при создании отгрузки');
        }
        $shipmentCollection->calculateDelivery();

        /** оплата */
        $paymentCollection = $bitrixOrder->getPaymentCollection();

        try {
            $extPayment = $paymentCollection->createItem();
            $extPayment->setField('SUM', $bitrixOrder->getPrice());
            $extPayment->setField('PAY_SYSTEM_ID', $order->getPaySystemId());
            $extPayment->setPaid('Y');
            $extPayment->setField('DATE_PAID', $order->getDateInsert());
            $extPayment->setField('DATE_BILL', $order->getDateInsert());
            /** @var \Bitrix\Sale\PaySystem\Service $paySystem */
            $paySystem = $extPayment->getPaySystem();
            $extPayment->setField('PAY_SYSTEM_NAME', $paySystem->getField('NAME'));
        } catch (\Exception $e) {
            LoggerFactory::create('manzanaOrder')->error(sprintf('order payment failed: %s', $e->getMessage()), [
                'userId'    => $bitrixOrder->getUserId(),
                'manzanaId' => $order->getManzanaId(),
            ]);
            throw new OrderCreateException('Order payment failed');
        }

        $result = $bitrixOrder->save();
        /** костыль для обновления дат */
        OrderTable::update($result->getId(),
            [
                'DATE_INSERT' => $order->getDateInsert(),
                'DATE_UPDATE' => $order->getDateUpdate(),
            ]
        );
        Manager::enableExtendsDiscount();

        return $result->isSuccess();
    }

    /**
     * @param int $orderId
     * @param Cheque $cheque
     * @param array|OrderItem[] $manzanaBasketItems
     *
     * @throws \Exception
     */
    protected function updateOrderFromManzana(int $orderId, Cheque $cheque, $manzanaBasketItems): void
    {
        if ($orderId <= 0)
        {
            throw new BitrixOrderNotFoundException(\sprintf('Order %s not found', $orderId));
        }
        Event::disableEvents();
        $order = BitrixOrder::load($orderId);
        if ($order)
        {
            $baseOrderStatus = $order->getField('PAYED');

            $propertyCollection = $order->getPropertyCollection();
            $orderProperty = \FourPaws\Helpers\BxCollection::getOrderPropertyByCode($propertyCollection, 'MANZANA_NUMBER');
            if ($orderProperty) {
                $orderProperty->setValue($cheque->chequeNumber);
            }
            else
            {
                LoggerFactory::create('manzanaOrder')->error(sprintf('failed to set MANZANA_NUMBER for order %s', $order->getField('ACCOUNT_NUMBER')));
            }
            $order->setFieldNoDemand('STATUS_ID', static::MANZANA_FINAL_STATUS);
            $order->setFieldNoDemand('DATE_UPDATE', new DateTime());

            if ($baseOrderStatus !== BitrixUtils::BX_BOOL_TRUE) {
                $order->setFieldNoDemand('PAYED', BitrixUtils::BX_BOOL_TRUE);

                $basket = $order->getBasket();
                $basketItems = $basket->getBasketItems();
                $basketItemsMainParams = [];
                /** @var BasketItem $item */
                foreach ($basketItems as $item)
                {
                    $itemExternalId = end(explode('#', $item->getField('PRODUCT_XML_ID')));

                    $basketItemsMainParams[$itemExternalId]['quantity'] += $item->getQuantity();
                    $basketItemsMainParams[$itemExternalId]['price'] = (int)$item->getPrice();
                }

                $isChangeNeeded = false;
                // Проверяется не полное соответствие чека исходному заказу, а достаточное условие для необходимости обновить заказ
                if (
                    $cheque->sumDiscounted != $basket->getPrice() ||
                    count($manzanaBasketItems) !== count($basketItems)
                ) {
                    $isChangeNeeded = true;
                } else {
                    /** @var OrderItem $item */
                    foreach ($manzanaBasketItems as $item)
                    {
                        $article = $item->getArticle();
                        if (
                            !array_key_exists($article, $basketItemsMainParams) ||
                            $basketItemsMainParams[$article]['quantity'] != $item->getQuantity() ||
                            $basketItemsMainParams[$article]['price'] != $item->getPrice()
                        )
                        {
                            $isChangeNeeded = true;
                            break;
                        }
                    }
                }

                if ($isChangeNeeded)
                {
                    LoggerFactory::create('manzanaOrder')->info(sprintf('site order\'s basket is changed from Manzana, order id: %s', $orderId));

                    $manzanaBasketItemsCollection = new ArrayCollection($manzanaBasketItems);

                    Manager::disableExtendsDiscount();
                    $basket->clearCollection();
                    $this->addManzanaItemsToBasket($basket, $manzanaBasketItemsCollection);
                    Manager::enableExtendsDiscount();

                    $orderProperty = \FourPaws\Helpers\BxCollection::getOrderPropertyByCode($propertyCollection, 'BONUS_COUNT');
                    if ($orderProperty) {
                        $orderProperty->setValue($this->getItemsBonuses($manzanaBasketItemsCollection));
                    }
                    else
                    {
                        LoggerFactory::create('manzanaOrder')->error(sprintf('failed to set BONUS_COUNT for order %s', $order->getField('ACCOUNT_NUMBER')));
                    }
                }
            }
            $order->save();
        }
        Event::enableEvents();
    }

    /**
     * @param ArrayCollection $items
     *
     * @return Basket
     *
     * @throws IblockNotFoundException
     */
    protected function createBasketFromManzana(ArrayCollection $items): Basket
    {
        /** @var Basket $orderBasket */
        $orderBasket = Basket::create(SITE_ID);
        $this->addManzanaItemsToBasket($orderBasket, $items);
        return $orderBasket;
    }

    /**
     * @param Basket $basket
     * @param ArrayCollection $items
     *
     * @return void
     *
     * @throws IblockNotFoundException
     */
    protected function addManzanaItemsToBasket(Basket $basket, ArrayCollection $items): void
    {
        $offerIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);

        /** @var OrderItem $item */
        foreach ($items as $item) {
            $productId = $item->getProductId();
            $basketItem = $basket->createItem('catalog', $productId);
            $basketItem->setFields([
                'PRICE'                  => $item->getPrice(),
                'BASE_PRICE'             => $item->getBasePrice(),
                'CUSTOM_PRICE'           => BitrixUtils::BX_BOOL_TRUE,
                'QUANTITY'               => $item->getQuantity(),
                'CURRENCY'               => CurrencyManager::getBaseCurrency(),
                'NAME'                   => $item->getName(),
                'WEIGHT'                 => $item->getWeight(),
                'DETAIL_PAGE_URL'        => $item->getDetailPageUrl(),
                'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
                'CATALOG_XML_ID'         => $offerIblockId,
                'PRODUCT_XML_ID'         => $item->getArticle(),
            ]);
        }
    }

    /**
     * @param ArrayCollection $items
     *
     * @return float|int
     */
    protected function getItemsBonuses(ArrayCollection $items)
    {
        $allBonuses = 0;
        /** @var OrderItem $item */
        foreach ($items as $item) {
            $allBonuses += $item->getBonus();
        }

        return $allBonuses;
    }

    /**
     * @param Cheque $cheque
     *
     * @return OrderItem[]
     * @throws ManzanaServiceException
     * @throws NoItemsInChequeException
     * @throws ChequeItemArticleEmptyException
     * @throws ChequeItemNotExistsException
     * @throws ChequeItemNotActiveException
     */
    protected function getItemsByCheque(Cheque $cheque): array
    {
        if (!$chequeItems = $this->manzanaService->getItemsByCheque($cheque->chequeId)) {
            throw new NoItemsInChequeException(\sprintf('Cheque %s has no items', $cheque->chequeNumber));
        }
        $this->loadOffersByCheque($cheque, $chequeItems);

        $result = [];

        foreach ($chequeItems as $chequeItem) {
            $offer = $this->manzanaOrderOffers[$chequeItem->number];
            if (null === $offer) {
                throw new ChequeItemNotExistsException(
                    \sprintf('Cheque %s item %s not found', $cheque->chequeNumber, $chequeItem->number)
                );
            }

            if (!$offer->isActive()) {
                throw new ChequeItemNotActiveException(
                    \sprintf('Catalog offer %s (#%s) is not active', $offer->getXmlId(), $offer->getId())
                );
            }

            $item = new OrderItem();
            $item
                ->setArticle($chequeItem->number)
                ->setBonus($chequeItem->bonus)
                ->setPrice($chequeItem->price - ($chequeItem->price * $chequeItem->discount / 100))
                ->setBasePrice($chequeItem->price)
                ->setQuantity($chequeItem->quantity)
                ->setSum($chequeItem->sumDiscounted)
                ->setName($chequeItem->name)
                ->setHaveStock(false)
                ->setWeight($offer->getCatalogProduct()->getWeight())
                ->setDetailPageUrl($offer->getLink())
                ->setProductId($offer->getId());
            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param Cheque       $cheque
     * @param ChequeItem[] $chequeItems
     *
     * @return Offer[]
     * @throws ChequeItemArticleEmptyException
     */
    protected function loadOffersByCheque(Cheque $cheque, array $chequeItems): array
    {
        $result = [];
        $xmlIds = [];
        foreach ($chequeItems as $i => $chequeItem) {
            if (!$xmlId = $chequeItem->number) {
                throw new ChequeItemArticleEmptyException(
                    \sprintf('Cheque %s item #%s has no article', $cheque->chequeNumber, $i)
                );
            }

            if ($this->manzanaOrderOffers[$xmlId]) {
                $result[] = $this->manzanaOrderOffers[$xmlId];
            } else {
                $xmlIds[] = $xmlId;
            }
        }

        if (!empty($xmlIds)) {
            $offers = (new OfferQuery())->withFilter(['XML_ID' => $xmlIds])->exec();
            /** @var Offer $offer */
            foreach ($offers as $offer) {
                $this->manzanaOrderOffers[$offer->getXmlId()] = $offer;
                $result[] = $offer;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getClosedOrderStatuses(): array
    {
        return array_merge(self::STATUS_CANCEL, self::STATUS_FINAL);
    }

    /**
     * @return string
     */
    public function getClosedOrderStatusesForQuery(): string
    {
        return \implode(',', \array_map(function ($status) {
            return sprintf('"%s"', $status);
        }, \array_merge(self::STATUS_CANCEL, self::STATUS_FINAL)));
    }

    /**
     * @param Order $order
     * @return ArrayCollection
     * @throws ObjectException
     */
    public function getOrderStatuses(Order $order): ArrayCollection
    {
        $statuses = [
            (new OrderStatusChange())
                ->setOrderStatus($this->orderStatusRepository->findById('N'))
                ->setDateCreate($order->getDateInsert())
        ];

        $rs = (new \CSaleOrderChange())->GetList(
            ['DATE_CREATE' => 'ASC'],
            [
                'ORDER_ID' => $order->getId(),
                'TYPE' => 'ORDER_STATUS_CHANGED'
            ],
            false,
            false,
            ['DATE_CREATE', 'DATA']
        );
        while($orderChange = $rs->Fetch()) {
            $orderChangeData = unserialize($orderChange['DATA']);
            $statuses[] = (new OrderStatusChange())
                ->setOrderStatus($this->orderStatusRepository->findById($orderChangeData['STATUS_ID']))
                ->setDateCreate(new DateTime($orderChange['DATE_CREATE']))
            ;
        }

        return new ArrayCollection($statuses);
    }
}
