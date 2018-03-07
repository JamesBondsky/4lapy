<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Sale\Order;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\External\Exception\ExpertsenderServiceException;
use FourPaws\External\ExpertsenderService;
use FourPaws\External\SmsService;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine;

class NotificationService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
     * NotificationService constructor.
     *
     * @param OrderService $orderService
     * @param SmsService $smsService
     * @param StoreService $storeService
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
            $this->renderer = $container->get('templating');
        } elseif ($container->has('twig')) {
            $this->renderer = $container->get('twig');
        } else {
            throw new \LogicException(
                'You can not use the "render" method if the Templating Component or the Twig Bundle are not available.'
            );
        }

        $this->setLogger(LoggerFactory::create('sale_notification'));
    }

    /**
     * @param Order $order
     */
    public function sendNewOrderMessage(Order $order)
    {
        try {
            $this->orderService->getOnlinePayment($order);

            return;
        } catch (NotFoundException $e) {
            // заказ не должен быть с оплатой "онлайн"
        }

        try {
            $this->emailService->sendOrderNewEmail($order);
        } catch (ExpertsenderServiceException $e) {
            $this->logger->error($e->getMessage());
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
    }

    /**
     * @param Order $order
     */
    public function sendOrderPaymentMessage(Order $order)
    {
        try {
            $payment = $this->orderService->getOnlinePayment($order);
        } catch (NotFoundException $e) {
            return;
        }

        if (!$payment->isPaid()) {
            return;
        }

        try {
            $this->emailService->sendOrderNewEmail($order);
        } catch (ExpertsenderServiceException $e) {
            $this->logger->error($e->getMessage());
        }
        $parameters = $this->getOrderData($order);
        $this->sendSms('FourPawsSaleBundle:Sms:order.paid.html.php', $parameters, true);
    }

    /**
     * @param Order $order
     */
    public function sendOrderCancelMessage(Order $order)
    {
        if (!$order->isCanceled()) {
            return;
        }

        $parameters = $this->getOrderData($order);

        $this->sendSms(
            'FourPawsSaleBundle:Sms:order.canceled.html.php',
            $parameters
        );
    }

    /**
     * @param Order $order
     */
    public function sendOrderStatusMessage(Order $order)
    {
        if ($order->isCanceled()) {
            return;
        }

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
                if ($this->emailService->sendOrderCompleteEmail($order)) {
                    $this->orderService->setOrderPropertyByCode($order, 'COMPLETE_MESSAGE_SENT', 'Y');
                    $order->save();
                }
            } catch (ExpertsenderServiceException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        if ($smsTemplate) {
            $this->sendSms(
                $smsTemplate,
                $parameters
            );
        }
    }

    /**
     * @param string $tpl
     * @param array $parameters
     * @param bool $immediate
     */
    protected function sendSms(string $tpl, array $parameters, bool $immediate = false)
    {
        if (empty($parameters)) {
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
            $result['dcDelivery'] = $properties['REGION_COURIER_FROM_DC'] === BitrixUtils::BX_BOOL_TRUE ? true : false;
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
                    'address'  => $shop->getAddress(),
                    'schedule' => $shop->getSchedule(),
                ];
            }
        } catch (NotFoundException $e) {
            $this->logger->error($e->getMessage());
        } catch (StoreNotFoundException $e) {
            $this->logger->error($e->getMessage());
            return [];
        }

        return $result;
    }
}
