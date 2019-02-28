<?php

namespace FourPaws\External\Dostavista\Consumer;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Bitrix\Sale\Order;
use FourPaws\UserBundle\EventController\Event;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

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

        $result = static::MSG_ACK;

        try {
            $data = json_decode($message->getBody(), true);
            $dostavistaOrder = $data['dostavista_order_id'];
            $bitrixOrderId = $data['bitrix_order_id'];
            /**
             * @var Order $order
             * Получаем битриксовый заказ
             */
            if ($bitrixOrderId == null || $dostavistaOrder == null) {
                $result = static::MSG_REJECT;
            } else {
                $order = $this->orderService->getOrderById($bitrixOrderId);
                if (!$order) {
                    $result = static::MSG_REJECT;
                } else {
                    /** Отправляем отмену заказа в достависту */
                    $response = $this->dostavistaService->cancelOrder($dostavistaOrder);
                    if ($response['connection'] === false) {
                        $result = static::MSG_REJECT;
                    } else {
                        $dostavistaOrderId = $response['order_id'];
                        if (is_array($dostavistaOrderId) || empty($dostavistaOrderId)) {
                            $result = static::MSG_REJECT;
                        } else {
                            /** Обновляем битриксовое свойство */
                            $this->orderService->setOrderPropertiesByCode(
                                $order,
                                [
                                    'IS_EXPORTED_TO_DOSTAVISTA' => ($dostavistaOrderId !== 0 && !is_array($dostavistaOrderId)) ? BitrixUtils::BX_BOOL_TRUE : BitrixUtils::BX_BOOL_FALSE
                                ]
                            );
                            $order->save();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $result = static::MSG_REJECT;
        }

        Event::enableEvents();

        return $result;
    }

    /**
     * @return LoggerInterface
     */
    protected function log(): LoggerInterface
    {
        return $this->logger;
    }
}
