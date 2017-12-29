<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Callback\Consumer;

use FourPaws\App\Application as App;
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
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \RuntimeException
     * @throws \LogicException
     * @return mixed false to reject and requeue, any other value to acknowledge
     */
    public function execute(AMQPMessage $msg) : bool
    {
        $href = $msg->getBody();
        $res  = $this->guzzle->send(new Request('get', $href));
        $data = json_decode($res->getBody()->getContents());
        if ((int)$data->result !== 4 || $res->getStatusCode() !== 200) {
            $this->log()->critical('Сервис обартного звонка ответил ошибкой');
            App::getInstance()->getContainer()->get('old_sound_rabbit_mq.callback_serv_producer')->publish(
                $href
            );
        }
        
        return true;
    }
}
