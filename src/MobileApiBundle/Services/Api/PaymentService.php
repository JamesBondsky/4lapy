<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\Payment;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\PersonalBundle\Service\OrderService as PersonalOrderService;
use FourPaws\SaleBundle\Service\PaymentService as AppPaymentService;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;
use FourPaws\SapBundle\Exception\PaymentException;

class PaymentService
{
    use LazyLoggerAwareTrait;

    /**
     * @var PersonalOrderService
     */
    private $personalOrderService;

    /**
     * @var AppPaymentService
     */
    private $appPaymentService;


    public function __construct(
        PersonalOrderService $personalOrderService,
        AppPaymentService $appPaymentService
    )
    {
        $this->personalOrderService = $personalOrderService;
        $this->appPaymentService = $appPaymentService;
    }

    /**
     * @param int $orderId
     * @return string
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     * @throws \Exception
     */
    public function getPaymentUrl(int $orderId): string
    {
        $order = $this->personalOrderService->getOrderById($orderId);
        $bitrixOrder = $order->getBitrixOrder();
        if (!$order) {
            throw new NotFoundException("Заказ ID $orderId не найден");
        }

        if (!$bitrixOrder->getPaymentCollection()->count()) {
            throw new \Exception("У заказа ID $orderId не указана платежная система");
        }



        $url = new Uri('');

        $paymentItem = null;
        foreach ($bitrixOrder->getPaymentCollection() as $payment) {
            if ($payment->isInner()) {
                continue;
            }

            if ($payment->getPaySystem()->getField('CODE') === OrderPayment::PAYMENT_ONLINE) {
                $paymentItem = $payment;
            }
        }

        if (!$paymentItem) {
            throw new \Exception("У заказа ID $orderId не выбран способ оплаты - онлайн");
        }

        $service = PaySystemManager::getObjectById($paymentItem->getPaymentSystemId());

        if ($service) {
            // $context = BitrixApp::getInstance()->getContext();
            $isOk = false;
            try {
                // $_SESSION['ORDER_PAYMENT_URL'] = $this->getPaymentUrl();
                $result = $service->initiatePay(
                    $paymentItem// ,
                    // $context->getRequest()
                );

                var_dump($result);
                $isOk = true;
            } /** @noinspection PhpRedundantCatchClauseInspection */ catch (PaymentException $e) {
                unset($_SESSION['ORDER_PAYMENT_URL']);
                $this->log()->notice(sprintf('payment initiate error: %s', $e->getMessage()), [
                    'order' => $order->getId(),
                    'code' => $e->getCode()
                ]);
            } catch (\Exception $e) {
                unset($_SESSION['ORDER_PAYMENT_URL']);
                $this->log()->error(sprintf('payment error: %s: %s', \get_class($e) , $e->getMessage()), [
                    'order' => $order->getId(),
                    'code' => $e->getCode()
                ]);
            }

            /*
            if (!$isOk) {
                $url = $this->getCompleteUrl($order);
                try {
                    $this->appPaymentService->processOnlinePaymentByOrderNumber($bitrixOrder);
                    if (null !== $relatedOrder && !$relatedOrder->isPaid()) {
                        $url = $this->getPaymentUrl();
                    }
                } catch (SberbankPaymentException $e) {
                    $this->log()->notice(sprintf('payment check error: %s', $e->getMessage()), [
                        'order' => $order->getId(),
                        'code' => $e->getCode()
                    ]);
                    $this->appPaymentService->processOnlinePaymentError($bitrixOrder);
                }

            }
            */
        }




        $hrefDecorator = new FullHrefDecorator((string) $url->getUri());
        return $hrefDecorator->getFullPublicPath();
    }
}
