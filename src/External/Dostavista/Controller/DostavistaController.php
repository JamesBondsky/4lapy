<?php

namespace FourPaws\External\Dostavista\Controller;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use CSaleOrder;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use FourPaws\SapBundle\Service\Orders\StatusService;

class DostavistaController implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public function __construct()
    {
    }

    public function deliveryDostavistaOrderChangeAction(Request $request): JsonResponse
    {
        $callbackSecretKey = \COption::GetOptionString('articul.dostavista.delivery', 'callback_secret_key', '');

        if (!isset($_SERVER['HTTP_X_DV_SIGNATURE'])) {

            return JsonErrorResponse::createWithData(
                'Error: Signature is not found!',
                [],
                200,
                []
            );
        }
        $stream = file_get_contents('php://input');
        $signature = hash_hmac('sha256', $stream, $callbackSecretKey);
        if ($signature != $_SERVER['HTTP_X_DV_SIGNATURE']) {
            $mess = 'Signatures is not equals!';
            $this->log()->error($mess);
            return JsonErrorResponse::createWithData(
                'Error: ' . $mess,
                [],
                200,
                []
            );
        }
        $data = json_decode($stream, true);
        $dostavistaOrderId = $data['order']['order_id'];
        $dostavistaStatus = $data['order']['status'];
        $bitrixStatus = StatusService::STATUS_DOSTAVISTA_MAP[array_flip(StatusService::STATUS_SITE_DOSTAVISTA_MAP)[$dostavistaStatus]];
        if (empty($bitrixStatus)) {
            $mess = 'Conformity of dostavista status and status bitrix not found!';
            $context = [
                'dostavista_order_id' => $dostavistaOrderId,
                'dostavista_status' => $dostavistaStatus
            ];
            $this->log()->error($mess, $context);
            return JsonErrorResponse::createWithData(
                'Error: ' . $mess,
                $context,
                200,
                []
            );
        }
        $orderRes = CSaleOrder::getList([], ['PROPERTY_VAL_BY_CODE_ORDER_ID_DOSTAVISTA' => $dostavistaOrderId], false, ['nTopCount' => 1]);
        if (!($order = $orderRes->fetch())) {
            $mess = 'Bitrix Order not Found!';
            $context = [
                'dostavista_order_id' => $dostavistaOrderId
            ];
            $this->log()->notice($mess, $context);
            return JsonErrorResponse::createWithData(
                'Error: ' . $mess,
                $context,
                200,
                []
            );
        }
        if ($bitrixStatus == $order['STATUS_ID']) {
            $mess = 'Order statuses equals!';
            $this->log()->notice($mess);
            return JsonSuccessResponse::create(
                'Success: ' . $mess,
                200,
                []
            );
        } elseif (!CSaleOrder::StatusOrder($order['ID'], $bitrixStatus)) {
            $mess = 'Bitrix set status exception for order [' . $order['ID'] . '], dostavista_order_id [' . $dostavistaOrderId . '], ' . 'dostavista_status [' . $dostavistaStatus . '], bitrix_status [' . $bitrixStatus . ']';
            $context = [
                'bitrix_order_id' => $order['ID'],
                'dostavista_order_id' => $dostavistaOrderId,
                'dostavista_status' => $dostavistaStatus,
                'bitrix_status' => $bitrixStatus
            ];
            $this->log()->error($mess, $context);
            return JsonErrorResponse::createWithData(
                'Error: ' . $mess,
                $context,
                200,
                []
            );
        } else {
            $mess = 'Status success changed for order [' . $order['ID'] . '] from status [' . $order['STATUS_ID'] . '] to status [' . $bitrixStatus . ']';
            $this->log()->notice($mess);
            return JsonSuccessResponse::create(
                'Success: ' . $mess,
                200,
                []
            );
        }
    }
}