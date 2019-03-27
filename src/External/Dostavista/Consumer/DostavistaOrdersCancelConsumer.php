<?php

namespace FourPaws\External\Dostavista\Consumer;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Bitrix\Sale\Order;
use FourPaws\UserBundle\EventController\Event;
use PhpAmqpLib\Message\AMQPMessage;
use FourPaws\External\Dostavista\Exception\DostavistaOrdersAddConsumerException;
use FourPaws\App\Application;

/**
 * Class DostavistaOrdersCancelConsumer
 *
 * @package FourPaws\External\Dostavista\Consumer
 */
class DostavistaOrdersCancelConsumer extends DostavistaConsumerBase
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
            $dostavistaOrder = $data['dostavista_order_id'];
            $bitrixOrderId = $data['bitrix_order_id'];
            /**
             * @var Order $order
             * Получаем битриксовый заказ
             */
            if ($bitrixOrderId === null) {
                throw new DostavistaOrdersAddConsumerException('Dostavista: bitrix order id empty in message data!', 10);
            }
            if ($dostavistaOrder === null) {
                throw new DostavistaOrdersAddConsumerException('Dostavista: dostavista order id empty in message data!', 15);
            }
            $order = $this->orderService->getOrderById($bitrixOrderId);
            if (!$order) {
                throw new DostavistaOrdersAddConsumerException('Dostavista: bitrix order not found!', 20);
            }
            /** Отправляем отмену заказа в достависту */
            $response = $this->dostavistaService->cancelOrder($dostavistaOrder);
            if ($response['connection'] === false) {
                throw new DostavistaOrdersAddConsumerException('Dostavista: connection with service dostavista error!', 30);
            }
            $dostavistaOrderId = $response['order_id'];
            if (is_array($dostavistaOrderId) || empty($dostavistaOrderId)) {
                throw new DostavistaOrdersAddConsumerException('Dostavista: dostavista order not found!', 30);
            }
            /** Обновляем битриксовое свойство */
            $this->orderService->setOrderPropertiesByCode(
                $order,
                [
                    'IS_EXPORTED_TO_DOSTAVISTA' => ($dostavistaOrderId !== 0 && !is_array($dostavistaOrderId)) ? BitrixUtils::BX_BOOL_TRUE : BitrixUtils::BX_BOOL_FALSE
                ]
            );
            $order->save();
            $result = static::MSG_ACK;
        } catch (\Exception $e) {
            $this->log()->error('Dostavista error, code: ' . $e->getCode() . ' message: ' . $e->getMessage());
        }

        Event::enableEvents();

        return $result;
    }
}
