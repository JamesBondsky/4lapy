<?php

namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
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
use Bitrix\Sale\Internals\OrderPropsTable;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\Order as BitrixOrder;
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
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Entity\OrderDelivery;
use FourPaws\PersonalBundle\Entity\OrderItem;
use FourPaws\PersonalBundle\Entity\OrderPayment;
use FourPaws\PersonalBundle\Entity\OrderProp;
use FourPaws\PersonalBundle\Exception\ChequeItemArticleEmptyException;
use FourPaws\PersonalBundle\Exception\ChequeItemNotExistsException;
use FourPaws\PersonalBundle\Exception\NoItemsInChequeException;
use FourPaws\PersonalBundle\Repository\OrderRepository;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
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
    public static $finalStatuses = [
        'G',
        'J',
    ];

    public static $cancelStatuses = [
        'A',
        'K',
    ];

    protected static $manzanaFinalStatus = 'G';

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var CurrentUserProviderInterface $currentUser
     */
    protected $currentUser;

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
     * OrderService constructor.
     *
     * @param OrderRepository              $orderRepository
     * @param DeliveryService              $deliveryService
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param ManzanaService               $manzanaService
     */
    public function __construct(
        OrderRepository $orderRepository,
        DeliveryService $deliveryService,
        CurrentUserProviderInterface $currentUserProvider,
        ManzanaService $manzanaService
    )
    {
        $this->orderRepository = $orderRepository;
        $this->currentUser = $currentUserProvider;
        $this->manzanaService = $manzanaService;
        $this->deliveryService = $deliveryService;
    }

    /**
     * @return ArrayCollection|Order[]
     * @throws \RuntimeException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @throws \Exception
     */
    public function getAllClosedOrders(): ArrayCollection
    {
        $closedSiteOrders = $this->getClosedSiteOrders()->toArray();
        try {
            $manzanaOrders = $this->getManzanaOrders()->toArray();
        } catch (ManzanaServiceException $e) {
            $manzanaOrders = [];
        }

        return $this->mergeAllClosedOrders($closedSiteOrders, $manzanaOrders);
    }

    /**
     * @param array $closedSiteOrders
     * @param array $manzanaOrders
     *
     * @return ArrayCollection|Order[]
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function mergeAllClosedOrders(array $closedSiteOrders, array $manzanaOrders): ArrayCollection
    {
        /** Очищаем дубли из манзаны */
        /** @var Order $manzanaOrder */
        foreach ($manzanaOrders as $key => $manzanaOrder) {
            if (\in_array($manzanaOrder->getManzanaId(), $this->getSiteManzanaOrders($manzanaOrder->getUserId()), true)) {
                unset($manzanaOrders[$key]);
            }
        }

        return new ArrayCollection(array_merge($closedSiteOrders, $manzanaOrders));
    }

    /**
     * @return ArrayCollection
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentTypeException
     * @throws DeliveryNotFoundException
     * @throws EmptyEntityClass
     * @throws IblockNotFoundException
     * @throws ManzanaServiceContactSearchMoreOneException
     * @throws ManzanaServiceContactSearchNullException
     * @throws ManzanaServiceException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderCreateException
     * @throws SystemException
     * @throws \Exception
     */
    public function getManzanaOrders(): ArrayCollection
    {
        $orders = new ArrayCollection();
        $cheques = new ArrayCollection($this->manzanaService->getCheques($this->manzanaService->getContactIdByUser()));
        if (!$cheques->isEmpty()) {
            $hasAdd = false;
            $deliveryId = $this->deliveryService->getDeliveryIdByCode(DeliveryService::INNER_PICKUP_CODE);
            /** @var Cheque $cheque */
            foreach ($cheques as $cheque) {
                /** @var \DateTimeImmutable $date */
                $date = $cheque->date;
                $bitrixDate = DateTime::createFromTimestamp($date->getTimestamp());
                $order = (new Order())
                    ->setDateInsert($bitrixDate)
                    ->setDatePayed($bitrixDate)
                    ->setDateStatus($bitrixDate)
                    ->setDateUpdate($bitrixDate)
                    ->setManzana(true)
                    ->setUserId($this->currentUser->getCurrentUserId())
                    ->setPayed(true)
                    ->setStatusId(static::$manzanaFinalStatus)
                    ->setPrice($cheque->sum)
                    ->setItemsSum($cheque->sum)
                    ->setManzanaId('' . $cheque->chequeNumber)
                    ->setPaySystemId(PaySystemActionTable::query()->setFilter(['CODE' => 'cash'])->setSelect(['PAY_SYSTEM_ID'])->exec()->fetch()['PAY_SYSTEM_ID'])
                    ->setDeliveryId($deliveryId);

                if (!$cheque->hasItemsBool()) {
                    continue;
                }

                if ($this->hasOrderByManzana($order)) {
                    continue;
                }

                try {
                    $items = $this->getItemsByCheque($cheque);
                } catch (NoItemsInChequeException|ChequeItemArticleEmptyException|ChequeItemNotExistsException $e) {
                    continue;
                }

                $order->setNewManzana(true);
                $order->setItems(new ArrayCollection($items));
                if (!$this->hasOrderByManzana($order)) {
                    $hasAdd = $this->addOrder($order);
                } else {
                    $orders[$order->getId()] = $order;
                }
            }

            if ($hasAdd) {
                LocalRedirect(Application::getInstance()->getContext()->getRequest()->getRequestUri());
            }
        }

        return $orders;
    }

    /**
     * @return ArrayCollection|Order[]
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws NotFoundException
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     * @throws SystemException
     * @throws ArgumentException
     * @throws IblockNotFoundException
     * @throws NotFoundException
     * @throws \Exception
     */
    public function getActiveSiteOrders(): ArrayCollection
    {
        return $this->getUserOrders([
            'filter' => [
                '!STATUS_ID' => array_merge(static::$finalStatuses, static::$cancelStatuses),
                'CANCELED'   => 'N',
            ],
            'setKey' => 'ID',
        ]);
    }

    /**
     * @param array $params
     *
     * @return ArrayCollection|Order[]
     * @throws ServiceNotFoundException
     * @throws NotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ArgumentException
     * @throws EmptyEntityClass
     * @throws IblockNotFoundException
     * @throws SystemException
     * @throws \Exception
     */
    public function getUserOrders(array $params): ArrayCollection
    {
        $orderCollection = $this->orderRepository->getUserOrders($params);
        if (!$orderCollection->isEmpty()) {
            /** @var Order $order */
            foreach ($orderCollection as $key => $order) {
                if (!$order->isManzana() && $order->getId() > 0) {
                    /** удаляем к чертям заказы без товаров */
                    if ($order->isItemsEmpty()) {
                        unset($orderCollection[$key]);
                        continue;
                    }
                }
            }
        }

        return $orderCollection;
    }

    /**
     * @return ArrayCollection
     * @throws \Exception
     */
    public function getClosedSiteOrders(): ArrayCollection
    {
        return $this->getUserOrders([
            'filter' => [
                [
                    'LOGIC'     => 'OR',
                    'STATUS_ID' => array_merge(static::$finalStatuses, static::$cancelStatuses),
                    'CANCELED'  => 'Y',
                ],
            ],
            'setKey' => 'ID',
        ]);
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
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws \Exception
     * @throws NotFoundException
     */
    public function getStore(Order $order): ?Store
    {
        /** @var OrderProp $prop */
        $props = $order->getProps();
        if (!$props->isEmpty()) {
            /** получение и проверка доставки */
            $deliveryCode = $order->getOrderService()->getOrderDeliveryCode($order->getBitrixOrder());
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
     * @param Order $order
     *
     * @return bool
     * @throws ArgumentException
     * @throws SystemException
     * @throws ObjectPropertyException
     */
    protected function hasOrderByManzana(Order $order): bool
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
    protected function addOrder(Order $order): bool
    {
        if (!$order->isManzana() || empty($order->getManzanaId()) || $order->isItemsEmpty()) {
            return false;
        }
        Manager::disableExtendsDiscount();
        $bitrixOrder = BitrixOrder::create(SITE_ID, $order->getUserId(), $order->getCurrency());

        /** ставим даты */
        $bitrixOrder->setFieldNoDemand('STATUS_ID', 'G');
        $bitrixOrder->setFieldNoDemand('DATE_INSERT', $order->getDateInsert());
        $bitrixOrder->setFieldNoDemand('DATE_UPDATE', $order->getDateInsert());
        $bitrixOrder->setFieldNoDemand('PAYED', 'Y');
        $bitrixOrder->setFieldNoDemand('DATE_PAYED', $order->getDateInsert());
        $bitrixOrder->setFieldNoDemand('DATE_STATUS', $order->getDateInsert());

        /** @var Basket $orderBasket */
        $orderBasket = Basket::create(SITE_ID);
        /** @var OrderItem $item */
        $allBonuses = 0;
        $offerIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
        $bitrixOrder->setBasket($orderBasket);
        /** @var OrderItem $item */
        foreach ($order->getItems() as $item) {
            $productId = $item->getId();
            /** @var Offer $offer */
            $basketItem = $orderBasket->createItem('catalog', $productId);
            $basketItem->setPrice($item->getPrice(), true);
            $basketItem->setFields([
                'QUANTITY'               => $item->getQuantity(),
                'CURRENCY'               => CurrencyManager::getBaseCurrency(),
                'NAME'                   => $item->getName(),
                'WEIGHT'                 => $item->getWeight(),
                'DETAIL_PAGE_URL'        => $item->getDetailPageUrl(),
                'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
                'CATALOG_XML_ID'         => $offerIblockId,
                'PRODUCT_XML_ID'         => $item->getArticle(),
            ]);
            $allBonuses += $item->getBonus();
        }

        /** свойства */
        $orderProps = $bitrixOrder->getPropertyCollection();
        $propId = (int)OrderPropsTable::query()->setFilter(['=CODE' => 'MANZANA_NUMBER'])->setSelect(['ID'])->setCacheTtl(360000)->exec()->fetch()['ID'];
        $orderProp = $orderProps->getItemByOrderPropertyId($propId);
        $orderProp->setValue($order->getManzanaId());
        $propId = (int)OrderPropsTable::query()->setFilter(['=CODE' => 'USER_REGISTERED'])->setSelect(['ID'])->setCacheTtl(360000)->exec()->fetch()['ID'];
        $orderProp = $orderProps->getItemByOrderPropertyId($propId);
        $orderProp->setValue('Y');
        $propId = (int)OrderPropsTable::query()->setFilter(['=CODE' => 'IS_EXPORTED'])->setSelect(['ID'])->setCacheTtl(360000)->exec()->fetch()['ID'];
        $orderProp = $orderProps->getItemByOrderPropertyId($propId);
        $orderProp->setValue('Y');
        $propId = (int)OrderPropsTable::query()->setFilter(['=CODE' => 'BONUS_COUNT'])->setSelect(['ID'])->setCacheTtl(360000)->exec()->fetch()['ID'];
        $orderProp = $orderProps->getItemByOrderPropertyId($propId);
        $orderProp->setValue($allBonuses);
        $propId = (int)OrderPropsTable::query()->setFilter(['=CODE' => 'SHIPMENT_PLACE_CODE'])->setSelect(['ID'])->setCacheTtl(360000)->exec()->fetch()['ID'];
        $orderProp = $orderProps->getItemByOrderPropertyId($propId);
        $orderProp->setValue('DC01');

        $userCityService = App::getInstance()->getContainer()->get(UserCitySelectInterface::class);
        $city = $userCityService->getSelectedCity();
        $propId = (int)OrderPropsTable::query()->setFilter(['=CODE' => 'CITY_CODE'])->setSelect(['ID'])->setCacheTtl(360000)->exec()->fetch()['ID'];
        $orderProp = $orderProps->getItemByOrderPropertyId($propId);
        $orderProp->setValue($city['CODE']);
        $propId = (int)OrderPropsTable::query()->setFilter(['=CODE' => 'CITY'])->setSelect(['ID'])->setCacheTtl(360000)->exec()->fetch()['ID'];
        $orderProp = $orderProps->getItemByOrderPropertyId($propId);
        $orderProp->setValue($city['DISPLAY']);

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
                'DATE_UPDATE' => $order->getDateInsert(),
            ]
        );
        Manager::enableExtendsDiscount();

        return $result->isSuccess();
    }

    /**
     * @param Cheque $cheque
     *
     * @return OrderItem[]
     * @throws ManzanaServiceException
     * @throws NoItemsInChequeException
     * @throws ChequeItemArticleEmptyException
     * @throws ChequeItemNotExistsException
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

            $item = new OrderItem();
            $item
                ->setArticle($chequeItem->number)
                ->setBonus($chequeItem->bonus)
                ->setPrice($chequeItem->price)
                ->setQuantity($chequeItem->quantity)
                ->setSum($chequeItem->sum)
                ->setName($chequeItem->name)
                ->setHaveStock(false)
                ->setWeight($offer->getCatalogProduct()->getWeight())
                ->setDetailPageUrl($offer->getLink())
                ->setId($offer->getId());
            $result[$item->getArticle()] = $item;
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
}
