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
            $clientId = \COption::GetOptionString('articul.dostavista.delivery', 'client_id_test', '');
            $token = \COption::GetOptionString('articul.dostavista.delivery', 'token_test', '');
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
     */
    public function sendOrder(array $data)
    {
        //проверяем коннект
        if (!$this->client->checkConnection()) {
            $this->logger->error('Сервер достависты не отвечает!');
            return [
                'success' => false,
                'message' => 'Сервер достависты не отвечает!'
            ];
        }

        //пробуем отправить заказ в достависту
        try {
            $result = $this->client->send($data);
            $this->logger->info('well done');
            return [
                'success' => true,
                'message' => 'Заказ успешно импортирован в Достависту'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка импорта заказа'
            ];
        }
    }
}