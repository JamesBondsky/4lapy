<?php

namespace FourPaws\Callback\Consumer;

use GuzzleHttp\Psr7\Request;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class CallbackConsumer
 *
 * @package FourPaws\Callback\Consumer
 */
class CallbackConsumer extends CallbackConsumerBase
{
    const HREF = 'https://srv_03:F6RIikaO9QvhlZ7C@4584.vats-on.ru/execsvcscriptplain?name=[VATS-ON] SiteCallBack&startparam1=#phone#&startparam2=#dateTime#&async=0&timeout=#timeout#';
    
    /**
     * @param AMQPMessage $msg The message
     *
     * @return mixed false to reject and requeue, any other value to acknowledge
     * @throws \LogicException
     */
    public function execute(AMQPMessage $msg) : bool
    {
        $href    = $msg->getBody();
        $request = new Request('get', $href);
        $promise = $this->guzzle->sendAsync($request)->then(
            function () {
                $this->log()->info('Запрос отправлен');
            },
            function () {
                $this->log()->critical('произошла ошибка при выполнении');
            }
        );
        $promise->wait();
        
        return true;
    }
}