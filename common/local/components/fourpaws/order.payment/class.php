<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\BitrixUtils;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Main\Application as BitrixApp;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\PaymentException;
use FourPaws\SaleBundle\Exception\SberbankPaymentException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\PaymentService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderPaymentComponent extends FourPawsComponent
{
    /** @var OrderService */
    protected $orderService;

    /** @var PaymentService */
    protected $paymentService;

    /** @var CurrentUserProviderInterface */
    protected $currentUserProvider;

    /**
     * {@inheritdoc}
     *
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ServiceNotFoundException
     */
    public function __construct($component = null)
    {
        $serviceContainer = Application::getInstance()->getContainer();
        $this->orderService = $serviceContainer->get(OrderService::class);
        $this->paymentService = $serviceContainer->get(PaymentService::class);
        $this->currentUserProvider = $serviceContainer->get(CurrentUserProviderInterface::class);
        parent::__construct($component);
    }

    /**
     * {@inheritdoc}
     */
    public function onPrepareComponentParams($params): array
    {
        $params['ORDER_ID'] = (int)$params['ORDER_ID'];
        $params['HASH'] = $params['HASH'] ?? '';
        $params['CACHE_TYPE'] = 'N';

        return parent::onPrepareComponentParams($params);
    }

    /** {@inheritdoc} */
    public function prepareResult(): void
    {
        global $APPLICATION;
        if ($this->arParams['SET_TITLE'] === BitrixUtils::BX_BOOL_TRUE) {
            $APPLICATION->SetTitle('Перейти к оплате');
        }

        $order = null;
        $relatedOrder = null;
        try {
            $userId = $this->currentUserProvider->getCurrentUserId();
        } catch (NotAuthorizedException $e) {
            $userId = null;
        }

        if ((int)$this->arParams['ORDER_ID'] === 0) {
            Tools::process404('', true, true, true);
        }

        try {
            $order = $this->orderService->getOrderById(
                (int)$this->arParams['ORDER_ID'],
                true,
                $userId,
                $this->arParams['HASH']
            );
            if ($this->orderService->hasRelatedOrder($order)) {
                $relatedOrder = $this->orderService->getRelatedOrder($order);
            }
        } catch (NotFoundException $e) {
            Tools::process404('', true, true, true);
        }

        /**
         * Попытка повторной оплаты заказа
         */
        if ($order->isPaid() && ((null === $relatedOrder) || $relatedOrder->isPaid())) {
            Tools::process404('', true, true, true);
        }

        $paymentItem = null;

        $this->arResult['IS_SUCCESS'] = 'N';
        $this->arResult['ERRORS'] = [];

        /** @var Payment $payment */
        foreach ($order->getPaymentCollection() as $payment) {
            if ($payment->isInner()) {
                continue;
            }

            if ($payment->getPaySystem()->getField('CODE') === OrderPayment::PAYMENT_ONLINE) {
                $paymentItem = $payment;
            }
        }

        if (!$paymentItem) {
            Tools::process404('', true, true, true);
        }

        if ($this->arParams['PAY'] === BitrixUtils::BX_BOOL_TRUE) {
            $service = PaySystemManager::getObjectById($paymentItem->getPaymentSystemId());

            if ($service) {
                $context = BitrixApp::getInstance()->getContext();
                $isOk = false;
                try {
                    $_SESSION['ORDER_PAYMENT_URL'] = $this->getPaymentUrl();
                    $result = $service->initiatePay(
                        $paymentItem,
                        $context->getRequest()
                    );

                    if ($result->isSuccess()) {
                        $this->arResult['IS_SUCCESS'] = 'Y';
                    } else {
                        $this->arResult['ERRORS'] = $result->getErrorMessages();
                    }
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

                if (!$isOk) {
                    $url = $this->getCompleteUrl($order);
                    try {
                        $this->paymentService->processOnlinePaymentByOrderNumber($order);
                        if (null !== $relatedOrder && !$relatedOrder->isPaid()) {
                            $url = $this->getPaymentUrl();
                        }
                    } catch (SberbankPaymentException $e) {
                        $this->log()->notice(sprintf('payment check error: %s', $e->getMessage()), [
                            'order' => $order->getId(),
                            'code' => $e->getCode()
                        ]);
                        $this->paymentService->processOnlinePaymentError($order);
                    }

                    LocalRedirect($url->getUri());
                }
            }
        }

        $this->arResult['ORDER'] = $order;
        $url = new Uri($APPLICATION->GetCurPage());
        $this->arResult['ORDER_PAY_URL'] = $url->getUri();

        if ($relatedOrder) {
            $this->arResult['RELATED_ORDER'] = $relatedOrder;
            $this->arResult['RELATED_ORDER_PAY_URL'] = $url->getUri();
        }
    }

    /**
     * @param Order $order
     *
     * @return Uri
     */
    protected function getCompleteUrl(Order $order): Uri
    {
        $url = new Uri(sprintf('/sale/order/complete/%s/', $order->getId()));

        if (!empty($this->arParams['HASH'])) {
            $url->addParams(['HASH' => $this->arParams['HASH']]);
        }

        return $url;
    }

    /**
     * @return Uri
     * @throws SystemException
     */
    protected function getPaymentUrl(): Uri
    {
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $url = new Uri($request->getRequestedPage());
        $url->addParams(['ORDER_ID' => $this->arParams['ORDER_ID']]);
        if (!empty($this->arParams['HASH'])) {
            $url->addParams(['HASH' => $this->arParams['HASH']]);
        }

        return $url;
    }
}
