<?php

namespace FourPaws\External;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\External\Dostavista\Client;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class SmsService
 *
 * @package FourPaws\External
 */
class DostavistaService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Client
     */
    protected $client;

    public function __construct()
    {
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
                    $this->logger->info('Order ' . $data['point'][1]['client_order_id'] . ' success create in Dostavista service');
                }
            } catch (\Exception $e) {
                $result = [
                    'success' => false,
                    'message' => 'Ошибка импорта заказа'
                ];
                $this->logger->error('Order ' . $data['point'][1]['client_order_id'] . ' import failed in "Dostavista" service', $result);
            }
        } else {
            $result = [
                'success' => $res['success'],
                'message' => $res['message']
            ];
            $this->logger->error('Connection failed with "Dostavista" service', $result);
        }
        return $result;
    }

    /**
     * @param int $orderId
     * @param array $data
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function editOrder(int $orderId, array $data)
    {
        //проверяем коннект
        $res = $this->client->checkConnection();
        if ($res['success']) {
            //пробуем отправить заказ в достависту
            try {
                $result = $this->client->editOrder($orderId, $data);
                if ($result['success']) {
                    $this->logger->info('Order ' . $data['point'][1]['client_order_id'] . ' success create in Dostavista service');
                }
            } catch (\Exception $e) {
                $result = [
                    'success' => false,
                    'message' => 'Ошибка импорта заказа'
                ];
                $this->logger->error('Order ' . $data['point'][1]['client_order_id'] . ' import failed in "Dostavista" service', $result);
            }
        } else {
            $result = [
                'success' => $res['success'],
                'message' => $res['message']
            ];
            $this->logger->error('Connection failed with "Dostavista" service', $result);
        }
        return $result;
    }
}