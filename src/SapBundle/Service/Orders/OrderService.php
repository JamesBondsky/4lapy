<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\Orders;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Sale\Order;
use FourPaws\SaleBundle\Service\OrderService as BaseOrderService;
use FourPaws\SapBundle\Dto\In\Orders\Order as OrderDtoIn;
use FourPaws\SapBundle\Dto\Out\Orders\Order as OrderDtoOut;
use FourPaws\SapBundle\Source\SourceMessage;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Class OrderService
 *
 * @package FourPaws\SapBundle\Service\Orders
 */
class OrderService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var BaseOrderService
     */
    private $baseOrderService;
    /**
     * @var ArrayTransformerInterface
     */
    private $arrayTransformer;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var string
     */
    private $outPath;
    /**
     * @var string
     */
    private $messageId;

    /**
     * OrderService constructor.
     *
     * @param BaseOrderService $baseOrderService
     * @param ArrayTransformerInterface $arrayTransformer
     * @param SerializerInterface $serializer
     * @param Filesystem $filesystem
     */
    public function __construct(
        BaseOrderService $baseOrderService,
        ArrayTransformerInterface $arrayTransformer,
        SerializerInterface $serializer,
        Filesystem $filesystem
    ) {
        $this->baseOrderService = $baseOrderService;
        $this->arrayTransformer = $arrayTransformer;
        $this->serializer = $serializer;
        $this->filesystem = $filesystem;
    }

    public function out(Order $order)
    {
        $message = $this->transformOrderToMessage($order);
        $fileName = $this->getFileName($order);
        /**
         * Получаем из настроек ftp и выгружаем по ftp
         */
    }

    /**
     * @param Order $order
     *
     * @return SourceMessage
     */
    public function transformOrderToMessage(Order $order)
    {
        /**
         * @todo
         *
         * Do some magic with order
         */
        $orderArray = [];

        $dto = $this->arrayTransformer->fromArray($orderArray, OrderDtoOut::class);
        $xml = $this->serializer->serialize($dto, 'xml');

        return new SourceMessage($this->getMessageId($order), OrderDtoOut::class, $xml);
    }

    /**
     * @param OrderDtoIn $orderDto
     *
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @return Order
     */
    public function transformDtoToOrder(OrderDtoIn $orderDto): Order
    {
        $orderArray = $this->arrayTransformer->toArray($orderDto);

        $order = Order::load($orderArray['id']);

        /**
         * @todo
         *
         * Do some magic with order
         */

        return $order;
    }

    /**
     * @param Order $order
     *
     * @return OrderDtoOut
     */
    public function transformOrderToDto(Order $order): OrderDtoOut
    {
        $dto = new OrderDtoOut();

        $dto->setId($order->getId());

        return $dto;
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    public function getMessageId(Order $order): string
    {
        if (null === $this->messageId) {
            $this->messageId = sprintf('order_%s_%s', $order->getId(), time());
        }

        return $this->messageId;
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    public function getFileName(Order $order): string
    {
        return sprintf('%s-%s.xml', $order->getDateInsert()->format('Ymd'), $order->getId());
    }

    /**
     * @param string $outPath
     *
     * @return OrderService
     * @throws IOException
     */
    public function setOutPath(string $outPath): OrderService
    {
        if (!$this->filesystem->exists($outPath)) {
            $this->filesystem->mkdir($outPath, '0775');
        }

        $this->outPath = $outPath;

        return $this;
    }
}
