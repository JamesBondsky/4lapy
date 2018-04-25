<?php

namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
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
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
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
                $items = [];
                if ($cheque->hasItemsBool()) {
                    $chequeItems = new ArrayCollection($this->manzanaService->getItemsByCheque($cheque->chequeId));
                    if (!$chequeItems->isEmpty()) {
                        /** @var ChequeItem $chequeItem */
                        $i = -1;
                        foreach ($chequeItems as $chequeItem) {
                            $i++;
                            if ((int)$chequeItem->number < 2000000) {
                                $item = new OrderItem();
                                $item->setId($chequeItem->number);
                                if ((int)$chequeItem->number > 1000000) {
                                    $item->setArticle($chequeItem->number);
                                }
                                $item->setBonus($chequeItem->bonus);
                                $item->setPrice($chequeItem->price);
                                $item->setQuantity($chequeItem->quantity);
                                $item->setSum($chequeItem->sum);
                                $item->setName($chequeItem->name);
                                $item->setHaveStock(false);
                                $item->setWeight(0);
                                $items[!empty($item->getArticle()) ? $item->getArticle() : $i] = $item;
                            }
                        }
                    }
                } else {
                    // пропускаем чеки без товаров
                    continue;
                }
                $order->setItems(new ArrayCollection($items));
                $orders[$order->getId()] = $order;
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
     * @return Store
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws \Exception
     * @throws NotFoundException
     */
    public function getStore(Order $order): Store
    {
        /** @var OrderProp $prop */
        //CITY_CODE
        $props = $order->getProps();
        if (!$props->isEmpty()) {
            $dpdTerminal = $props->get('DPD_TERMINAL_CODE');
            $deliveryPlace = $props->get('DELIVERY_PLACE_CODE');
            if ($dpdTerminal instanceof OrderProp && $dpdTerminal->getValue()) {
                $deliveryService = App::getInstance()->getContainer()->get('delivery.service');

                return $deliveryService->getDpdTerminalByCode($dpdTerminal->getValue());
            }
            if ($deliveryPlace instanceof OrderProp && $deliveryPlace->getValue()) {
                $storeService = App::getInstance()->getContainer()->get('store.service');

                return $storeService->getStoreByXmlId($deliveryPlace->getValue());
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
     * @return Order|null
     * @throws \Exception
     */
    public function getOrderById(int $orderId)
    {
        $params = [
            'filter' => [
                'ID' => $orderId
            ]
        ];
        $collection = $this->orderRepository->findBy($params);

        return $collection->count() ? $collection->first() : null;
    }
}
