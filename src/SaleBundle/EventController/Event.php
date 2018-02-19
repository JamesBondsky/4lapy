<?php

namespace FourPaws\SaleBundle\EventController;

use Bitrix\Main\EventManager;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\SaleBundle\Discount\Action\Condition\BasketQuantity;
use FourPaws\SaleBundle\Discount\BasketFilter;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Gifter;

/**
 * Class Event
 *
 * Обработчики событий
 *
 * @package FourPaws\SaleBundle\EventController
 */
class Event implements ServiceHandlerInterface
{
    /**
     * @var EventManager
     */
    protected static $eventManager;

    /**
     * @param \Bitrix\Main\EventManager $eventManager
     *
     * @return mixed|void
     */
    public static function initHandlers(EventManager $eventManager)
    {
        self::$eventManager = $eventManager;
        self::initHandler('OnCondSaleActionsControlBuildList', [Gift::class, 'GetControlDescr']);
        self::initHandler('OnCondSaleActionsControlBuildList', [Gifter::class, 'GetControlDescr']);
        self::initHandler('OnCondSaleActionsControlBuildList', [BasketFilter::class, 'GetControlDescr']);
        self::initHandler('OnCondSaleActionsControlBuildList', [BasketQuantity::class, 'GetControlDescr']);
        self::initHandler('OnAfterSaleOrderFinalAction', [Manager::class, 'OnAfterSaleOrderFinalAction']);
    }

    /**
     *
     *
     * @param string $eventName
     * @param callable $callback
     * @param string $module
     *
     */
    public static function initHandler(string $eventName, callable $callback, string $module = 'sale')
    {
        self::$eventManager->addEventHandler(
            $module,
            $eventName,
            $callback
        );
    }
}
