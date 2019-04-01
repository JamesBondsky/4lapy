<?php

namespace FourPaws\External\Dostavista\Consumer;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Bitrix\Sale\Order;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use JMS\Serializer\Serializer;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareInterface;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Psr\Log\LoggerInterface;
use FourPaws\External\DostavistaService;
use FourPaws\SaleBundle\Service\OrderService;

abstract class DostavistaConsumerBase implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    const DATE_TIME_FORMAT = 'd.m.Y H:i:s';

    const ERRORS = [
        'order_id_empty' => [
            'code' => 100400,
            'message' => 'Dostavista: bitrix order id empty in message data!'
        ],
        'dostavista_order_id_empty' => [
            'code' => 100401,
            'message' => 'Dostavista: dostavista order id empty in message data!'
        ],
        'order_not_found' => [
            'code' => 100404,
            'message' => 'Dostavista: bitrix order not found!'
        ],
        'dostavista_order_not_found' => [
            'code' => 100405,
            'message' => 'Dostavista: dostavista order not found!'
        ],
        'service_add_order_failed' => [
            'code' => 100500,
            'message' => 'Dostavista: dostavista service add order failed!'
        ],
        'service_connection_failed' => [
            'code' => 100521,
            'message' => 'Dostavista: connection with service dostavista error!'
        ],
        'order_date_create_not_found' => [
            'code' => 100415,
            'message' => 'Dostavista: bitrix order date create not found!'
        ],
        'time_to_send_has_expired' => [
            'code' => 100524,
            'message' => 'Dostavista error, time to send the order to the express delivery has expired!'
        ],
    ];

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var DostavistaService
     */
    protected $dostavistaService;

    public function __construct(Serializer $serializer, DostavistaService $dostavistaService, OrderService $orderService, DeliveryService $deliveryService)
    {
        Application::includeBitrix();

        $this->serializer = $serializer;
        $this->dostavistaService = $dostavistaService;
        $this->orderService = $orderService;
        $this->deliveryService = $deliveryService;

        $this->withLogType('dostavista');
        $this->setLogger(LoggerFactory::create($this->getLogName(), $this->getLogType()));
    }

    /**
     * @inheritdoc
     */
    abstract public function execute(AMQPMessage $message): bool;

    /**
     * @return LoggerInterface
     */
    public function log(): LoggerInterface
    {
        if ($this->logger === null) {
            $this->withLogType('dostavista');
            $this->logger = LoggerFactory::create($this->getLogName(), $this->getLogType());
        }

        return $this->logger;
    }

    /**
     * @param Order $order
     * @param bool $dostavistaOrderId
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     *
     * @return void
     */
    public function updateCommWayProperty(Order $order, $dostavistaOrderId = false): void
    {
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
    }
}
