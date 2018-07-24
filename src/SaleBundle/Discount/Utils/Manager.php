<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Discount\Utils;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
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
 *
 * @package FourPaws\SaleBundle\Discount\Utils
 */
class Manager
{
    protected static $extendEnabled = true;
    protected static $extendCalculated = false;

    /** @var bool */
    protected static $calculation = false;

    /**
     * @param Event $event
     */
    public static function OnBeforeSaleOrderFinalAction(Event $event): void
    {
        if (self::isCalculation()) {
            $event->addResult(new EventResult(EventResult::ERROR));
        }
    }

    /**
     * @param null|Event $event
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
    public static function OnAfterSaleOrderFinalAction(Event $event): void
    {
        if (!self::isExtendDiscountEnabled()) {
            return;
        }

        /**
         * @var Order $order
         */
        $order = $event->getParameter('ENTITY');

        if ($order->isCanceled()) {
            return;
        }

        self::setCalculation(true);
        if (self::isOrderNotEmpty($order) && !self::$extendCalculated) {
            self::disableExtendsDiscount();
            $container = Application::getInstance()->getContainer();
            $basketService = $container->get(BasketService::class);
            $manzana = $container->get(Manzana::class);
            $couponStorage = $container->get(CouponStorageInterface::class);

            // Автоматически добавляем подарки
            $basketService
                ->getAdder('gift', $order)
                ->processOrder();

            // Удаляем подарки, акции которых не выполнились
            $basketService
                ->getCleaner('gift', $order)
                ->processOrder();

            // нужна дополнительная проверка так как иногда к этому моменту в корзине не остается товаров
            if (self::isOrderNotEmpty($order)) {

                $basketService
                    ->getAdder('detach', $order)
                    ->processOrder();

                $promoCode = $couponStorage->getApplicableCoupon();
                if ($promoCode) {
                    $manzana->setPromocode($promoCode);
                }

                try {
                    $manzana->calculate($order);
                    $basketService->setPromocodeDiscount($manzana->getDiscount());
                } catch (ManzanaPromocodeUnavailableException $e) {
                    $couponStorage->delete($promoCode);
                }
            }

            self::enableExtendsDiscount();

            self::$extendCalculated = true;
        }
        self::setCalculation(false);
    }

    /**
     * @todo вынести куданить в хелпер
     *
     * @param Order $order
     *
     * @return bool
     */
    public static function isOrderNotEmpty(Order $order): bool
    {
        $quantity = 0;
        if ($basket = $order->getBasket()) {
            /** @var BasketItem $basketItem */
            foreach ($basket->getBasketItems() as $basketItem) {
                $quantity += (int)$basketItem->getQuantity();
            }
        }
        return (bool)$quantity;
    }

    /**
     * @return bool
     */
    public static function isExtendDiscountEnabled(): bool
    {
        return self::$extendEnabled;
    }

    /**
     * Отключаем расчет акций для предотвращения многократного применения
     */
    public static function disableExtendsDiscount(): void
    {
        self::$extendEnabled = false;
    }


    /**
     * Включаем расчет акций
     */
    public static function enableExtendsDiscount(): void
    {
        self::$extendEnabled = true;
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

    /**
     * @return bool
     */
    public static function isExtendCalculated(): bool
    {
        return self::$extendCalculated;
    }

    /**
     * @param bool $extendCalculated
     */
    public static function setExtendCalculated(bool $extendCalculated): void
    {
        self::$extendCalculated = $extendCalculated;
    }

    /**
     * @return bool
     */
    public static function isCalculation(): bool
    {
        return self::$calculation;
    }

    /**
     * @param bool $calculation
     */
    public static function setCalculation(bool $calculation): void
    {
        self::$calculation = $calculation;
    }
}
