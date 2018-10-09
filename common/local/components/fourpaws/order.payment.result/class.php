<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Iblock\Component\Tools;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;
use FourPaws\App\Application;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\PaymentException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\PaymentService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Exception\NotAuthorizedException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderPaymentResultComponent extends FourPawsComponent
{
    /** @var OrderService */
    protected $orderService;

    /** @var PaymentService */
    protected $paymentService;

    /** @var CurrentUserProviderInterface */
    protected $currentUserProvider;

    public function __construct($component = null)
    {
        $serviceContainer = Application::getInstance()->getContainer();
        $this->orderService = $serviceContainer->get(OrderService::class);
        $this->paymentService = $serviceContainer->get(PaymentService::class);
        $this->currentUserProvider = $serviceContainer->get(CurrentUserProviderInterface::class);
        parent::__construct($component);
    }

    public function onPrepareComponentParams($params): array
    {
        $params['ORDER_ID'] = (int)$params['ORDER_ID'];
        $params['HASH'] = $params['HASH'] ?? '';
        $params['REDIRECT_URL'] = $params['REDIRECT_URL'] ?? '';
        $params['CACHE_TYPE'] = 'N';

        return parent::onPrepareComponentParams($params);
    }

    /** {@inheritdoc} */
    public function prepareResult(): void
    {
        $order = null;
        $relatedOrder = null;
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
            if ($this->orderService->hasRelatedOrder($order)) {
                $relatedOrder = $this->orderService->getRelatedOrder($order);
            }
        } catch (NotFoundException $e) {
            Tools::process404('', true, true, true);
        }
        unset($_SESSION['ORDER_PAYMENT_URL']);

        /**
         * Попытка повторной оплаты заказа
         */
        if ($order->isPaid() && ((null === $relatedOrder) || $relatedOrder->isPaid())) {
            Tools::process404('', true, true, true);
        }

        $paymentItem = null;

        /** @var Payment $payment */
        foreach ($order->getPaymentCollection() as $payment) {
            if ($payment->isInner()) {
                continue;
            }

            if ($payment->getPaySystem()->getField('CODE') === OrderPayment::PAYMENT_ONLINE) {
                $paymentItem = $payment;
            }
        }

        if (!$paymentItem || !$service = PaySystemManager::getObjectById($payment->getPaymentSystemId())) {
            Tools::process404('', true, true, true);
        }

        $actionFile = $payment->getPaySystem()->getFieldsValues()['ACTION_FILE'];
        $url = new \Bitrix\Main\Web\Uri(sprintf('/sale/order/complete/%s/', $order->getId()));

        if (!empty($this->arParams['HASH'])) {
            $url->addParams(['HASH' => $this->arParams['HASH']]);
        }

        if (!empty($this->arParams['REDIRECT_URL'])) {
            $url->setPath($this->arParams['REDIRECT_URL']);
            $url->addParams(['ORDER_ID' => $order->getId()]);
        }

        $isOk = false;
        try {
            $this->includeResultFile($actionFile);
            if ($relatedOrder && !$relatedOrder->isPaid()) {
                $url->setPath('/sale/payment');
                $url->addParams(['ORDER_ID' => $order->getId()]);
            }
            $isOk = true;
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (PaymentException $e) {
            $this->log()->notice(sprintf('payment error: %s', $e->getMessage()), [
                'order' => $order->getId(),
                'code' => $e->getCode()
            ]);
        } catch (\Exception $e) {
            $this->log()->error(sprintf('payment error: %s: %s', \get_class($e), $e->getMessage()), [
                'order' => $order->getId(),
                'code' => $e->getCode()
            ]);
        }
        if (!$isOk) {
            $this->paymentService->processOnlinePaymentError($order);
        }

        LocalRedirect($url->getUri());
    }

    /**
     * @param string $actionFile
     */
    protected function includeResultFile(string $actionFile): void
    {
        if (is_dir($_SERVER['DOCUMENT_ROOT'] . $actionFile) &&
            file_exists($_SERVER['DOCUMENT_ROOT'] . $actionFile . '/result.php')
        ) {
            require $_SERVER['DOCUMENT_ROOT'] . $actionFile . '/result.php';
        }
    }
}
