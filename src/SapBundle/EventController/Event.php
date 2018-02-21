<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\EventController;

use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use FourPaws\App\ServiceHandlerInterface;

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
     * @return mixed|void
     */
    public static function initHandlers(EventManager $eventManager)
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
    public static function initHandler(string $eventName, string $method, string $module = 'sale')
    {
        self::$eventManager->addEventHandler(
            $module,
            $eventName,
            [
                self::class,
                $method,
            ]
        );
    }

    /**
     * @param BitrixEvent $event
     */
    public static function consumeOrderAfterSave(BitrixEvent $event)
    {
        $isNew = $event->getParameter('IS_NEW');

        if ($isNew) {
            /** @var Order $order */
            $order = $event->getParameter('ENTITY');

            /**
             * Если новый заказ и оплата не онлайн, отправляем в SAP
             *
             * @todo implement
             */
        }
    }

    /**
     * @param BitrixEvent $event
     */
    public static function consumeOrderAfterSavePayment(BitrixEvent $event)
    {
        /** @var Payment $payment */
        $oldFields = $event->getParameter('VALUES');
        $payment = $event->getParameter('ENTITY');

        if ($payment->getPaymentSystemId() === 3 && $payment->isPaid()) {
            /**
             * Если оплата онлайн и статус меняется на оплачено, то выгружаем в SAP
             *
             * @todo implement
             */
        }
    }
}
