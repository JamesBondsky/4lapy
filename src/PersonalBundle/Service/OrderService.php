<?php

namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotImplementedException;
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
use Bitrix\Sale\Order as BitrixOrderService;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
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
use FourPaws\PersonalBundle\Repository\OrderRepository;
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
    public static $finalStatuses = ['G', 'J'];
    public static $cancelStatuses = ['A', 'K'];
    protected static $manzanaFinalStatus = 'G';
    protected $manzanaFinalStatusSort = 110;
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /** @var CurrentUserProviderInterface $currentUser */
    private $currentUser;
    /** @var ManzanaService */
    private $manzanaService;
    private $siteManzanaOrders;
    private $manzanaOrderOffers;

    /**
     * OrderService constructor.
     *
     * @param OrderRepository $orderRepository
     *
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(OrderRepository $orderRepository)
    {
        $container = App::getInstance()->getContainer();
        $this->orderRepository = $orderRepository;
        $this->currentUser = $container->get(CurrentUserProviderInterface::class);
        $this->manzanaService = $container->get('manzana.service');
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
        $closedSiteOrderDates = [];
        /** @var Order $closedSiteOrder */
        foreach ($closedSiteOrders as $closedSiteOrder) {
            $timestamp = $closedSiteOrder->getDateInsert()->getTimestamp();
            $closedSiteOrderDates[$timestamp] = $closedSiteOrder->getId();
            /** учитываем рассинхрон в секунду */
            $closedSiteOrderDates[$timestamp - 1] = $closedSiteOrder->getId();
            $closedSiteOrderDates[$timestamp + 1] = $closedSiteOrder->getId();
        }
        /** @var Order $manzanaOrder */
        /** Очищаем дубли из манзаны */
        foreach ($manzanaOrders as $key => $manzanaOrder) {
            $timestamp = $manzanaOrder->getDateInsert()->getTimestamp();
            if (\in_array($timestamp, $closedSiteOrderDates, true)) {
                /** заполняем бонусы по данным из манзаны */
                /** @var Order $realOrder */
                $realOrder =& $closedSiteOrders[$closedSiteOrderDates[$timestamp]];
                /** @var OrderItem $item */
                /** @var OrderItem $manzanaItem */
                if ($realOrder instanceof Order) {
                    foreach ($manzanaOrder->getItems() as $manzanaItem) {
                        foreach ($realOrder->getItems() as &$item) {
                            if ($item->getXmlId() === $manzanaItem->getXmlId() || $item->getName() === $manzanaItem->getName()) {
                                $item->setBonus($manzanaItem->getBonus());
                                break;
                            }
                        }
                    }
                }
                unset($item, $manzanaOrders[$key]);
            }
        }
        return new ArrayCollection(array_merge($closedSiteOrders, $manzanaOrders));
    }

    /**
     * @return ArrayCollection
     * @throws ObjectPropertyException
     * @throws \FourPaws\SaleBundle\Exception\OrderCreateException
     * @throws ObjectNotFoundException
     * @throws ObjectException
     * @throws NotImplementedException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws ApplicationCreateException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws ManzanaServiceContactSearchMoreOneException
     * @throws ManzanaServiceContactSearchNullException
     * @throws ManzanaServiceException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws \Exception
     */
    public function getManzanaOrders(): ArrayCollection
    {
        $orders = new ArrayCollection();
        $cheques = new ArrayCollection($this->manzanaService->getCheques($this->manzanaService->getContactIdByUser()));
        if (!$cheques->isEmpty()) {
            $hasAdd = false;
            /** @var Cheque $cheque */
            foreach ($cheques as $cheque) {
                $order = new Order();
                /** @var \DateTimeImmutable $date */
                $date = $cheque->date;
                $bitrixDate = DateTime::createFromTimestamp($date->getTimestamp());
                $order->setDateInsert($bitrixDate);
                $order->setDatePayed($bitrixDate);
                $order->setDateStatus($bitrixDate);
                $order->setDateUpdate($bitrixDate);
                $order->setManzana(true);
                $order->setUserId($this->currentUser->getCurrentUserId());
                $order->setPayed(true);
                $order->setStatusId(static::$manzanaFinalStatus);
                $order->setPrice($cheque->sum);
                $order->setItemsSum($cheque->sum);
                $order->setManzanaId($cheque->chequeNumber);
                $order->setPaySystemId(PaySystemActionTable::query()->setFilter(['CODE' => 'cash'])->setSelect(['PAY_SYSTEM_ID'])->exec()->fetch()['PAY_SYSTEM_ID']);
                $order->setDeliveryId(SaleDeliveryServiceTable::query()->setSelect(['ID'])->setFilter(['CODE' => '4lapy_pickup'])->setCacheTtl(360000)->exec()->fetch()['ID']);
                $items = [];
                $newManzana = true;
                if ($cheque->hasItemsBool()) {
                    $chequeItems = new ArrayCollection($this->manzanaService->getItemsByCheque($cheque->chequeId));
                    if (!$chequeItems->isEmpty()) {
                        /** @var ChequeItem $chequeItem */
                        $i = -1;
                        foreach ($chequeItems as $chequeItem) {
                            $i++;
                            if ((int)$chequeItem->number < 2000000) {
                                $item = new OrderItem();
                                if ((int)$chequeItem->number > 1000000) {
                                    $item->setArticle($chequeItem->number);
                                    /** @todo лучше вынести вниз в групповой запрос */
                                    $offer = null;
                                    if (!empty($item->getArticle())) {
                                        /** @var Offer $offer */
                                        $offer = (new OfferQuery())->withFilter(['=XML_ID' => $item->getArticle()])->withSelect(['ID'])->withNav(['nTopCount' => 1])->exec()->first();
                                        $this->manzanaOrderOffers[$order->getManzanaId()][$item->getArticle()] = $offer;
                                    }
                                    if ($offer !== null && $offer instanceof Offer && $offer->getId() > 0) {
                                        $item->setId($offer->getId());
                                    } else {
                                        $item->setId($chequeItem->number);
                                        $newManzana = false;
                                    }
                                } else {
                                    $item->setId($chequeItem->number);
                                    $newManzana = false;
                                }
                                $item->setBonus($chequeItem->bonus);
                                $item->setPrice($chequeItem->price);
                                $item->setQuantity($chequeItem->quantity);
                                $item->setSum($chequeItem->sum);
                                $item->setName($chequeItem->name);
                                $item->setHaveStock(false);
                                $item->setWeight(0);
                                $items[!empty($item->getArticle()) ? $item->getArticle() : $i] = $item;
                            } else {
                                $newManzana = false;
                            }
                        }
                    }
                } else {
                    // пропускаем чеки без товаров
                    continue;
                }
                $order->setNewManzana($newManzana);
                $order->setItems(new ArrayCollection($items));
                /** если все позиции на сайте ищутся то сохраняем на сайте и исключаем из массива - но будет принудительная перезагрузка
                 */
                if ($order->isNewManzana()) {
                    if (!$this->hasOrderByManzana($order)) {
                        $hasAdd = $this->addOrder($order);
                    }
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
        //CITY_CODE
        $props = $order->getProps();
        if (!$props->isEmpty()) {
            $dpdTerminal = $props->get('DPD_TERMINAL_CODE');
            $deliveryPlace = $props->get('DELIVERY_PLACE_CODE');
            if ($dpdTerminal instanceof OrderProp && $dpdTerminal->getValue()) {
                try {
                    /** @var DeliveryService $deliveryService */
                    $deliveryService = App::getInstance()->getContainer()->get('delivery.service');

                    return $deliveryService->getDpdTerminalByCode($dpdTerminal->getValue());
                } catch (\Exception $exception) {
                    return null;
                }
            }
            if ($deliveryPlace instanceof OrderProp && $deliveryPlace->getValue()) {
                try {
                    /** @var StoreService $storeService */
                    $storeService = App::getInstance()->getContainer()->get('store.service');

                    return $storeService->getStoreByXmlId($deliveryPlace->getValue());
                } catch (\Exception $exception) {
                    return null;
                }
            }
        }

        $store = new Store();
        //$street = $order->getPropValue('STREET') . ' ул.';
        $street = $order->getPropValue('STREET');
        $house = ', д.' . $order->getPropValue('HOUSE');
        $building = !empty($order->getPropValue('BUILDING')) ? ', корпус/строение ' . $order->getPropValue('BUILDING') : '';
        $porch = !empty($order->getPropValue('PORCH')) ? ', подъезд. ' . $order->getPropValue('PORCH') : '';
        $apartment = !empty($order->getPropValue('APARTMENT')) ? ', кв. ' . $order->getPropValue('APARTMENT') : '';
        $floor = !empty($order->getPropValue('FLOOR')) ? ', этаж ' . $order->getPropValue('FLOOR') : '';
        $city = ', г. ' . $order->getPropValue('CITY');
        $store->setAddress($street . $house . $building . $porch . $apartment . $floor . $city);
        $store->setActive(true);
        $store->setIsShop(false);

        return $store;
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
        return \in_array($order->getManzanaId(), $this->getSiteManzanaOrders($order->getUserId()), true);
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws SystemException
     * @throws ObjectPropertyException
     */
    protected function getSiteManzanaOrders($userId): array
    {
        if ($this->siteManzanaOrders === null) {
            $res = OrderTable::query()->setFilter([
                'USER_ID'         => $userId,
                'PROPERTY.CODE'   => 'MANZANA_NUMBER',
                '!PROPERTY.VALUE' => [null, ''],
            ])->setSelect(['ID', 'PROPERTY_CODE' => 'PROPERTY.CODE', 'PROPERTY_VALUE' => 'PROPERTY.VALUE'])->exec();
            while ($item = $res->fetch()) {
                if ($item['PROPERTY_CODE'] === 'MANZANA_NUMBER') {
                    $this->siteManzanaOrders[$item['ID']] = $item['PROPERTY_VALUE'];
                }
            }
        }
        return $this->siteManzanaOrders ?? [];
    }

    /**
     * @param Order $order
     *
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ObjectPropertyException
     * @throws \RuntimeException
     * @throws ArgumentException
     * @throws SystemException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws OrderCreateException
     * @throws OrderCreateException
     * @throws \Exception
     * @throws \Exception
     */
    protected function addOrder(Order $order): bool
    {
        if (!$order->isManzana() || empty($order->getManzanaId()) || $order->isItemsEmpty()) {
            return false;
        }
        $bitrixOrder = BitrixOrderService::create(SITE_ID, $order->getUserId(), $order->getCurrency());

        /** ставим даты */
        $bitrixOrder->setFieldNoDemand('STATUS_ID', 'G');
        $bitrixOrder->setFieldNoDemand('DATE_INSERT', $order->getDateInsert());
        $bitrixOrder->setFieldNoDemand('DATE_UPDATE', $order->getDateInsert());
        $bitrixOrder->setFieldNoDemand('PAYED', 'Y');
        $bitrixOrder->setFieldNoDemand('DATE_PAYED', $order->getDateInsert());
        $bitrixOrder->setFieldNoDemand('DATE_STATUS', $order->getDateInsert());

        /** ставим account_number = id чека с обрезкой в 100 символов на всякий случай */
        $bitrixOrder->setFieldNoDemand('ACCOUNT_NUMBER', substr($order->getManzanaId(), 0, 100));

        /** корзина */
        $orderBasket = Basket::create(SITE_ID);
        /** @var OrderItem $item */
        $allBonuses = 0;
        foreach ($order->getItems() as $item) {
            $productId = $item->getId();
            /** @var Offer $offer */
            $offer = $this->manzanaOrderOffers[$order->getManzanaId()][$item->getArticle()];
            $basketItem = $orderBasket->createItem('catalog', $productId);
            $basketItem->setPrice($item->getPrice(), true);
            $basketItem->setFieldNoDemand('QUANTITY', $item->getQuantity());
            $basketItem->setFieldNoDemand('CAN_BUY', 'Y');
            $basketItem->setFieldNoDemand('DELAY', 'N');
            $basketItem->setFieldNoDemand('CURRENCY', 'RUB');
            $basketItem->setFieldNoDemand('NAME', $offer->getName());
            $basketItem->setFieldNoDemand('WEIGHT', $offer->getCatalogProduct()->getWeight());
            $basketItem->setFieldNoDemand('DETAIL_PAGE_URL',
                $offer->getProduct()->getDetailPageUrl() . '?offer=' . $offer->getId());
            $basketItem->setFieldNoDemand('PRODUCT_PROVIDER_CLASS', 'Bitrix\Catalog\Product\CatalogProvider');
            $basketItem->setFieldNoDemand('CATALOG_XML_ID',
                IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS));
            $basketItem->setFieldNoDemand('PRODUCT_XML_ID', $item->getArticle());
            $allBonuses += $item->getBonus();
        }
        $bitrixOrder->setBasket($orderBasket);

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
        return $result->isSuccess();
    }
}
