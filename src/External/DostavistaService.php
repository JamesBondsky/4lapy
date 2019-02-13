<?php

namespace FourPaws\External;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\External\Dostavista\Client;
use FourPaws\External\Dostavista\Model\CancelOrder;
use FourPaws\External\Dostavista\Model\Order;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use FourPaws\App\Application as App;
use JMS\Serializer\Serializer;

/**
 * Class SmsService
 *
 * @package FourPaws\External
 */
class DostavistaService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Serializer $serializer
     */
    protected $serializer;
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
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
}