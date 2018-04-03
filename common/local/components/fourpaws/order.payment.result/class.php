<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;
use FourPaws\App\Application;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\PaymentException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Exception\NotAuthorizedException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderPaymentResultComponent extends \CBitrixComponent
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
        $params['REDIRECT_URL'] = $params['REDIRECT_URL'] ?? '';

        return parent::onPrepareComponentParams($params);
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
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

                if ($payment->getPaySystem()->getField('CODE') === OrderService::PAYMENT_ONLINE) {
                    $paymentItem = $payment;
                }
            }

            if (!$paymentItem || !$service = PaySystemManager::getObjectById($payment->getPaymentSystemId())) {
                Tools::process404('', true, true, true);
            }

            $actionFile = $payment->getPaySystem()->getFieldsValues()['ACTION_FILE'];
            $url = new \Bitrix\Main\Web\Uri('/sale/order/complete/' . $order->getId());

            if (!empty($this->arParams['HASH'])) {
                $url->addParams(['HASH' => $this->arParams['HASH']]);
            }

            if (!empty($this->arParams['REDIRECT_URL'])) {
                $url->setPath($this->arParams['REDIRECT_URL']);
                $url->addParams(['ORDER_ID' => $order->getId()]);
            }

            try {
                $this->includeResultFile($actionFile);
                if ($relatedOrder && !$relatedOrder->isPaid()) {
                    $url->setPath('/sale/payment');
                    $url->addParams(['ORDER_ID' => $order->getId()]);
                }
            } /** @noinspection PhpRedundantCatchClauseInspection */ catch (PaymentException $e) {
                $this->orderService->processPaymentError($order);
                $this->arResult['ERRORS'][] = $e->getMessage();
            }
            LocalRedirect($url->getUri());
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component_order_payment_result');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }

        $this->includeComponentTemplate();
    }

    /**
     * @param string $actionFile
     *
     * @return bool
     */
    protected function includeResultFile(string $actionFile): bool
    {
        if (is_dir($_SERVER['DOCUMENT_ROOT'] . $actionFile) &&
            file_exists($_SERVER['DOCUMENT_ROOT'] . $actionFile . '/result.php')
        ) {
            require $_SERVER['DOCUMENT_ROOT'] . $actionFile . '/result.php';

            return true;
        }

        return false;
    }
}
