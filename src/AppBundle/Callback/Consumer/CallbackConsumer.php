<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Callback\Consumer;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class CallbackConsumer
 *
 * @package FourPaws\Callback\Consumer
 */
class CallbackConsumer extends CallbackConsumerBase
{
    public const SUCCESS = 4;

    /**
     * @param AMQPMessage $msg The message
     *
     * @return bool
     */
    public function execute(AMQPMessage $msg): bool
    {
        $href = $msg->getBody();
        if (!empty($href)) {
            try {
                $res = $this->guzzle->send(new Request('get', $href));
            } catch (GuzzleException $e) {
                $this->log()->error('Сервис обартного звонка ответил ошибкой' . $e->getMessage() . ' на ссылку ' . $href);
                return false;
            }
            $data = json_decode($res->getBody()->getContents());

            if ((int)$data->result !== static::SUCCESS || $res->getStatusCode() !== 200) {
                $this->log()->error('Сервис обартного звонка ответил ошибкой на ссылку ' . $href . ' - ' . implode('|',
                        (array)$data), (array)$data);
                //публикуем заного с задержкой в 30 секунд
                sleep(30);
                try {
                    $container = App::getInstance()->getContainer();
                } catch (ApplicationCreateException $e) {
                    $this->log()->error('Ошибка загрузки контейнера');
                    return false;
                }
                $callBackProducer = $container->get('old_sound_rabbit_mq.callback_set_producer');
                /** @var Producer $callBackProducer */
                $callBackProducer->publish($href);
            }

            return true;
        }
        return false;
    }
}
