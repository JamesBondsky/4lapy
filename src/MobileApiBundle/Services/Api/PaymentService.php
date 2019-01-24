<?php

/**
 * @copyright Copyright (c) NotAgency
 */


namespace FourPaws\MobileApiBundle\Services\Api;

/**
 * Подключение класса RBS
 */
/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/payment/rbs.php';

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\PaymentService as AppPaymentService;
use FourPaws\PersonalBundle\Repository\OrderRepository;

class PaymentService
{
    use LazyLoggerAwareTrait;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var AppPaymentService
     */
    private $appPaymentService;


    public function __construct(
        OrderRepository $orderRepository,
        AppPaymentService $appPaymentService
    )
    {
        $this->orderRepository = $orderRepository;
        $this->appPaymentService = $appPaymentService;
    }

    /**
     * @param int $orderNumber
     * @param string $payType
     * @param string $payToken
     * @return string
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     * @throws \Exception
     */
    public function getPaymentUrl(int $orderNumber, string $payType, string $payToken = ''): string
    {
        /**
         * @var $order Order
         */
        $order = $this->orderRepository->findBy([
            'filter' => [
                'ACCOUNT_NUMBER' => $orderNumber
            ]
        ])->current();
        if (!$order) {
            throw new NotFoundException("Заказ с номером $orderNumber не найден");
        }

        $bitrixOrder = $order->getBitrixOrder();
        $amount = $order->getItemsSum() * 100;

        if (!$bitrixOrder->getPaymentCollection()->count()) {
            throw new \Exception("У заказа $orderNumber не указана платежная система");
        }

        if (!$this->appPaymentService->isOnlinePayment($bitrixOrder)) {
            throw new \Exception("У заказа $orderNumber не выбран способ оплаты - онлайн");
        }

        $url = '';

        switch ($payType) {
            case 'cashless':
                $url = $this->appPaymentService->registerOrder($bitrixOrder, $amount);
                break;
            case 'applepay':
                $response = $this->appPaymentService->processApplePay($bitrixOrder, $payToken);
                if ($response['error']) {
                    throw new \Exception($response['error']['message'], $response['error']['code']);
                }
                break;
            case 'android':
                $response = $this->appPaymentService->processGooglePay($bitrixOrder, $payToken, $amount);
                if ($response['error']) {
                    throw new \Exception($response['error']['message'], $response['error']['code']);
                }
                break;
            default:
                throw new \Exception("Unsupported pay type " . $payType);
                break;
        }

        return $url;
    }
}
