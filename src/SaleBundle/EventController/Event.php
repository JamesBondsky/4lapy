<?php

namespace FourPaws\SaleBundle\EventController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use FourPaws\App\Application;
use FourPaws\App\BaseServiceHandler;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\MainTemplate;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\SaleBundle\Discount\Action\Action\DetachedRowDiscount;
use FourPaws\SaleBundle\Discount\Action\Action\DiscountFromProperty;
use FourPaws\SaleBundle\Discount\Action\Condition\BasketFilter;
use FourPaws\SaleBundle\Discount\Action\Condition\BasketQuantity;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Service\NotificationService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\UserAccountService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class Event
 *
 * Обработчики событий
 *
 * @package FourPaws\SaleBundle\EventController
 */
class Event extends BaseServiceHandler
{
    protected static $isEventsDisable = false;

    public static function disableEvents(): void
    {
        self::$isEventsDisable = true;
    }

    public static function enableEvents(): void
    {
        self::$isEventsDisable = false;
    }

    /**
     * @param EventManager $eventManager
     *
     * @return mixed|void
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);

        $module = 'sale';
        ###   Обработчики скидок       ###

        /** Инициализация кастомных правил работы с корзиной */
        static::initHandlerCompatible('OnCondSaleActionsControlBuildList', [Gift::class, 'GetControlDescr'], $module);
        static::initHandlerCompatible('OnCondSaleActionsControlBuildList', [BasketFilter::class, 'GetControlDescr'], $module);
        static::initHandlerCompatible('OnCondSaleActionsControlBuildList', [BasketQuantity::class, 'GetControlDescr'], $module);
        static::initHandlerCompatible('OnCondSaleActionsControlBuildList', [DiscountFromProperty::class, 'GetControlDescr'], $module);
        static::initHandlerCompatible('OnCondSaleActionsControlBuildList', [DetachedRowDiscount::class, 'GetControlDescr'], $module);
        /** Здесь дополнительная обработка акций */
        static::initHandler('OnAfterSaleOrderFinalAction', [Manager::class, 'OnAfterSaleOrderFinalAction'], $module);
        static::initHandler('OnBeforeSaleOrderFinalAction', [Manager::class, 'OnBeforeSaleOrderFinalAction'], $module);

        ###   Обработчики скидок EOF   ###

        /** отправка email */
        // новый заказ
        static::initHandler('OnSaleOrderSaved', [self::class, 'sendNewOrderMessage'], $module);
        // смена платежной системы у заказа
        static::initHandler('OnSalePaymentEntitySaved', [self::class, 'sendNewOrderMessage'], $module);
        // оплата заказа
        static::initHandler('OnSaleOrderPaid', [self::class, 'sendOrderPaymentMessage'], $module);
        // отмена заказа
        static::initHandler('OnSaleOrderCanceled', [self::class, 'sendOrderCancelMessage'], $module);
        // смена статуса заказа
        static::initHandler('OnSaleStatusOrderChange', [self::class, 'sendOrderStatusMessage'], $module);

        /** очистка кеша заказа */
        static::initHandler('OnSaleOrderSaved', [self::class, 'clearOrderCache'], $module);

        /** обновление бонусного счета пользователя и бонусного процента пользователя */
        $module = 'main';
        static::initHandlerCompatible('OnAfterUserLogin', [self::class, 'updateUserAccountBalance'], $module);
        static::initHandlerCompatible('OnAfterUserAuthorize', [self::class, 'updateUserAccountBalance'], $module);
        static::initHandlerCompatible('OnAfterUserLoginByHash', [self::class, 'updateUserAccountBalance'], $module);
    }

    public static function updateUserAccountBalance(): void
    {
        try {
            /** @var MainTemplate $template */
            $template = MainTemplate::getInstance(BitrixApplication::getInstance()->getContext());
            /** выполняем только при пользовательской авторизации(это аякс), либо из письма и обратных ссылок(это personal)
             *  так же чекаем что это не страница заказа
             */
            if (!$template->hasUserAuth()) {
                return;
            }
            $container = Application::getInstance()->getContainer();
            $userService = $container->get(CurrentUserProviderInterface::class);
            $userAccountService = $container->get(UserAccountService::class);
            $user = $userService->getCurrentUser();
            [, $bonus] = $userAccountService->refreshUserBalance($user);
            $userService->refreshUserBonusPercent($user, $bonus);
            $userService->refreshUserOpt($user);
        } catch (NotAuthorizedException $e) {
            // обработка не требуется
        } catch (\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('failed to update user account balance: ' . $e->getMessage());
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @throws SystemException
     */
    public static function sendNewOrderMessage(BitrixEvent $event): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        $entity = $event->getParameter('ENTITY');

        if ($entity instanceof Order) {
            $isNew = $event->getParameter('IS_NEW');
            if (!$isNew) {
                return;
            }
            $order = $entity;
        } elseif ($entity instanceof Payment) {
            /** @var PaymentCollection $collection */
            $collection = $entity->getCollection();
            $order = $collection->getOrder();
        } else {
            return;
        }

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(
            OrderService::class
        );
        if ($orderService->isSubscribe($order) || $orderService->isManzanaOrder($order)) {
            // пропускаются заказы, созданные по подписке
            return;
        }

        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()
            ->getContainer()
            ->get(NotificationService::class);

        $notificationService->sendNewOrderMessage($order);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @throws SystemException
     */
    public static function sendOrderPaymentMessage(BitrixEvent $event): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        /** @var Payment $payment */
        $order = $event->getParameter('ENTITY');

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(
            OrderService::class
        );
        if ($orderService->isManzanaOrder($order)) {
            return;
        }

        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()
            ->getContainer()
            ->get(NotificationService::class);

        $notificationService->sendOrderPaymentMessage($order);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public static function sendOrderCancelMessage(BitrixEvent $event): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(
            OrderService::class
        );
        if ($orderService->isManzanaOrder($order)) {
            return;
        }

        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()
            ->getContainer()
            ->get(NotificationService::class);

        $notificationService->sendOrderCancelMessage($order);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws SystemException
     * @throws ObjectPropertyException
     * @throws ArgumentException
     * @throws ApplicationCreateException
     */
    public static function sendOrderStatusMessage(BitrixEvent $event): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(
            OrderService::class
        );
        if ($orderService->isManzanaOrder($order)) {
            return;
        }

        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()
            ->getContainer()
            ->get(NotificationService::class);

        $notificationService->sendOrderStatusMessage($order);
    }

    /**
     * @param BitrixEvent $event
     */
    public static function clearOrderCache(BitrixEvent $event): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

        TaggedCacheHelper::clearManagedCache([
            'order:' . $order->getField('USER_ID'),
            'personal:order:' . $order->getField('USER_ID'),
            'order:item:' . $order->getId(),
        ]);
    }
}
