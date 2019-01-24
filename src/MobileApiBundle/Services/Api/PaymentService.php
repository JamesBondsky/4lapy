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
                // $this->addError('required_params_missed');
                break;
        }

        if ($payType == 'applepay' || $payType == 'android') {

            var_dump($response);

            if (($response['orderStatus']['orderStatus'] == 1)) { //hold
                $arFieldsBlock = array(
                    "PS_SUM" => $response['orderStatus']["amount"] / 100,
                    // "PS_CURRENCY" => $sbrf->getCurrenciesISO($response['orderStatus']["currency"]),
                    // "PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
                    "PAYED" => "N",
                    "PS_STATUS" => "N",
                    "PS_STATUS_CODE" => "Hold",
                    "PS_STATUS_DESCRIPTION" => GetMessage("WF.SBRF_PS_CURSTAT") . GetMessage("WF.SBRF_PS_STATUS_DESC_HOLD") . "; " . GetMessage("WF.SBRF_PS_CARDNUMBER") . $response['orderStatus']["cardAuthInfo"]["pan"] . "; " . GetMessage("WF.SBRF_PS_CARDHOLDER") . $response['orderStatus']['cardAuthInfo']["cardholderName"] . "; OrderNumber:" . $response['orderStatus']['orderNumber'],
                    "PS_STATUS_MESSAGE" => $response['orderStatus']["paymentAmountInfo"]["paymentState"],
                    "PAY_VOUCHER_NUM" => $response['data']['orderId'], //дописываем айдишник транзакции к заказу, чтоб потом передать в сап
                    // "PAY_VOUCHER_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)))
                );
                var_dump($arFieldsBlock);
                // $Order->Update($OrderNumber, $arFieldsBlock);
            }
            if (($response['orderStatus']['orderStatus'] == 2)) { //success
                $arFieldsSuccess = array(
                    "PS_SUM" => $response['orderStatus']["amount"] / 100,
                    // "PS_CURRENCY" => $sbrf->getCurrenciesISO($response['orderStatus']["currency"]),
                    // "PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
                    "PAYED" => "Y",
                    "PS_STATUS" => "Y",
                    "PS_STATUS_CODE" => "Pay",
                    "PS_STATUS_DESCRIPTION" => GetMessage("WF.SBRF_PS_CURSTAT") . GetMessage("WF.SBRF_PS_STATUS_DESC_PAY") . "; " . GetMessage("WF.SBRF_PS_CARDNUMBER") . $response['orderStatus']["cardAuthInfo"]["pan"] . "; " . GetMessage("WF.SBRF_PS_CARDHOLDER") . $response['orderStatus']['cardAuthInfo']["cardholderName"] . "; OrderNumber:" . $response['orderStatus']['orderNumber'],
                    // "PS_STATUS_MESSAGE" => self::toWIN($response['orderStatus']["paymentAmountInfo"]["paymentState"]),
                    "PAY_VOUCHER_NUM" => $response['data']['orderId'], //дописываем айдишник транзакции к заказу, чтоб потом передать в сап
                    // "PAY_VOUCHER_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)))
                );
                var_dump($arFieldsSuccess);
                // $Order->PayOrder($OrderNumber, "Y", true, true);
                // $Order->Update($OrderNumber, $arFieldsSuccess);
                // $message = GetMessage("WF.SBRF_PAY_SUCCESS_TEXT", array("#ORDER_ID#" => $arOrder["ID"]));
            }
        }


        return $url;
    }
}
