<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Order;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\External\Exception\ExpertsenderServiceException;
use FourPaws\External\ExpertsenderService;
use FourPaws\External\SmsService;
use FourPaws\StoreBundle\Service\StoreService;
use Psr\Log\LoggerAwareInterface;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine;

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
     * @throws ApplicationCreateException
     */
    public function __construct(
        OrderService $orderService,
        SmsService $smsService,
        StoreService $storeService,
        ExpertsenderService $emailService
    ) {
        $this->orderService = $orderService;
        $this->smsService = $smsService;
        $this->storeService = $storeService;
        $this->emailService = $emailService;

        $container = Application::getInstance()->getContainer();
        if ($container->has('templating')) {
            /** @noinspection MissingService */
            $this->renderer = $container->get('templating');
        } elseif ($container->has('twig')) {
            $this->renderer = $container->get('twig');
        } else {
            throw new \LogicException(
                'You can not use the "render" method if the Templating Component or the Twig Bundle are not available.'
            );
        }

        $this->withLogName('sale_notification');
    }

    /**
     * @param Order $order
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @throws SystemException
     */
    public function sendNewOrderMessage(Order $order): void
    {
        if (static::$isSending) {
            return;
        }

        /**
         * Заказ не должен быть с оплатой "онлайн"
         */
        if ($this->orderService->getOrderPaymentType($order) === OrderService::PAYMENT_ONLINE) {
            return;
        }

        if ($this->getOrderMessageFlag($order, 'NEW_ORDER_MESSAGE_SENT') === BitrixUtils::BX_BOOL_TRUE) {
            return;
        }

        static::$isSending = true;

        try {
            if ($transactionId = $this->emailService->sendOrderNewEmail($order)) {
                $this->setOrderMessageFlag($order, 'NEW_ORDER_MESSAGE_SENT');
            }
            $this->logMessage($order, $transactionId);
        } catch (ExpertsenderServiceException $e) {
            $this->log()->error($e->getMessage());
        }

        $smsTemplate = null;
        $parameters = $this->getOrderData($order);
        switch ($parameters['deliveryCode']) {
            case DeliveryService::INNER_DELIVERY_CODE:
                $smsTemplate = 'FourPawsSaleBundle:Sms:order.new.delivery.inner.html.php';
                break;
            case DeliveryService::INNER_PICKUP_CODE:
                if ($parameters['dcDelivery']) {
                    $smsTemplate = 'FourPawsSaleBundle:Sms:order.new.pickup.shop.html.php';
                } else {
                    $smsTemplate = 'FourPawsSaleBundle:Sms:order.new.pickup.dc.html.php';
                }
                break;
            case DeliveryService::DPD_DELIVERY_CODE:
            case DeliveryService::DPD_PICKUP_CODE:
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
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @throws ApplicationCreateException
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
        if (!$this->orderService->getOrderPaymentType($order) === OrderService::PAYMENT_ONLINE) {
            return;
        }

        if (!$this->orderService->getOrderPayment($order)->isPaid()) {
            return;
        }

        if ($this->getOrderMessageFlag($order, 'NEW_ORDER_MESSAGE_SENT') === BitrixUtils::BX_BOOL_TRUE) {
            return;
        }

        static::$isSending = true;

        try {
            if ($transactionId = $this->emailService->sendOrderNewEmail($order)) {
                $this->setOrderMessageFlag($order, 'NEW_ORDER_MESSAGE_SENT');
            }
            $this->logMessage($order, $transactionId);
        } catch (ExpertsenderServiceException $e) {
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
     * @throws ApplicationCreateException
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
            case OrderService::STATUS_ISSUING_POINT:
                if ($parameters['deliveryCode'] === DeliveryService::INNER_PICKUP_CODE) {
                    if ($parameters['dcDelivery']) {
                        $smsTemplate = 'FourPawsSaleBundle:Sms:order.status.issuingPoint.dc.html.php';
                    } else {
                        $smsTemplate = 'FourPawsSaleBundle:Sms:order.status.issuingPoint.shop.html.php';
                    }
                }
                break;
            case OrderService::STATUS_DELIVERING:
                $sendCompleteEmail = true;
                if ($parameters['deliveryCode'] === DeliveryService::INNER_DELIVERY_CODE) {
                    $smsTemplate = 'FourPawsSaleBundle:Sms:order.status.delivering.html.php';
                }
                break;
            case OrderService::STATUS_DELIVERED:
                $sendCompleteEmail = true;
                if ($parameters['deliveryCode'] === DeliveryService::INNER_DELIVERY_CODE) {
                    $smsTemplate = 'FourPawsSaleBundle:Sms:order.status.delivered.html.php';
                }
                break;
        }

        if ($sendCompleteEmail && $this->orderService->getOrderPropertyByCode(
                $order,
                'COMPLETE_MESSAGE_SENT'
            )->getValue() !== BitrixUtils::BX_BOOL_TRUE
        ) {
            try {
                if ($transactionId = $this->emailService->sendOrderCompleteEmail($order)) {
                    $this->setOrderMessageFlag($order, 'COMPLETE_MESSAGE_SENT');
                }
                $this->logMessage($order, $transactionId);
            } catch (ExpertsenderServiceException $e) {
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
                    'REGION_COURIER_FROM_DC',
                    'PHONE',
                    'EMAIL',
                    'DELIVERY_DATE',
                    'DELIVERY_PLACE_CODE',
                ]
            );

            $result['accountNumber'] = $order->getField('ACCOUNT_NUMBER');
            $result['dcDelivery'] = $properties['REGION_COURIER_FROM_DC'] === BitrixUtils::BX_BOOL_TRUE;
            $result['phone'] = $properties['PHONE'];
            $result['email'] = $properties['EMAIL'];
            $result['price'] = $order->getPrice();
            $result['deliveryDate'] = \DateTime::createFromFormat(
                'd.m.Y',
                $properties['DELIVERY_DATE']
            );
            $result['deliveryCode'] = $this->orderService->getOrderDeliveryCode($order);

            if ($result['deliveryCode'] === DeliveryService::INNER_PICKUP_CODE) {
                $shop = $this->storeService->getByXmlId(
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
}
