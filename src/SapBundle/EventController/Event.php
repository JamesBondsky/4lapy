<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\EventController;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\Helpers\BxCollection;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SapBundle\Consumer\ConsumerRegistry;
use FourPaws\SapBundle\Enum\SapOrder;
use FourPaws\SapBundle\Exception\LogicException;
use FourPaws\SapBundle\Exception\UnexpectedValueException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class Event
 *
 * Обработчики событий
 *
 * @package FourPaws\SapBundle\EventController
 */
class Event implements ServiceHandlerInterface
{
    /**
     * @var EventManager
     */
    protected static $eventManager;

    /**
     * @param EventManager $eventManager
     *
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        self::$eventManager = $eventManager;

        self::initHandler('OnSaleOrderSaved', 'consumeOrderAfterSaveOrder');
        self::initHandler('OnSalePaymentEntitySaved', 'consumeOrderAfterSavePayment');
    }

    /**
     * @param string $eventName
     * @param string $method
     * @param string $module
     */
    public static function initHandler(string $eventName, string $method, string $module = 'sale'): void
    {
        self::$eventManager->addEventHandler(
            $module,
            $eventName,
            [self::class, $method]
        );
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ObjectNotFoundException
     * @throws ApplicationCreateException
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    public static function consumeOrderAfterSaveOrder(BitrixEvent $event): void
    {
        /**
         * @var Order $order
         * @var OrderService $orderService
         */
        $order = $event->getParameter('ENTITY');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $orderService = Application::getInstance()->getContainer()->get(
            OrderService::class
        );

        /**
         * Если заказ уже выгружен в SAP, оплата онлайн, или заказ создан по подписке, пропускаем
         */
        if (
            self::isOrderExported($order)
            || self::isManzanaOrder($order)
            || $orderService->isOnlinePayment($order)
            || $orderService->isSubscribe($order)
        ) {
            return;
        }

        self::getConsumerRegistry()->consume($order);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws ApplicationCreateException
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    public static function consumeOrderAfterSavePayment(BitrixEvent $event): void
    {
        /** @var Payment $payment */
        $oldFields = $event->getParameter('VALUES');
        $payment = $event->getParameter('ENTITY');

        if (
            $oldFields['PAID'] !== 'Y'
            && (int)$payment->getPaymentSystemId() === SapOrder::PAYMENT_SYSTEM_ONLINE_ID
            && $payment->getOrderId() > 0
            && $payment->isPaid()
        ) {
            /**
             * Если оплата онлайн и статус меняется на оплачено, то выгружаем в SAP
             *
             * @var ConsumerRegistry $consumerRegistry
             */
            $order = Order::load($payment->getOrderId());

            /** @noinspection NullPointerExceptionInspection */
            if (!self::isOrderExported($order) && !self::isManzanaOrder($order)) {
                self::getConsumerRegistry()->consume($order);
            }
        }
    }

    /**
     * @throws ApplicationCreateException
     *
     * @return ConsumerRegistry
     */
    public static function getConsumerRegistry(): ConsumerRegistry
    {
        try {
            return Application::getInstance()->getContainer()->get(ConsumerRegistry::class);
        } catch (ServiceNotFoundException | ServiceCircularReferenceException $e) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return new ConsumerRegistry();
        }
    }

    /**
     * @param Order $order
     *
     * @return bool
     *
     * @throws ObjectNotFoundException
     */
    private static function isOrderExported(Order $order): bool
    {
        $isConsumedValue = BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), 'IS_EXPORTED');

        return null !== $isConsumedValue && $isConsumedValue->getValue() === 'Y';
    }

    /**
     * @param Order $order
     *
     * @return bool
     *
     * @throws ObjectNotFoundException
     */
    private static function isManzanaOrder(Order $order): bool
    {
        $manzanaNumberValue = BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), 'MANZANA_NUMBER');

        return null !== $manzanaNumberValue && (bool)$manzanaNumberValue->getValue();
    }
}
