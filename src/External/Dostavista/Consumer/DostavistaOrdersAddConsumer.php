<?php

namespace FourPaws\External\Dostavista\Consumer;

use Adv\Bitrixtools\Tools\BitrixUtils;
use FourPaws\External\Dostavista\Exception\DostavistaOrdersAddConsumerException;
use FourPaws\UserBundle\EventController\Event;
use PhpAmqpLib\Message\AMQPMessage;
use Bitrix\Sale\Order;
use Bitrix\Main\Application;
use FourPaws\App\Application as App;
use Bitrix\Main\Type\DateTime;

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
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Bitrix\Main\ObjectException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(AMQPMessage $message): bool
    {
        Event::disableEvents();

        $result = static::MSG_REJECT;
        Application::getConnection()->queryExecute("SELECT CURRENT_TIMESTAMP");
        $body = $message->getBody();
        $data = json_decode($body, true);
        try {
            $bitrixOrderId = $data['bitrix_order_id'];
            unset($data['bitrix_order_id'], $data['order_create_date']);
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
        } catch (DostavistaOrdersAddConsumerException|\Exception $e) {
            /**
             * Отправляем сообщение в другую очередь
             * @noinspection MissingService
             */
            $data = json_decode($body, true);
            $date['last_date_try_to_send'] = (new DateTime())->format(static::DATE_TIME_FORMAT);
            $producer = App::getInstance()->getContainer()->get('old_sound_rabbit_mq.dostavista_orders_add_dead_producer');
            $producer->publish($this->serializer->serialize($data, 'json'));
            /**
             * Пишем логи
             */
            $this->log()->error('Dostavista error, code: ' . $e->getCode() . ' message: ' . $e->getMessage());
        }

        Event::enableEvents();

        return $result;
    }
}
