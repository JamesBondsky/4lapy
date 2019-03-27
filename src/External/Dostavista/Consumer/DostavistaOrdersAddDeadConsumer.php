<?php

namespace FourPaws\External\Dostavista\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use FourPaws\App\Application;
use Bitrix\Main\Type\DateTime;
use FourPaws\External\Dostavista\Exception\DostavistaOrdersAddConsumerException;

/**
 * Class DostavistaOrdersAddDeadConsumer
 *
 * @package FourPaws\External\Dostavista\Consumer
 */
class DostavistaOrdersAddDeadConsumer extends DostavistaConsumerBase
{
    /**
     * @param AMQPMessage $message
     * @return bool
     */
    public function execute(AMQPMessage $message): bool
    {
        $res = static::MSG_REJECT;
        $body = $message->getBody();
        $data = json_decode($message->getBody(), true);
        $lastDateTryToSend = new DateTime($data['last_date_try_to_send'], static::DATE_TIME_FORMAT);
        if ($lastDateTryToSend->add('+1 minutes') >= new DateTime()) {
            $res = static::MSG_REJECT_REQUEUE;
        }
        try {
            /** @var DateTime $orderCreateDate */
            $orderCreateDate = new DateTime($data['order_create_date'], static::DATE_TIME_FORMAT);
            if (!$orderCreateDate instanceof DateTime) {
                throw new DostavistaOrdersAddConsumerException('Dostavista: bitrix order date create not found!', 120);
            }
            if ($orderCreateDate->add('+20 minutes') >= new DateTime()) {
                //время отправки вышло
                $bitrixOrderId = $data['bitrix_order_id'];
                if ($bitrixOrderId === null) {
                    throw new DostavistaOrdersAddConsumerException('Dostavista: bitrix order id empty in message data!', 10);
                }
                $order = $this->orderService->getOrderById($bitrixOrderId);
                /** Обновляем битриксовые свойства достависты */
                $this->updateCommWayProperty($order, false);
                if ($order) {
                    $this->dostavistaService->dostavistaOrderAddErrorSendEmail($order->getId(), $order->getField('ACCOUNT_NUMBER'), '', '', (new \Datetime)->format(static::DATE_TIME_FORMAT));
                } else {
                    $this->dostavistaService->dostavistaOrderAddErrorSendEmail(0, 0, '', '', (new \Datetime)->format(static::DATE_TIME_FORMAT));
                }
                throw new DostavistaOrdersAddConsumerException('Dostavista error, time to send the order to the delivery man has expired', 130);
            }
            //пушим обратно на обработку
            /** @noinspection MissingService */
            $producer = Application::getInstance()->getContainer()->get('old_sound_rabbit_mq.dostavista_orders_add_producer');
            $producer->publish($body);
            $res = static::MSG_ACK;
        } catch (DostavistaOrdersAddConsumerException|\Exception $e) {
            $this->log()->error('Dostavista error, code: ' . $e->getCode() . ' message: ' . $e->getMessage(), is_array($data) ? $data : []);
        }
        return $res;
    }
}
