<?php

namespace FourPaws\SaleBundle\EventController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\SaleBundle\Discount\Action\Action\DetachedRowDiscount;
use FourPaws\SaleBundle\Discount\Action\Action\DiscountFromProperty;
use FourPaws\SaleBundle\Discount\Action\Condition\BasketQuantity;
use FourPaws\SaleBundle\Discount\Action\Condition\BasketFilter;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Gifter;
use FourPaws\SaleBundle\Exception\ValidationException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\NotificationService;
use FourPaws\SaleBundle\Service\UserAccountService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
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
    public static function initHandlers(EventManager $eventManager): void
    {
        self::$eventManager = $eventManager;

        ###   Обработчики скидок       ###

        /** Инициализация кастомных правил работы с корзиной */
        self::initHandler('OnCondSaleActionsControlBuildList', [Gift::class, 'GetControlDescr']);
        self::initHandler('OnCondSaleActionsControlBuildList', [Gifter::class, 'GetControlDescr']);
        self::initHandler('OnCondSaleActionsControlBuildList', [BasketFilter::class, 'GetControlDescr']);
        self::initHandler('OnCondSaleActionsControlBuildList', [BasketQuantity::class, 'GetControlDescr']);
        self::initHandler('OnCondSaleActionsControlBuildList', [DiscountFromProperty::class, 'GetControlDescr']);
        self::initHandler('OnCondSaleActionsControlBuildList', [DetachedRowDiscount::class, 'GetControlDescr']);
        /** Здесь дополнительная обработка акций */
        self::initHandler('OnAfterSaleOrderFinalAction', [Manager::class, 'OnAfterSaleOrderFinalAction']);

        ###   Обработчики скидок EOF   ###


        self::initHandler('OnSaleBasketItemRefreshData', [static::class, 'updateItemAvailability']);

        self::initHandler('OnSaleOrderSaved', [static::class, 'sendNewOrderMessage']);
        self::initHandler('OnSaleOrderPaid', [static::class, 'sendOrderPaymentMessage']);
        self::initHandler('OnSaleOrderCanceled', [static::class, 'sendOrderCancelMessage']);
        self::initHandler('OnSaleStatusOrderChange', [static::class, 'sendOrderStatusMessage']);

        self::initHandler('OnAfterUserLogin', [static::class, 'updateUserAccountBalance'], 'main');
        self::initHandler('OnAfterUserAuthorize', [static::class, 'updateUserAccountBalance'], 'main');
        self::initHandler('OnAfterUserLoginByHash', [static::class, 'updateUserAccountBalance'], 'main');
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

    /**
     * @param array $user
     */
    public static function updateUserAccountBalance(array $user)
    {
        $userId = (int)$user['user_fields']['ID'];
        if($userId > 1) {
            try {
                $container = Application::getInstance()->getContainer();
                $userService = $container->get(CurrentUserProviderInterface::class);
                $userAccountService = $container->get(UserAccountService::class);

                $userEntity = $userService->getUserRepository()->find($userId);
                /* @todo по ТЗ должно выполняться в фоновом режиме */
                [$res, $bonus] = $userAccountService->refreshUserBalance($userEntity);

                /** обновление скидки
                 * @todo сделать обновление через очередь, не критично если какое-то время будет старая скидка, тем более в случае неактивности манзаны она не обновится все равно
                 */
                $userService->refreshUserDiscount($userEntity, $bonus);
            } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException $e) {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            } catch (ConstraintDefinitionException|InvalidIdentifierException|ValidationException $e) {
                $logger = LoggerFactory::create('params');
                $logger->critical('Ошибка параметров - ' . $e->getMessage());
            }
            catch (NotAuthorizedException $e){
                /** не выскочит */
            }
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Exception
     */
    public static function updateItemAvailability(BitrixEvent $event)
    {
        $basketItem = $event->getParameter('ENTITY');
        Application::getInstance()
                   ->getContainer()
                   ->get(BasketService::class)
                   ->refreshItemAvailability($basketItem);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public static function sendNewOrderMessage(BitrixEvent $event)
    {
        /** @var Order $order */
        $order = $event->getParameter('ENTITY');
        $isNew = $event->getParameter('IS_NEW');
        if (!$isNew) {
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
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public static function sendOrderPaymentMessage(BitrixEvent $event)
    {
        /** @var Payment $payment */
        $order = $event->getParameter('ENTITY');

        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()
                                          ->getContainer()
                                          ->get(NotificationService::class);

        $notificationService->sendOrderPaymentMessage($order);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public static function sendOrderCancelMessage(BitrixEvent $event)
    {
        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()
                                          ->getContainer()
                                          ->get(NotificationService::class);

        $notificationService->sendOrderCancelMessage($order);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public static function sendOrderStatusMessage(BitrixEvent $event)
    {
        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()
                                          ->getContainer()
                                          ->get(NotificationService::class);

        $notificationService->sendOrderStatusMessage($order);
    }
}
