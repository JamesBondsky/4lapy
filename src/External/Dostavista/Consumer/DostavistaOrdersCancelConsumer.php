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
                throw new DostavistaOrdersAddConsumerException(
                    self::ERRORS['order_id_empty']['message'],
                    self::ERRORS['order_id_empty']['code']
                );
            }
            if ($dostavistaOrder === null) {
                throw new DostavistaOrdersAddConsumerException(
                    self::ERRORS['dostavista_order_id_empty']['message'],
                    self::ERRORS['dostavista_order_id_empty']['code']
                );
            }
            $order = $this->orderService->getOrderById($bitrixOrderId);
            if (!$order) {
                throw new DostavistaOrdersAddConsumerException(
                    self::ERRORS['order_not_found']['message'],
                    self::ERRORS['order_not_found']['code']
                );
            }
            /** Отправляем отмену заказа в достависту */
            $response = $this->dostavistaService->cancelOrder($dostavistaOrder);
            if ($response['connection'] === false) {
                throw new DostavistaOrdersAddConsumerException(
                    self::ERRORS['service_connection_failed']['message'],
                    self::ERRORS['service_connection_failed']['code']
                );
            }
            $dostavistaOrderId = $response['order_id'];
            if (is_array($dostavistaOrderId) || empty($dostavistaOrderId)) {
                throw new DostavistaOrdersAddConsumerException(
                    self::ERRORS['dostavista_order_not_found']['message'],
                    self::ERRORS['dostavista_order_not_found']['code']
                );
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
