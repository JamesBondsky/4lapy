<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Order;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\External\Exception\ExpertsenderEmptyEmailException;
use FourPaws\External\Exception\ExpertsenderServiceException;
use FourPaws\External\ExpertsenderService;
use FourPaws\External\SmsService;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Entity\OrderSubscribeCopyParams;
use FourPaws\SaleBundle\Enum\OrderStatus;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Exception\NotFoundException;
use Psr\Log\LoggerAwareInterface;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine;

/**
 * Class NotificationService
 *
 * @package FourPaws\SaleBundle\Service
 */
class NotificationService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var SmsService
     */
    protected $smsService;

    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * @var DelegatingEngine
     */
    protected $renderer;

    /**
     * @var ExpertsenderService
     */
    protected $emailService;

    /**
     * Для предотвращения зацикливания отправки писем
     *
     * @var bool
     */
    protected static $isSending = false;

    /**
     * NotificationService constructor.
     * @param OrderService $orderService
     * @param SmsService $smsService
     * @param StoreService $storeService
     * @param ExpertsenderService $emailService
     *
     * @throws ApplicationCreateException
     */
    public function __construct(
        OrderService $orderService,
        SmsService $smsService,
        StoreService $storeService,
        ExpertsenderService $emailService
    )
    {
        $this->orderService = $orderService;
        $this->smsService = $smsService;
        $this->storeService = $storeService;
        $this->emailService = $emailService;

        $container = Application::getInstance()->getContainer();
        /** @noinspection MissingService */
        $this->renderer = $container->get('templating');

        $this->withLogName('sale_notification');
    }

    /**
     * @param Order $order
     *
     * @throws ObjectNotFoundException
     * @throws SystemException
     */
    public function sendNewOrderMessage(Order $order): void
    {
        if ($this->orderService->isSubscribe($order)) {
            // Для заказов, созданных по подписке, свои триггеры
            $this->sendOrderSubscribeOrderNewMessage($order);
            return;
        }

        if (static::$isSending) {
            return;
        }

        /**
         * Заказ не должен быть с оплатой "онлайн"
         */
        if ($this->orderService->isOnlinePayment($order)) {
            return;
        }

        if ($this->getOrderMessageFlag($order, 'NEW_ORDER_MESSAGE_SENT') === BitrixUtils::BX_BOOL_TRUE) {
            return;
        }

        static::$isSending = true;

        $this->setOrderMessageFlag($order, 'NEW_ORDER_MESSAGE_SENT');
        try {
            $transactionId = $this->emailService->sendOrderNewEmail($order);
            if ($transactionId) {
                $this->logMessage($order, $transactionId);
            }
        } catch (ExpertsenderEmptyEmailException $e) {
            $this->log()->info('не установлен email для отправки у заказа - '.$order->getId());
        } catch (ExpertsenderServiceException|\Exception $e) {
            $this->log()->error($e->getMessage());
        }

        $smsTemplate = null;
        $parameters = $this->getOrderData($order);
        switch (true) {
            case $parameters['isOneClick']:
                $smsTemplate = 'FourPawsSaleBundle:Sms:order.new.one_click.html.php';
                break;
            case $parameters['deliveryCode'] === DeliveryService::INNER_DELIVERY_CODE:
                $smsTemplate = 'FourPawsSaleBundle:Sms:order.new.delivery.inner.html.php';
                break;
            case $parameters['deliveryCode'] === DeliveryService::INNER_PICKUP_CODE:
                if ($parameters['dcDelivery']) {
                    $smsTemplate = 'FourPawsSaleBundle:Sms:order.new.pickup.dc.html.php';
                } else {
                    $smsTemplate = 'FourPawsSaleBundle:Sms:order.new.pickup.shop.html.php';
                }
                break;
            case $parameters['deliveryCode'] === DeliveryService::DPD_DELIVERY_CODE:
            case $parameters['deliveryCode'] === DeliveryService::DPD_PICKUP_CODE:
                $smsTemplate = 'FourPawsSaleBundle:Sms:order.new.delivery.dpd.html.php';
                break;
        }

        if ($smsTemplate) {
            $this->sendSms($smsTemplate, $parameters, true);
        }

        $this->sendNewUserSms($parameters);
        static::$isSending = false;
    }

    /**
     * @param Order $order
     *
     * @throws ObjectNotFoundException
     * @throws SystemException
     */
    public function sendOrderPaymentMessage(Order $order): void
    {
        if (static::$isSending) {
            return;
        }

        /**
         * Заказ должен быть с оплатой "онлайн"
         */
        if (!$this->orderService->isOnlinePayment($order)) {
            return;
        }

        if (!$this->orderService->getOrderPayment($order)->isPaid()) {
            return;
        }

        if ($this->getOrderMessageFlag($order, 'NEW_ORDER_MESSAGE_SENT') === BitrixUtils::BX_BOOL_TRUE) {
            return;
        }

        static::$isSending = true;

        $this->setOrderMessageFlag($order, 'NEW_ORDER_MESSAGE_SENT');
        try {
            $transactionId = $this->emailService->sendOrderNewEmail($order);
            if ($transactionId) {
                $this->logMessage($order, $transactionId);
            }
        } catch (ExpertsenderEmptyEmailException $e) {
            $this->log()->info('не установлен email для отправки у заказа - '.$order->getId());
        } catch (ExpertsenderServiceException|\Exception $e) {
            $this->log()->error($e->getMessage());
        }
        $parameters = $this->getOrderData($order);
        $this->sendSms('FourPawsSaleBundle:Sms:order.paid.html.php', $parameters, true);
        $this->sendNewUserSms($parameters);
        static::$isSending = false;
    }

    /**
     * @param Order $order
     */
    public function sendOrderCancelMessage(Order $order): void
    {
        if (static::$isSending) {
            return;
        }

        if (!$order->isCanceled()) {
            return;
        }

        static::$isSending = true;

        $parameters = $this->getOrderData($order);

        $this->sendSms(
            'FourPawsSaleBundle:Sms:order.canceled.html.php',
            $parameters
        );
        static::$isSending = false;
    }

    /**
     * @param Order $order
     */
    public function sendOrderStatusMessage(Order $order): void
    {
        if (static::$isSending) {
            return;
        }

        if ($order->isCanceled()) {
            return;
        }

        static::$isSending = true;

        $status = $order->getField('STATUS_ID');
        $parameters = $this->getOrderData($order);

        $smsTemplate = null;
        $sendCompleteEmail = false;
        switch ($status) {
            case OrderStatus::STATUS_ISSUING_POINT:
                if ($parameters['deliveryCode'] === DeliveryService::INNER_PICKUP_CODE) {
                    if ($parameters['dcDelivery']) {
                        $smsTemplate = 'FourPawsSaleBundle:Sms:order.status.issuingPoint.dc.html.php';
                    } else {
                        $smsTemplate = 'FourPawsSaleBundle:Sms:order.status.issuingPoint.shop.html.php';
                    }
                }
                break;
            case OrderStatus::STATUS_DELIVERING:
                $sendCompleteEmail = true;
                if ($parameters['deliveryCode'] === DeliveryService::INNER_DELIVERY_CODE) {
                    $smsTemplate = 'FourPawsSaleBundle:Sms:order.status.delivering.html.php';
                }
                break;
            case OrderStatus::STATUS_DELIVERED:
                $sendCompleteEmail = true;
                if ($parameters['deliveryCode'] === DeliveryService::INNER_DELIVERY_CODE) {
                    $smsTemplate = 'FourPawsSaleBundle:Sms:order.status.delivered.html.php';
                }
                break;
        }

        if ($sendCompleteEmail &&
            $this->getOrderMessageFlag($order, 'COMPLETE_MESSAGE_SENT') !== BitrixUtils::BX_BOOL_TRUE
        ) {
            try {
                $transactionId = $this->emailService->sendOrderCompleteEmail($order);
                $this->setOrderMessageFlag($order, 'COMPLETE_MESSAGE_SENT');
                if ($transactionId) {
                    $this->logMessage($order, $transactionId);
                }
            } catch (ExpertsenderEmptyEmailException $e) {
                $this->log()->info('не установлен email для отправки у заказа - '.$order->getId());
            } catch (ExpertsenderServiceException|\Exception $e) {
                $this->log()->error($e->getMessage());
            }
        }

        if ($smsTemplate) {
            $this->sendSms(
                $smsTemplate,
                $parameters
            );
        }

        static::$isSending = false;
    }

    /**
     * @param string $tpl
     * @param array $parameters
     * @param bool $immediate
     */
    protected function sendSms(string $tpl, array $parameters, bool $immediate = false): void
    {
        if (empty($parameters) || !$parameters['phone']) {
            return;
        }

        $text = $this->renderer->render($tpl, $parameters);

        if ($immediate) {
            $this->smsService->sendSmsImmediate($text, $parameters['phone']);
        } else {
            $this->smsService->sendSms($text, $parameters['phone']);
        }
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    protected function getOrderData(Order $order): array
    {
        $result = [];

        try {
            $properties = $this->orderService->getOrderPropertiesByCode(
                $order,
                [
                    'SHIPMENT_PLACE_CODE',
                    'PHONE',
                    'EMAIL',
                    'DELIVERY_DATE',
                    'DELIVERY_PLACE_CODE',
                    'IS_FAST_ORDER'
                ]
            );

            $result['accountNumber'] = $order->getField('ACCOUNT_NUMBER');
            $result['dcDelivery'] = (bool)$properties['SHIPMENT_PLACE_CODE'];
            $result['phone'] = $properties['PHONE'];
            $result['email'] = $properties['EMAIL'];
            $result['price'] = $order->getPrice();
            $result['bonusSum'] = $order->getPaymentCollection()->getInnerPayment()
                ? $order->getPaymentCollection()->getInnerPayment()->getSum()
                : 0;
            $result['deliveryDate'] = \DateTime::createFromFormat(
                'd.m.Y',
                $properties['DELIVERY_DATE']
            );
            $result['deliveryCode'] = $this->orderService->getOrderDeliveryCode($order);
            $result['isOneClick'] = $properties['IS_FAST_ORDER'] === 'Y';

            if (!$result['isOneClick'] &&
                ($result['deliveryCode'] === DeliveryService::INNER_PICKUP_CODE)
            ) {
                $shop = $this->storeService->getStoreByXmlId(
                    $properties['DELIVERY_PLACE_CODE']
                );
                $result['shop'] = [
                    'address' => $shop->getAddress(),
                    'schedule' => $shop->getScheduleString(),
                ];
            }
        } catch (\Exception $e) {
            $this->log()->error($e->getMessage());
            $result = [];
        }

        return $result;
    }

    /**
     * @param array $parameters
     */
    protected function sendNewUserSms(array $parameters): void
    {
        if (!isset($_SESSION['NEW_USER']) || empty($_SESSION['NEW_USER'])) {
            return;
        }

        $parameters['login'] = $_SESSION['NEW_USER']['LOGIN'];
        $parameters['password'] = $_SESSION['NEW_USER']['PASSWORD'];
        $this->sendSms('FourPawsSaleBundle:Sms:order.new.user.html.php', $parameters, true);
        unset($_SESSION['NEW_USER']);
    }

    /**
     * @param Order $order
     * @param int $transactionId
     */
    protected function logMessage(Order $order, int $transactionId): void
    {
        $email = $this->orderService->getOrderPropertyByCode($order, 'EMAIL')->getValue();

        $this->log()->notice(
            sprintf(
                'message %s for order %s sent successfully to %s',
                $transactionId,
                $order->getId(),
                $email
            )
        );
    }

    /**
     * @param Order $order
     * @param string $code
     *
     * @return string
     */
    protected function getOrderMessageFlag(Order $order, string $code): string
    {
        $propValue = $this->orderService->getOrderPropertyByCode(
            $order,
            $code
        )->getValue();

        return ($propValue === BitrixUtils::BX_BOOL_TRUE) ? $propValue : BitrixUtils::BX_BOOL_FALSE;
    }

    /**
     * @param Order $order
     * @param string $code
     */
    protected function setOrderMessageFlag(Order $order, string $code): void
    {
        $this->orderService->setOrderPropertyByCode($order, $code, 'Y');
        try {
            $order->save();
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to update order property: %s', $e->getMessage()), [
                'property' => $code,
                'order' => $order->getId()
            ]);
        }
    }

    /**
     * @param Order $order
     * @return string
     */
    protected function getOrderPhone(Order $order): string
    {
        $value = '';
        try {
            $propValue = $this->orderService->getOrderPropertyByCode($order, 'PHONE');
            $value = trim($propValue->getValue());
        } catch (\Exception $e) {
            // просто вернем пустую строку
        }

        return $value;
    }

    /**
     * Отправка уведомления об автоматической отмене подписки (админам)
     *
     * @param OrderSubscribe $orderSubscribe
     * @throws ApplicationCreateException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws \Exception
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     * @throws \FourPaws\PersonalBundle\Exception\NotFoundException
     */
    public function sendAutoUnsubscribeOrderMessage(OrderSubscribe $orderSubscribe): void
    {
        $order = $orderSubscribe->getOrder()->getBitrixOrder();
        $subscribeDateCreate = $orderSubscribe->getDateCreate();
        $user = $orderSubscribe->getUser();
        // 30.03.2018: Канал уведомления (email или sms), триггер и текст ожидаем от 4 Лап.
        // 06.04.2018: Просто отправка письма, без ES, средствами системы
        $fields = [
            'ORDER_ID' => $order->getId(),
            'ACCOUNT_NUMBER' => $order->getField('ACCOUNT_NUMBER'),
            'SUBSCRIBE_ID' => $orderSubscribe->getId(),
            'SUBSCRIBE_DATE' => $subscribeDateCreate ? $subscribeDateCreate->format('d.m.Y') : '',
            'USER_ID' => $order->getUserId(),
            'USER_NAME' => $user->getName(),
            'USER_FULL_NAME' => $user->getFullName(),
            'USER_EMAIL' => $user->getEmail(),
        ];

        \CEvent::SendImmediate(
            '4PAWS_ORDER_SUBSCRIBE_AUTO_UNSUBSCRIBE',
            's1',
            $fields
        );
    }

    /**
     * Оформлена подписка на доставку
     *
     * @param OrderSubscribe $orderSubscribe
     */
    public function sendOrderSubscribedMessage(OrderSubscribe $orderSubscribe): void
    {
        try {
            $this->emailService->sendOrderSubscribedEmail($orderSubscribe);
        } catch (ExpertsenderEmptyEmailException $e) {
            $this->log()->info('не установлен email для отправки у заказа - '.$orderSubscribe->getId());
        } catch (ExpertsenderServiceException|\Exception $e) {
            $this->log()->error($e->getMessage());
        }
    }

    /**
     * Информация о предстоящем заказе по подписке (только что созданном)
     *
     * @param Order $order
     */
    public function sendOrderSubscribeOrderNewMessage(Order $order): void
    {
        try {
            $this->emailService->sendOrderSubscribeOrderNewEmail($order);
        } catch (ExpertsenderEmptyEmailException $e) {
            $this->log()->info('не установлен email для отправки у заказа - '.$order->getId());
        } catch (ExpertsenderServiceException|\Exception $e) {
            $this->log()->error($e->getMessage());
        }
    }

    /**
     * Информация о предстоящей доставке заказа по подписке (за N дней до доставки)
     *
     * @param OrderSubscribeCopyParams $copyParams
     */
    public function sendOrderSubscribeUpcomingDeliveryMessage(OrderSubscribeCopyParams $copyParams): void
    {
        try {
            $deliveryDate = $copyParams->getDeliveryDate();
            // дата доставки заказа с учетом уже возможно созданного заказа
            $realDeliveryDate = $copyParams->getRealDeliveryDate();

            $smsEventName = 'orderSubscribeUpcomingDelivery';
            $smsEventKey = $copyParams->getOriginOrderId();
            $smsEventKey .= '~' . $deliveryDate->format('d.m.Y');
            $smsEventKey .= '~' . $realDeliveryDate->format('d.m.Y');
            if (!$this->smsService->isAlreadySent($smsEventName, $smsEventKey)) {
                $parameters = [];
                $parameters['phone'] = '';
                $parameters['periodDays'] = $copyParams->getOrderSubscribeService()->getDeliveryDateUpcomingDays(
                    $realDeliveryDate,
                    $copyParams->getCurrentDate()
                );
                if ($parameters['periodDays'] >= 0) {
                    $copyOrder = $copyParams->getCopyOrder();
                    if ($copyOrder) {
                        $parameters['phone'] = $this->getOrderPhone($copyOrder);
                    }
                    if ($parameters['phone'] === '') {
                        $parameters['phone'] = $copyParams->getOrderSubscribe()->getUser()->getPersonalPhone();
                    }

                    $smsTemplate = 'FourPawsSaleBundle:Sms:order.subscribe.upcoming.delivery.html.php';
                    $this->sendSms($smsTemplate, $parameters);
                    $this->smsService->markAlreadySent($smsEventName, $smsEventKey);
                }
            }
        } catch (\Exception $exception) {
            $this->log()->error($exception->getMessage());
        }
    }
}
