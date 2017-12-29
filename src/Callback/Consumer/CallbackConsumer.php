<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Callback\Consumer;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class CallbackConsumer
 *
 * @package FourPaws\Callback\Consumer
 */
class CallbackConsumer extends CallbackConsumerBase
{
    /**
     * @param AMQPMessage $msg The message
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws GuzzleException
     * @throws \RuntimeException
     * @throws \LogicException
     * @return bool
     */
    public function execute(AMQPMessage $msg) : bool
    {
        //App::getInstance()->getContainer()->get('sms.service')->sendSms('очередь отработала', '9201612427');
        //$this->log()->debug('очередь отработала');
        //return false;
        $href = $msg->getBody();
        $res  = $this->guzzle->send(new Request('get', $href));
        $data = json_decode($res->getBody()->getContents());
        if ((int)$data->result !== 4 || $res->getStatusCode() !== 200) {
            $this->log()->critical('Сервис обартного звонка ответил ошибкой');
            App::getInstance()->getContainer()->get('old_sound_rabbit_mq.callback_set_producer')->publish(
                $href
            );
            
            return false;
        }
        
        return true;
    }
}
