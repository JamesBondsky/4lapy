<?php
/**
 * Created by PhpStorm.
 * Date: 08.02.2018
 * Time: 18:11
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Event;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketPropertyItem;
use Bitrix\Sale\Order;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaPromocodeUnavailableException;
use FourPaws\SaleBundle\Discount\Manzana;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponStorageInterface;
use FourPaws\SaleBundle\Service\BasketService;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

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
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws RuntimeException
     * @throws NotFoundException
     * @throws InvalidArgumentException
     * @throws ApplicationCreateException
     * @throws Exception
     * @throws ObjectNotFoundException
     * @throws NotSupportedException
     * @throws ArgumentOutOfRangeException
     */
    public static function OnAfterSaleOrderFinalAction(Event $event = null): void
    {
        static $execution;
        if (!$execution && self::$finalActionEnabled) {
            $execution = true;
            if ($event instanceof Event) {
                /** @var Order $order */
                $order = $event->getParameter('ENTITY');
                if ($order instanceof Order) {
                    $container = Application::getInstance()->getContainer();
                    $basketService = $container->get(BasketService::class);
                    $manzana = $container->get(Manzana::class);
                    $couponStorage = $container->get(CouponStorageInterface::class);

                    // Автоматически добавляем подарки
                    $basketService
                        ->getAdder('gift')
                        ->processOrder();

                    // Удаляем подарки, акции которых не выполнились
                    /**
                     * @todo не сохранять подарки
                     */
                    $basketService
                        ->getCleaner('gift')
                        ->processOrder();

                    $basketService
                        ->getAdder('detach')
                        ->processOrder();

                    $promoCode = $couponStorage->getApplicableCoupon();
                    if ($promoCode) {
                        $manzana->setPromocode($promoCode);
                    }

                    try {
                        $manzana->calculate();
                        $basketService->setPromocodeDiscount($manzana->getDiscount());
                    } catch (ManzanaPromocodeUnavailableException $e) {
                        $couponStorage->delete($promoCode);
                    }
                }
            }

            $execution = false;
        }
    }

    /**
     * Отключаем расчет акций для предотвращения многократного применения
     */
    public static function disableProcessingFinalAction(): void
    {
        self::$finalActionEnabled = false;
    }


    /**
     * Включаем расчет акций
     */
    public static function enableProcessingFinalAction(): void
    {
        self::$finalActionEnabled = true;
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    public static function getExistGifts(Order $order): array
    {
        $result = [];
        $basket = $order->getBasket();

        if ($basket) {
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
