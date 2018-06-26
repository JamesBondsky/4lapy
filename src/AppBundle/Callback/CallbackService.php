<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Callback;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Uri;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Class CallbackService
 *
 * @package FourPaws\Callback
 */
class CallbackService
{
    public const SUCCESS = 4;
    private $parameters;
    private $guzzle;
    private $logger;

    /**
     * CallbackService constructor.
     *
     * @param array           $parameters
     * @param ClientInterface $guzzle
     */
    public function __construct(array $parameters, ClientInterface $guzzle)
    {
        $this->parameters = $parameters;
        $this->guzzle = $guzzle;
        $this->logger = LoggerFactory::create('callback');
    }

    /**
     * @param string $phone
     * @param string $curDate
     * @param int    $timeout
     *
     * @throws ObjectException
     */
    public function send(string $phone, string $curDate = '', int $timeout = 0): void
    {
        $href = $this->getUri($phone, $curDate, $timeout);

        if (!empty($href)) {
            try {
                $container = App::getInstance()->getContainer();
                $callBackProducer = $container->get('old_sound_rabbit_mq.callback_set_producer');
            } catch (ApplicationCreateException $e) {
                $this->logger->error('ошибка получения продюсера');
            }
            /** @var Producer $callBackProducer */
            $callBackProducer->publish($href);
        }
    }

    /**
     * @param string $phone
     * @param string $curDate
     * @param int    $timeout
     *
     * @return bool
     * @throws ObjectException
     */
    public function sendImmediate(string $phone, string $curDate = '', int $timeout = 0): bool
    {
        $href = $this->getUri($phone, $curDate, $timeout);

        if (!empty($href)) {
            try {
                $res = $this->guzzle->send(new Request('get', $href));
            } catch (GuzzleException $e) {
                $this->logger->error('Сервис обартного звонка ответил ошибкой' . $e->getMessage() . ' на ссылку ' . $href);
                return false;
            }
            $data = json_decode($res->getBody()->getContents());

            if ((int)$data->result !== static::SUCCESS || $res->getStatusCode() !== 200) {
                $this->logger->error('Сервис обартного звонка ответил ошибкой на ссылку ' . $href . ' - ' . implode('|',
                        (array)$data), (array)$data);
                return false;
            }

            return true;
        }
        return false;
    }

    /**
     * @param string $phone
     * @param string $curDate
     * @param int    $timeout
     *
     * @return string
     * @throws ObjectException
     */
    private function getUri(string $phone, string $curDate = '', int $timeout = 0): string
    {
        if (empty($this->parameters['baseUri']) || empty($this->parameters['pass']) || empty($this->parameters['login'])) {
            $this->logger->error('не заданы параметры');
            return '';
        }
        if (empty($phone)) {
            $this->logger->info('не указан телефон');
            return '';
        }
        if (empty($curDate)) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $date = new DateTime();
            $curDate = $date->format('Y-m-d H:i:s');
        }
        $uri = new Uri($this->parameters['baseUri']);
        $uri->addParams(
            [
                'name'        => '[VATS-ON] SiteCallBack',
                'async'       => 0,
                'startparam1' => $phone,
                'startparam2' => $curDate,
                'timeout'     => $timeout,
            ]
        );
        $uri->setPass($this->parameters['pass']);
        $uri->setUser($this->parameters['login']);
        $uri->setHost($uri->getUser() . ':' . $uri->getPass() . '@' . $uri->getHost());

        return $uri->getUri();
    }
}
