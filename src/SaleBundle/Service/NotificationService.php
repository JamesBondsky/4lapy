<?php

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\External\ExpertsenderService;
use FourPaws\External\SmsService;
use FourPaws\SaleBundle\Exception\NotFoundException;
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

        $parameters = $this->getOrderData($order);

        $this->emailService->sendOrderNewEmail($order);

        $smsTemplate = null;
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

        $this->emailService->sendOrderNewEmail($order);
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

        $status = $order->getField('STATUS_CODE');
        $parameters = $this->getOrderData($order);

        $smsTemplate = null;
        switch ($status) {
            case OrderService::STATUS_ISSUING_POINT:
                if ($parameters['deliveryCode'] === DeliveryService::INNER_PICKUP_CODE) {
                    if ($parameters['dcDelivery']) {
                        $smsTemplate = 'order.status.issuingPoint.dc.html.php';
                    } else {
                        $smsTemplate = 'order.status.issuingPoint.shop.html.php';
                    }
                }
                break;
            case OrderService::STATUS_DELIVERING:
                if ($parameters['deliveryCode'] === DeliveryService::INNER_DELIVERY_CODE) {
                    $smsTemplate = 'order.status.delivering.html.php';
                }
                $this->emailService->sendOrderCompleteEmail($order);
                break;
            case OrderService::STATUS_DELIVERED:
                if ($parameters['deliveryCode'] === DeliveryService::INNER_DELIVERY_CODE) {
                    $smsTemplate = 'order.status.delivered.html.php';
                }
                /** @todo проверять, что письмо уже было отправлено в статусе "Исполнен" */
                $this->emailService->sendOrderCompleteEmail($order);
                break;
        }

        if ($smsTemplate) {
            $this->sendSms(
                'FourPawsSaleBundle:Sms:order.canceled.html.php',
                $parameters
            );
        }
    }

    /**
     * @param string $tpl
     * @param array $parameters
     * @param bool $immediate
     */
    protected function sendSms(string $tpl, array $parameters = [], bool $immediate = false)
    {
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
            $result['accountNumber'] = $order->getField('ACCOUNT_NUMBER');
            $result['dcDelivery'] = $this->orderService->getOrderPropertyByCode($order, 'REGION_COURIER_FROM_DC')
                                                       ->getValue() === BitrixUtils::BX_BOOL_TRUE ? true : false;
            $result['phone'] = $this->orderService->getOrderPropertyByCode($order, 'PHONE')
                                                  ->getValue();
            $result['email'] = $this->orderService->getOrderPropertyByCode($order, 'EMAIL')
                                                  ->getValue();
            $result['price'] = $order->getPrice();
            $result['deliveryDate'] = \DateTime::createFromFormat(
                'd.m.Y',
                $this->orderService->getOrderPropertyByCode($order, 'DELIVERY_DATE')
                                   ->getValue()
            );
            $result['deliveryCode'] = $this->orderService->getOrderDeliveryCode($order);

            if ($result['deliveryCode'] === DeliveryService::INNER_PICKUP_CODE) {
                $shop = $this->storeService->getByXmlId(
                    $this->orderService->getOrderPropertyByCode($order, 'DELIVERY_PLACE_CODE')->getValue()
                );
                $result['shop'] = [
                    'address'  => $shop->getAddress(),
                    'schedule' => $shop->getSchedule(),
                ];
            }
        } catch (NotFoundException $e) {
            $this->logger->error($e->getMessage());
        }

        return $result;
    }
}
