<?php

namespace FourPaws\External\Dostavista\Consumer;

use Adv\Bitrixtools\Tools\BitrixUtils;
use FourPaws\External\Dostavista\Exception\DostavistaOrdersAddConsumerException;
use FourPaws\UserBundle\EventController\Event;
use PhpAmqpLib\Message\AMQPMessage;
use Bitrix\Sale\Order;
use Bitrix\Main\Application;

/**
 * Class DostavistaOrdersAddConsumer
 *
 * @package FourPaws\External\Dostavista\Consumer
 */
class DostavistaOrdersAddConsumer extends DostavistaConsumerBase
{
    /**
     * @param AMQPMessage $message
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(AMQPMessage $message): bool
    {
        Event::disableEvents();

        $result = static::MSG_REJECT;
        Application::getConnection()->queryExecute("SELECT CURRENT_TIMESTAMP");

        try {
            $data = json_decode($message->getBody(), true);
            $bitrixOrderId = $data['bitrix_order_id'];
            unset($data['bitrix_order_id']);
            /**
             * @var Order $order
             * Получаем битриксовый заказ
             */
            if ($bitrixOrderId === null) {
                throw new DostavistaOrdersAddConsumerException('Dostavista: bitrix order id empty in message data!', 10);
            }

            $order = $this->orderService->getOrderById($bitrixOrderId);
            if (!$order) {
                throw new DostavistaOrdersAddConsumerException('Dostavista: bitrix order not found!', 20);
            }

            /** Отправляем заказ в достависту */
            $response = $this->dostavistaService->addOrder($data);

            if ($response['connection'] === false) {
                throw new DostavistaOrdersAddConsumerException('Dostavista: connection with service dostavista error!', 30);
            }
            $dostavistaOrderId = $response['order_id'];
            if ((is_array($dostavistaOrderId) || empty($dostavistaOrderId)) || !$response['success']) {
                throw new DostavistaOrdersAddConsumerException('Dostavista: dostavista service add order failed!', 40);
            }
            /** Обновляем битриксовые свойства достависты */
            $deliveryId = $order->getField('DELIVERY_ID');
            $deliveryCode = $this->deliveryService->getDeliveryCodeById($deliveryId);
            $address = $this->orderService->compileOrderAddress($order)->setValid(true);
            $this->orderService->setOrderPropertiesByCode(
                $order,
                [
                    'IS_EXPORTED_TO_DOSTAVISTA' => ($dostavistaOrderId) ? BitrixUtils::BX_BOOL_TRUE : BitrixUtils::BX_BOOL_FALSE,
                    'ORDER_ID_DOSTAVISTA' => ($dostavistaOrderId) ? $dostavistaOrderId : 0
                ]
            );
            $this->orderService->updateCommWayPropertyEx($order, $deliveryCode, $address, ($dostavistaOrderId) ? true : false);
            $order->save();
            $result = static::MSG_ACK;
        } catch (\DostavistaOrdersAddConsumerException|\Exception $e) {
            $this->log()->error('Dostavista error, code: ' . $e->getCode() . ' message: ' . $e->getMessage());
            if ($order && is_array($response)) {
                $this->dostavistaService->dostavistaOrderAddErrorSendEmail($order->getId(), $order->getField('ACCOUNT_NUMBER'), $response['message'], $response['data'], (new \Datetime)->format('d.m.Y H:i:s'));
            } else {
                $this->dostavistaService->dostavistaOrderAddErrorSendEmail(0, 0, $e->getMessage(), $e->getCode(), (new \Datetime)->format('d.m.Y H:i:s'));
            }
        }

        Event::enableEvents();

        return $result;
    }
}
