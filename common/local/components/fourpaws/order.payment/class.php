<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Main\Application as BitrixApp;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;
use FourPaws\App\Application;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\PaymentException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Exception\NotAuthorizedException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderPaymentComponent extends \CBitrixComponent
{
    /** @var OrderService */
    protected $orderService;

    /** @var CurrentUserProviderInterface */
    protected $currentUserProvider;

    public function __construct($component = null)
    {
        $serviceContainer = Application::getInstance()->getContainer();
        $this->orderService = $serviceContainer->get(OrderService::class);
        $this->currentUserProvider = $serviceContainer->get(CurrentUserProviderInterface::class);
        parent::__construct($component);
    }

    public function onPrepareComponentParams($params)
    {
        $params['ORDER_ID'] = (int)$params['ORDER_ID'];
        $params['HASH'] = $params['HASH'] ?? '';

        return parent::onPrepareComponentParams($params);
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            $order = null;

            try {
                $userId = $this->currentUserProvider->getCurrentUserId();
            } catch (NotAuthorizedException $e) {
                $userId = null;
            }

            try {
                $order = $this->orderService->getOrderById(
                    (int)$this->arParams['ORDER_ID'],
                    true,
                    $userId,
                    $this->arParams['HASH']
                );
            } catch (NotFoundException $e) {
                Tools::process404('', true, true, true);
            }

            /**
             * Попытка повторной оплаты заказа
             */
            if ($order->isPaid()) {
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

                if ($payment->getPaySystem()->getField('CODE') === OrderService::PAYMENT_ONLINE) {
                    $paymentItem = $payment;
                }
            }

            if (!$paymentItem) {
                Tools::process404('', true, true, true);
            }

            $service = PaySystemManager::getObjectById($payment->getPaymentSystemId());
            if ($service) {
                $context = BitrixApp::getInstance()->getContext();

                try {
                    $result = $service->initiatePay(
                        $payment,
                        $context->getRequest()
                    );
                    if ($result->isSuccess()) {
                        $this->arResult['IS_SUCCESS'] = 'Y';
                    } else {
                        $this->arResult['ERRORS'] = $result->getErrorMessages();
                    }
                } catch (PaymentException $e) {
                    $this->arResult['ERRORS'][] = $e->getMessage();
                }
            }

            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }
}
