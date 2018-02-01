<?php
/**
 * Created by PhpStorm.
 * Date: 30.01.2018
 * Time: 14:17
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Cleaner;

use Bitrix\Main\Event;
use Bitrix\Sale\Order;

/**
 * Class Cleaner
 * @package FourPaws\SaleBundle\Discount\Cleaner
 */
class Cleaner
{
    /**
     *
     *
     * @param Event|null $event
     *
     */
    public static function OnAfterSaleOrderFinalAction(Event $event = null)
    {
        if($event instanceof Event) {
            /** @var Order $order */
            $order = $event->getParameter('ENTITY');
            if($order instanceof Order) {
                // do things
                // dump('ok');
            }
        }

    }
}