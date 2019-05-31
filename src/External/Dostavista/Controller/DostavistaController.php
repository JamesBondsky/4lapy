<?php

namespace FourPaws\External\Dostavista\Controller;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use CSaleOrder;
use FourPaws\App\Application;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\External\DostavistaService;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use FourPaws\SapBundle\Service\Orders\StatusService;

class DostavistaController implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public function __construct()
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
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
            /** @var DostavistaService $dostavistaService */
            $dostavistaService = Application::getInstance()->getContainer()->get('dostavista.service');
            $bitrixOrder = \Bitrix\Sale\Order::load($order['ID']);
//            $dostavistaService->out($bitrixOrder);
            $mess = 'Status success changed for order [' . $order['ID'] . '] for SAP';
            $this->log()->notice($mess);
            $mess = 'Status success changed for order [' . $order['ID'] . '] from status [' . $order['STATUS_ID'] . '] to status [' . $bitrixStatus . ']';
            $this->log()->notice($mess);
            //отправляем email, если достависта отменила или отложила заказ
            if (in_array($dostavistaStatus, ['canceled', 'delayed'])) {
                $dostavistaService->orderCancelSendMail($order['ID'], $bitrixOrder->getField('ACCOUNT_NUMBER'), ($dostavistaStatus == 'canceled') ? 'Отменен' : 'Отложен', (new \Datetime)->format('d.m.Y H:i:s'));
            }
            return JsonSuccessResponse::create(
                'Success: ' . $mess,
                200,
                []
            );
        }
    }
}