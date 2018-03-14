<?php
/**
 * Created by PhpStorm.
 * Date: 08.02.2018
 * Time: 18:11
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils;

use Bitrix\Main\Event;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketPropertyItem;
use Bitrix\Sale\Order;
use FourPaws\App\Application;
use FourPaws\SaleBundle\Service\BasketService;

/**
 * Class Manager
 * @package FourPaws\SaleBundle\Discount\Utils
 */
class Manager
{
    protected static $finalActionEnabled = true;

    /**
     *
     *
     * @param Event|null $event
     *
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Exception
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\NotSupportedException
     */
    public static function OnAfterSaleOrderFinalAction(Event $event = null)
    {
        static $execution;
        if (!$execution && self::$finalActionEnabled) {
            $execution = true;
            if ($event instanceof Event) {
                /** @var Order $order */
                $order = $event->getParameter('ENTITY');
                if ($order instanceof Order) {

                    // Автоматически добавляем подарки
                    Application::getInstance()
                        ->getContainer()
                        ->get(BasketService::class)
                        ->getAdder('gift')
                        ->processOrder();

                    // Удаляем подарки, акции которых не выполнились
                    Application::getInstance()
                        ->getContainer()
                        ->get(BasketService::class)
                        ->getCleaner('gift')
                        ->processOrder();
                }
            }
            $execution = false;
        }
    }

    /**
     *
     *
     */
    public static function disableProcessingFinalAction(): void
    {
        self::$finalActionEnabled = false;
    }

    /**
     *
     *
     */
    public static function enableProcessingFinalAction(): void
    {
        self::$finalActionEnabled = true;
    }

    /**
     *
     *
     * @param Order $order
     *
     * @return array
     */
    public static function getExistGifts(Order $order): array
    {
        $result = [];
        if ($basket = $order->getBasket()) {
            /** @var BasketItem $basketItem */
            foreach ($basket->getBasketItems() as $basketItem) {
                /** @var BasketPropertyItem $basketPropertyItem */
                foreach ($basketItem->getPropertyCollection() as $basketPropertyItem) {
                    if ($basketPropertyItem->getField('CODE') === 'IS_GIFT') {
                        $result[$basketItem->getId()]['quantity'] = (int)$basketItem->getQuantity();
                        $result[$basketItem->getId()]['discountId'] = (int)$basketPropertyItem->getField('VALUE');
                        $result[$basketItem->getId()]['offerId'] = (int)$basketItem->getProductId();
                        $result[$basketItem->getId()]['basketId'] = (int)$basketItem->getId();
                    }
                    if ($basketPropertyItem->getField('CODE') === 'IS_GIFT_SELECTED') {
                        $result[$basketItem->getId()]['selected'] = $basketPropertyItem->getField('VALUE');
                    }
                }
            }
        }
        return $result;
    }
}