<?php

namespace FourPaws\SaleBundle\EventController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use Bitrix\Main\EventResult;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketItemCollection;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\BaseServiceHandler;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\MainTemplate;
use FourPaws\App\Tools\StaticLoggerTrait;
use FourPaws\Helpers\BxCollection;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Service\OrderService as PersonalOrderService;
use FourPaws\PersonalBundle\Service\PiggyBankService;
use FourPaws\SaleBundle\Discount\Action\Action\DetachedRowDiscount;
use FourPaws\SaleBundle\Discount\Action\Action\DiscountFromProperty;
use FourPaws\SaleBundle\Discount\Action\Condition\BasketFilter;
use FourPaws\SaleBundle\Discount\Action\Condition\BasketQuantity;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Enum\OrderStatus;
use FourPaws\SaleBundle\Exception\ForgotBasket\FailedToUpdateException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\ForgotBasketService;
use FourPaws\SaleBundle\Service\NotificationService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\PaymentService;
use FourPaws\SaleBundle\Service\UserAccountService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Repository\UserRepository;
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
    use StaticLoggerTrait;

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

        static::initHandlerCompatible('OnBuildGlobalMenu', [
            self::class,
            'addRetailRocketOrderReportToAdminMenu',
        ], 'main');

        $module = 'sale';
        ###   Обработчики скидок       ###

        /** Инициализация кастомных правил работы с корзиной */
        static::initHandlerCompatible('OnCondSaleActionsControlBuildList', [
            Gift::class,
            'GetControlDescr'
        ], $module);
        static::initHandlerCompatible('OnCondSaleActionsControlBuildList', [
            BasketFilter::class,
            'GetControlDescr'
        ], $module);
        static::initHandlerCompatible('OnCondSaleActionsControlBuildList', [
            BasketQuantity::class,
            'GetControlDescr'
        ], $module);
        static::initHandlerCompatible('OnCondSaleActionsControlBuildList', [
            DiscountFromProperty::class,
            'GetControlDescr'
        ], $module);
        static::initHandlerCompatible('OnCondSaleActionsControlBuildList', [
            DetachedRowDiscount::class,
            'GetControlDescr'
        ], $module);
        /** Здесь дополнительная обработка акций */
        static::initHandler('OnAfterSaleOrderFinalAction', [
            Manager::class,
            'OnAfterSaleOrderFinalAction'
        ], $module);
        static::initHandler('OnBeforeSaleOrderFinalAction', [
            Manager::class,
            'OnBeforeSaleOrderFinalAction'
        ], $module);

        ###   Обработчики скидок EOF   ###

        /** сброс кеша малой корзины */
        $module = 'sale';
        static::initHandler('OnSaleBasketItemSaved', [
            self::class,
            'resetBasketCache'
        ], $module);
        static::initHandler('OnSaleBasketItemEntityDeleted', [
            self::class,
            'resetBasketCache'
        ], $module);

        /** предотвращение попадания отложенных товаров в заказ */
        static::initHandler('OnSaleBasketItemBeforeSaved', [
            self::class,
            'removeDelayedItems'
        ], $module);

        //TODO limit only by promo offer dates range
        /** добавление марок в заказ */
        static::initHandler('OnSaleOrderBeforeSaved', [
            self::class,
            'addMarksToOrderBasket'
        ], $module);

        /** генерация номера заказа */
        static::initHandlerCompatible('OnBeforeOrderAccountNumberSet', [
            self::class,
            'updateOrderAccountNumber'
        ], $module);
        static::initHandler('OnSaleOrderEntitySaved', [
            self::class,
            'unlockOrderTables'
        ], $module);

        /** отправка email */
        // новый заказ
        static::initHandler('OnSaleOrderEntitySaved', [
            self::class,
            'sendNewOrderMessage'
        ], $module);
        // смена платежной системы у заказа
        static::initHandler('OnSalePaymentEntitySaved', [
            self::class,
            'sendNewOrderMessage'
        ], $module);
        // оплата заказа
        static::initHandler('OnSaleOrderPaid', [
            self::class,
            'sendOrderPaymentMessage'
        ], $module);
        // отмена заказа
        static::initHandler('OnSaleOrderCanceled', [
            self::class,
            'sendOrderCancelMessage'
        ], $module);
        // смена статуса заказа
        static::initHandler('OnSaleStatusOrderChange', [
            self::class,
            'sendOrderStatusMessage'
        ], $module);

        /** отмена заказа, перешедшего в статус отмены */
        static::initHandler('OnSaleStatusOrderChange', [
            self::class,
            'cancelOrder'
        ], $module);

        /** очистка кеша заказа */
        static::initHandler('OnSaleOrderSaved', [
            self::class,
            'clearOrderCache'
        ], $module);

        /**
         * Сохранение имени пользователя
         */
        static::initHandler('OnSaleOrderEntitySaved', [
            self::class,
            'setNameAfterOrder'
        ], $module);

        /** обновление бонусного счета пользователя и бонусного процента пользователя */
        $module = 'main';
        static::initHandlerCompatible('OnAfterUserLogin', [
            self::class,
            'updateUserAccountBalance'
        ], $module);
        static::initHandlerCompatible('OnAfterUserAuthorize', [
            self::class,
            'updateUserAccountBalance'
        ], $module);
        static::initHandlerCompatible('OnAfterUserLoginByHash', [
            self::class,
            'updateUserAccountBalance'
        ], $module);

        /**
         * Забытая корзина
         */
        $module = 'sale';
        static::initHandler('OnSaleBasketItemSaved', [
            self::class,
            'disableForgotBasketReminder'
        ], $module);
        static::initHandler('OnSaleBasketItemEntityDeleted', [
            self::class,
            'disableForgotBasketReminder'
        ], $module);

        /**
         * Добавление марок в корзину
         */
        /*$module = 'sale';
        static::initHandler('OnSaleBasketSaved', [
            self::class,
            'addStampsToBasket'
        ], $module);*/
    }

    public static function updateUserAccountBalance(): void
    {
        try {
            /** @var MainTemplate $template */
            $template = MainTemplate::getInstance(BitrixApplication::getInstance()
                ->getContext());
            /** выполняем только при пользовательской авторизации(это аякс), либо из письма и обратных ссылок(это personal)
             *  так же чекаем что это не страница заказа
             */
            if (!$template->hasUserAuth()) {
                return;
            }
            $container = Application::getInstance()
                ->getContainer();
            $userService = $container->get(CurrentUserProviderInterface::class);
            $userAccountService = $container->get(UserAccountService::class);
            $user = $userService->getCurrentUser();
            [
                ,
                $bonus
            ] = $userAccountService->refreshUserBalance($user);
            $userService->refreshUserBonusPercent($user, $bonus);
        } catch (NotAuthorizedException $e) {
            // обработка не требуется
        } catch (Exception $e) {
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
            $order = $entity;
            if ($order->isCanceled()) {
                return;
            }
        } elseif ($entity instanceof Payment) {
            /** @var PaymentCollection $collection */
            $collection = $entity->getCollection();
            $order = $collection->getOrder();
        } else {
            return;
        }

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()
            ->getContainer()
            ->get(
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

        /** @var Order $order */
        $order = $event->getParameter('ENTITY');
        if ($order->isCanceled()) {
            return;
        }

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()
            ->getContainer()
            ->get(
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
     * @return EventResult
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     */
    public static function removeDelayedItems(BitrixEvent $event): EventResult
    {
        $basketItem = $event->getParameter('ENTITY');
        $result = new EventResult(EventResult::SUCCESS);
        if ($basketItem instanceof BasketItem) {
            /** @var BasketItemCollection $collection */
            $collection = $basketItem->getCollection();
            if ($collection->getOrderId() && $basketItem->isDelay()) {
                $result = new EventResult(EventResult::ERROR);
            }
        }

        return $result;
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

        // если свойств нет, то это удаление заказа
        if ($order->getPropertyCollection()
            ->isEmpty()) {
            return;
        }

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()
            ->getContainer()
            ->get(
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
        if ($order->isCanceled()) {
            return;
        }

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()
            ->getContainer()
            ->get(
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
     *
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @throws SystemException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws Exception
     */
    public static function cancelOrder(BitrixEvent $event): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

        // если свойств нет, то это удаление заказа
        if ($order->getPropertyCollection()
            ->isEmpty()) {
            return;
        }

        if (\in_array(
                $order->getField('STATUS_ID'),
                [
                    OrderStatus::STATUS_CANCEL_COURIER,
                    OrderStatus::STATUS_CANCEL_PICKUP
                ],
                true
            )
            && !$order->isCanceled()) {
            /** @var PaymentService $paymentService */
            $paymentService = Application::getInstance()
                ->getContainer()
                ->get(PaymentService::class);
            $paymentService->cancelPayment($order, 0, false);

            $order->setField('CANCELED', 'Y');
            $order->save();
        }
    }

    /**
     * @param $id
     * @param $type
     *
     * @return false|string
     */
    public static function updateOrderAccountNumber($id, $type)
    {
        $result = false;
        if (self::$isEventsDisable) {
            return $result;
        }

        if ($type === 'NUMBER') {
            try {
                /**
                 * @var array $connection
                 */
                $connection = BitrixApplication::getConnection();

                $connection->query('LOCK TABLE b_sale_order WRITE');
                /** ограничение сверху в запросе - для того, чтобы не захватывать заказы из манзаны */
                $maxNumber = $connection
                                 ->query('SELECT MAX(CAST(ACCOUNT_NUMBER AS UNSIGNED)) AS maxNumber FROM b_sale_order WHERE CAST(ACCOUNT_NUMBER AS UNSIGNED) < 9999999')
                                 ->fetch()['maxNumber'];
                $defaultNumber = Option::get('sale', 'account_number_data', 0);

                if ($defaultNumber) {
                    $result = (int)$defaultNumber > (int)$maxNumber ? $defaultNumber : ($maxNumber + 1);
                }
            } catch (Exception $e) {
                static::getLogger()
                    ->error(
                        sprintf(
                            'failed to set order %s account number: %s: %s',
                            $id,
                            \get_class($e),
                            $e->getMessage()
                        )
                    );
            }
        }

        return $result;
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ArgumentTypeException
     */
    public static function unlockOrderTables(BitrixEvent $event)
    {
        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

        if ($order->isCanceled()) {
            return;
        }

        try {
            BitrixApplication::getConnection()
                ->query('UNLOCK TABLES');
        } catch (Exception $e) {
            /** @noinspection NullPointerExceptionInspection */
            static::getLogger()
                ->error('failed to unlock order tables', [
                    'order' => $event->getParameter('ENTITY')
                        ->getId(),
                ]);
        }
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
    public static function setNameAfterOrder(BitrixEvent $event): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        $entity = $event->getParameter('ENTITY');

        if ($entity instanceof Order) {
            $order = $entity;

            if ($order->isCanceled()) {
                return;
            }
        } else {
            return;
        }

        $container = Application::getInstance()
            ->getContainer();
        $userService = $container->get(CurrentUserProviderInterface::class);

        if (!$userService->isAuthorized()) {
            return;
        }

        try {
            $currentUser = $userService->getCurrentUser();
        } catch (Exception $e) {
            return;
        }

        if (!$currentUser->getName()) {
            $orderService = $container->get(OrderService::class);
            $userRepository = $container->get(UserRepository::class);

            if ($orderService->isSubscribe($order) || $orderService->isManzanaOrder($order)) {
                // пропускаются заказы, созданные по подписке
                return;
            }

            $name = BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), 'NAME');

            if ($name) {
                $currentUser->setName($name->getValue());
            }

            try {
                $userRepository->update($currentUser);
            } catch (Exception $e) {
                self::getLogger()
                    ->error(\sprintf(
                        'User name update error: %s',
                        $e->getMessage()
                    ));
            }
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ArgumentException
     * @throws ArgumentTypeException
     * @throws FailedToUpdateException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws \LogicException
     */
    public static function disableForgotBasketReminder(BitrixEvent $event)
    {
        $entity = $event->getParameter('ENTITY');
        $userId = null;
        /** @var CurrentUserProviderInterface $currentUserProvider */
        $currentUserProvider = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
        if ($entity instanceof BasketItem) {
            try {
                $userId = $currentUserProvider->getCurrentUserId();
            } catch (NotAuthorizedException $e) {
            }
        }

        if ($userId) {
            /** @var ForgotBasketService $forgotBasketService */
            $forgotBasketService = Application::getInstance()->getContainer()->get(ForgotBasketService::class);
            $forgotBasketService->disableUserTasks($userId);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ArgumentException
     * @throws ArgumentTypeException
     * @throws \RuntimeException
     */
    public static function resetBasketCache(BitrixEvent $event)
    {
        $entity = $event->getParameter('ENTITY');
        if ($entity instanceof BasketItem) {
            TaggedCacheHelper::clearManagedCache(['basket:' . $entity->getFUserId()]);
        }
    }

    /**
     * @param $adminMenu
     * @param $moduleMenu
     */
    public static function addRetailRocketOrderReportToAdminMenu(&$adminMenu, &$moduleMenu)
    {
        foreach ($moduleMenu as $i => $menuItem) {
            if ($menuItem['parent_menu'] === 'global_menu_store' &&
                $menuItem['items_id'] === 'menu_sale_stat'
            ) {
                $moduleMenu[$i]['items'][] = [
                    'text' => 'Отчет по заказам для RetailRocket',
                    'title' => 'Отчет по заказам для RetailRocket',
                    'url' => '/bitrix/admin/fourpaws_retail_rocket_orders_report.php?lang=' . LANG,
                    'more_url' => ''
                ];
            }
        }
    }

    /**
     * @todo Не просто добавлять в корзину марку, а перерасчитывать их количество (удалять/добавлять)
     * @todo добавлять марки и при первичном добавлении товара в корзину (до того, как меняем количество). Сейчас марки сразу не добавляются
     * @todo Скрыть марки из вывода в корзине
     * @todo Обработка Exception
     * @todo Марки отправляются в SAP, только если перезагрузить страницу корзины перед тем, как начать оформление
     *
     * @param BitrixEvent $event
     */
//    public static function addStampsToBasket(BitrixEvent $event): void
//    {
//        $piggyBankService = ////////////////;
//        try {
//            /** @var Basket $basket */
//            $basket = $event->getParameter('ENTITY');
//            if ($basket instanceof Basket) {
//                $stampsQuantity = 1; //TODO calculate
//
//                /** @var BasketService $basketService */
//                $basketService = Application::getInstance()->getContainer()->get(BasketService::class);
//                $basketItem = $basketService->addOfferToBasket(
//                    $piggyBankService->getVirtualMarkId(),
//                    //$piggyBankService->getPhysicalMarkId(),
//                    $stampsQuantity,
//                    [],
//                    true,
//                    $basket
//                );
//            }
//        } catch (\Exception $e) {
//            //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/_dev_e123123123.txt', print_r($e->getTrace(), true), FILE_APPEND); //TODO: delete
//            //TODO Обработка Exception
//        }
//    }

    /**
     * Добавляет марки
     * @todo Сделать выборку по категориям товаров, участвующих в акции, и больше бонусов за товары ветаптеки по константе MARKS_PER_RATE_VETAPTEKA
     * @todo 3 раза отрабатывает в момент регистрации заказа. Разобраться, ограничить применение одним разом
     * @todo вынести в Service
     *
     * @param BitrixEvent $event
     */
    public function addMarksToOrderBasket(BitrixEvent $event): void
    {
        global $USER;
        if (!$USER->IsAdmin())
        {
            return;
        }

        try {
            /** @var Order $order */
            $order = $event->getParameter('ENTITY');

            /** подсчитываем марки только при создании нового заказе */
            if (!$order->isNew()) {
                return;
            }

            /** @var PiggyBankService $piggyBankService */
            $piggyBankService = Application::getInstance()->getContainer()->get('piggy_bank.service');

            $manzanaNumberValue = '';
            foreach ($order->getPropertyCollection()->getArray()['properties'] as $prop) {
                if ($prop['CODE'] === 'MANZANA_NUMBER') {
                    $manzanaNumberValue = $prop['VALUE'][0];
                    break;
                }
            }
            if (strpos($manzanaNumberValue, 'NEW') !== false) return; // если заказ из Битрикса (добавлено слово NEW в конце)


            if ($order instanceof Order) {
                /** @var BasketService $basketService */
                $basketService = Application::getInstance()->getContainer()->get(BasketService::class);

                $basket = $order->getBasket();
                $items = $basket->getOrderableItems();

                /** @var BasketItem $item */
                $sum = 0;
                foreach ($items as $item)
                {
                    if (in_array($item->getProductId(), $piggyBankService->getMarksIds(), false))
                    {
                        $basketService->deleteOfferFromBasket($item->getId());
                        continue;
                    }
                    $sum += $item->getPrice() * $item->getQuantity();
                }

                $marksToAdd = floor($sum / $piggyBankService::MARK_RATE);

                $basket->save();

                if ($marksToAdd > 0)
                {
                    $basketItem = $basketService->addOfferToBasket(
                        $piggyBankService->getVirtualMarkId(),
                        //$piggyBankService->getPhysicalMarkId(),
                        $marksToAdd,
                        [],
                        true,
                        $basket
                    );
                }

                return;
            }
        } catch (\Exception $e) {
            $logger = LoggerFactory::create('piggyBank');
            $logger->critical('failed to add PiggyBank marks for order: ' . $e->getMessage());
        }
    }
}
