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

        $result = self::MSG_REJECT;
        Application::getConnection()->queryExecute("SELECT CURRENT_TIMESTAMP");
        $body = $message->getBody();
        $data = json_decode($body, true);
        try {
            $bitrixOrderId = $data['bitrix_order_id'];
            unset($data['bitrix_order_id'], $data['order_create_date'], $data['last_date_try_to_send']);
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

            $order = $this->orderService->getOrderById($bitrixOrderId);
            if (!$order) {
                throw new DostavistaOrdersAddConsumerException(
                    self::ERRORS['order_not_found']['message'],
                    self::ERRORS['order_not_found']['code']
                );
            }

            /** Отправляем заказ в достависту */
            $response = $this->dostavistaService->addOrder($data);

            if ($response['connection'] === false) {
                throw new DostavistaOrdersAddConsumerException(
                    self::ERRORS['service_connection_failed']['message'],
                    self::ERRORS['service_connection_failed']['code']
                );
            }
            $dostavistaOrderId = $response['order_id'];
            if ((is_array($dostavistaOrderId) || empty($dostavistaOrderId)) || !$response['success']) {
                throw new DostavistaOrdersAddConsumerException(
                    self::ERRORS['service_add_order_failed']['message'],
                    self::ERRORS['service_add_order_failed']['code']
                );
            }
            /** Обновляем битриксовые свойства достависты */
            $this->updateCommWayProperty($order, $dostavistaOrderId);
            $result = self::MSG_ACK;
        } catch (DostavistaOrdersAddConsumerException|\Exception $e) {
            /**
             * Отправляем сообщение в другую очередь
             * @noinspection MissingService
             */
            $data = json_decode($body, true);
            $data['last_date_try_to_send'] = (new DateTime())->format(self::DATE_TIME_FORMAT);
            $producer = App::getInstance()->getContainer()->get('old_sound_rabbit_mq.dostavista_orders_add_dead_producer');
            $producer->publish($this->serializer->serialize($data, 'json'));
            /**
             * Пишем логи
             */
            if ($e->getCode() == 100500 && is_array($response) && isset($response['message'])) {
                $this->log()->error('Dostavista error, code: ' . $e->getCode() . ' message: ' . $response['message']);
            } else {
                $this->log()->error('Dostavista error, code: ' . $e->getCode() . ' message: ' . $e->getMessage());
            }
        }

        Event::enableEvents();

        return $result;
    }
}
