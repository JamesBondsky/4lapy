<?php

namespace FourPaws\External;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\External\Dostavista\Client;
use FourPaws\External\Dostavista\Model\CancelOrder;
use FourPaws\External\Dostavista\Model\Order;
use Bitrix\Sale\Order as BitrixOrder;
use FourPaws\SapBundle\Dto\Out\Orders\OrderStatus;
use FourPaws\SapBundle\Enum\SapOrder;
use FourPaws\SapBundle\Exception\NotFoundOrderUserException;
use FourPaws\SapBundle\Service\Orders\StatusService;
use FourPaws\SapBundle\Service\SapOutFile;
use FourPaws\SapBundle\Service\SapOutInterface;
use FourPaws\SapBundle\Source\SourceMessage;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use FourPaws\App\Application as App;
use JMS\Serializer\Serializer;
use FourPaws\SapBundle\Dto\Out\Orders\OrderStatus as OrderStatusDtoOut;
use FourPaws\UserBundle\Repository\UserRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SmsService
 *
 * @package FourPaws\External
 */
class DostavistaService implements LoggerAwareInterface, SapOutInterface
{
    use LoggerAwareTrait, SapOutFile;

    /**
     * @var Serializer $serializer
     */
    protected $serializer;
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var string
     */
    private $outPath;
    /**
     * @var string
     */
    private $outPrefix;
    /**
     * @var string
     */
    private $messageId;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * OrderService constructor.
     *
     * @param Serializer $serializer
     * @param UserRepository $userRepository
     * @param Filesystem $filesystem
     * @param ContainerInterface $container
     */
    public function __construct(Serializer $serializer, UserRepository $userRepository, Filesystem $filesystem, ContainerInterface $container)
    {
        $this->serializer = $serializer;
        $this->userRepository = $userRepository;
        $this->filesystem = $filesystem;
        $this->container = $container;
        $testMode = (\COption::GetOptionString('articul.dostavista.delivery', 'dev_mode', '') == BaseEntity::BITRIX_TRUE);
        if ($testMode) {
            $clientId = \COption::GetOptionString('articul.dostavista.delivery', 'client_id_dev', '');
            $token = \COption::GetOptionString('articul.dostavista.delivery', 'token_dev', '');
        } else {
            $clientId = \COption::GetOptionString('articul.dostavista.delivery', 'client_id_prod', '');
            $token = \COption::GetOptionString('articul.dostavista.delivery', 'token_prod', '');
        }
        $this->client = new Client($testMode, $clientId, $token);

        $this->setLogger(LoggerFactory::create('dostavista'));
    }

    /**
     * @param array $data
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addOrder(array $data)
    {
        //проверяем коннект
        $res = $this->client->checkConnection();
        if ($res['success']) {
            //пробуем отправить заказ в достависту
            try {
                $result = $this->client->addOrder($data);
                if ($result['success']) {
                    $this->logger->info('Order ' . $data['point'][1]['client_order_id'] . ' success create in Dostavista service', $data);
                }
            } catch (\Exception $e) {
                $result = [
                    'success' => false,
                    'message' => 'Ошибка импорта заказа',
                    'data' => $data
                ];
                $this->logger->error('Order ' . $data['point'][1]['client_order_id'] . ' import failed in "Dostavista" service', $result);
            }
        } else {
            $result = [
                'success' => $res['success'],
                'message' => $res['message'],
                'connection' => false,
                'data' => $data
            ];
            $this->logger->error('Connection failed with "Dostavista" service', $result);
        }
        return $result;
    }

    /**
     * @param string $orderId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function cancelOrder(string $orderId)
    {
        //проверяем коннект
        $res = $this->client->checkConnection();
        if ($res['success']) {
            //пробуем отменить заказ в достависте
            try {
                $result = $this->client->cancelOrder($orderId);
                if ($result['success']) {
                    $this->logger->info('Order ' . $orderId . ' success canceled in Dostavista service', $result);
                } else {
                    $this->logger->info('Order ' . $orderId . ' error canceled in Dostavista service', $result);
                }
            } catch (\Exception $e) {
                $result = [
                    'success' => false,
                    'message' => 'Ошибка импорта заказа'
                ];
                $this->logger->error('Order ' . $orderId . ' cancel failed in "Dostavista" service', $result);
            }
        } else {
            $result = [
                'success' => $res['success'],
                'message' => $res['message'],
                'connection' => false
            ];
            $this->logger->error('Connection failed with "Dostavista" service', $result);
        }
        return $result;
    }

    /**
     * @param Order $order
     */
    public function dostavistaOrderAdd(Order $order)
    {
        /** @noinspection MissingService */
        $producer = App::getInstance()->getContainer()->get('old_sound_rabbit_mq.dostavista_orders_add_producer');
        $producer->publish($this->serializer->serialize($order, 'json'));
    }

    /**
     * @param CancelOrder $order
     */
    public function dostavistaOrderCancel(CancelOrder $order)
    {
        /** @noinspection MissingService */
        $producer = App::getInstance()->getContainer()->get('old_sound_rabbit_mq.dostavista_orders_cancel_producer');
        $producer->publish($this->serializer->serialize($order, 'json'));
    }


    /**
     * @param BitrixOrder $order
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function out(BitrixOrder $order)
    {
        $this->setOutPath($this->container->getParameter('sap.directory.out'));
        $this->setOutPrefix('ORDER_STATUS_');
        $message = $this->transformOrderToMessage($order);
        $this->filesystem->dumpFile($this->getFileName($order), $message->getData());
    }

    /**
     * @param BitrixOrder $order
     *
     * @return string
     */
    public function getFileName($order): string
    {
        return \sprintf(
            '/%s/%s%s.xml',
            \trim($this->outPath, '/'),
            $this->outPrefix,
            $order->getField('ACCOUNT_NUMBER')
        );
    }

    /**
     * @param BitrixOrder $order
     *
     * @return SourceMessage
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function transformOrderToMessage(BitrixOrder $order): SourceMessage
    {
        $orderDto = new OrderStatusDtoOut();

        try {
            $orderUser = $this->userRepository->find($order->getUserId());
        } catch (ConstraintDefinitionException | InvalidIdentifierException $e) {
            $orderUser = null;
        }

        if (null === $orderUser) {
            throw new NotFoundOrderUserException(
                \sprintf(
                    'Пользователь с id %s не найден, заказ #%s',
                    $order->getUserId(),
                    $order->getId()
                )
            );
        }
        $sapStatus = array_flip(StatusService::STATUS_DOSTAVISTA_MAP)[$order->getField('STATUS_ID')];

        $orderDto
            ->setId($order->getField('ACCOUNT_NUMBER'))
            ->setStatus($sapStatus)
            ->setDeliveryType(SapOrder::DELIVERY_TYPE_DOSTAVISTA);

        $xml = $this->serializer->serialize($orderDto, 'xml');
        return new SourceMessage($this->getMessageId($order), OrderStatusDtoOut::class, $xml);
    }

    /**
     * @param BitrixOrder $order
     *
     * @return string
     */
    public function getMessageId(BitrixOrder $order): string
    {
        if (null === $this->messageId) {
            $this->messageId = \sprintf('order_status_%s_%s', $order->getId(), \time());
        }

        return $this->messageId;
    }
}