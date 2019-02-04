<?php

namespace FourPaws\External\Dostavista\Consumer;

use Adv\Bitrixtools\Tools\BitrixUtils;
use FourPaws\UserBundle\EventController\Event;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

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

        $result = static::MSG_ACK;

        try {
            $data = json_decode($message->getBody(), true);
            $bitrixOrderId = $data['bitrix_order_id'];
            unset($data['bitrix_order_id']);
            $response = $this->dostavistaService->addOrder($data);
            if ($response['connection'] === false) {
                $result = static::MSG_REJECT;
            }
            $dostavistaOrderId = $response['order_id'];
            if (is_array($dostavistaOrderId) || empty($dostavistaOrderId)) {
                $dostavistaOrderId = 0;
                $result = static::MSG_REJECT_REQUEUE;
            }
            $order = $this->orderService->getOrderById($bitrixOrderId);
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
        } catch (\Exception $e) {
            $result = static::MSG_REJECT_REQUEUE;
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
