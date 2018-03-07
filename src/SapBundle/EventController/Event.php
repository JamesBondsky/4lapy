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
use FourPaws\SapBundle\Consumer\ConsumerRegistry;
use FourPaws\SapBundle\Enum\SapOrderEnum;
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
         * Если заказ новый...
         */
        if ($event->getParameter('IS_NEW')) {
            /** @var Order $order */
            $order = $event->getParameter('ENTITY');
            /**
             * ...и оплата не онлайн, отправляем в SAP
             */
            if ($order->getPaymentSystemId() === SapOrderEnum::PAYMENT_SYSTEM_ONLINE_ID) {
                return;
            }

            self::getConsumerRegistry()->consume($order);
        }
    }

    /**
     * @param BitrixEvent $event
     *
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
            && (int)$payment->getPaymentSystemId() === SapOrderEnum::PAYMENT_SYSTEM_ONLINE_ID
            && $payment->getOrderId() > 0
            && $payment->isPaid()
        ) {
            /**
             * Если оплата онлайн и статус меняется на оплачено, то выгружаем в SAP
             *
             * @var ConsumerRegistry $consumerRegistry
             */

            $order = Order::load($payment->getOrderId());
            self::getConsumerRegistry()->consume($order);
        }
    }

    /**
     * @return ConsumerRegistry
     *
     * @throws ApplicationCreateException
     */
    public static function getConsumerRegistry(): ConsumerRegistry
    {
        try {
            return Application::getInstance()->getContainer()->get(ConsumerRegistry::class);
        } catch (ServiceNotFoundException | ServiceCircularReferenceException $e) {
            return new ConsumerRegistry();
        }
    }
}
