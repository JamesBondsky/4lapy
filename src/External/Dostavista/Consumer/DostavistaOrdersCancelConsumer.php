<?php

namespace FourPaws\External\Dostavista\Consumer;

use Adv\Bitrixtools\Tools\BitrixUtils;
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
            $order = $this->orderService->getOrderById($bitrixOrderId);
            $response = $this->dostavistaService->cancelOrder($dostavistaOrder);
            if ($response['connection'] === false) {
                $result = static::MSG_REJECT;
            }
            $dostavistaOrderId = $response['order_id'];
            if (is_array($dostavistaOrderId) || empty($dostavistaOrderId)) {
                return static::MSG_REJECT;
            } else {
                $this->orderService->setOrderPropertiesByCode(
                    $order,
                    [
                        'IS_EXPORTED_TO_DOSTAVISTA' => ($dostavistaOrderId !== 0 && !is_array($dostavistaOrderId)) ? BitrixUtils::BX_BOOL_TRUE : BitrixUtils::BX_BOOL_FALSE
                    ]
                );
                $order->save();
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
