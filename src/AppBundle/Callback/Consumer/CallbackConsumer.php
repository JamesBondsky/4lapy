<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Callback\Consumer;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Uri;
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
                /**чекаем дату перед отправкой и делаем подмену */
                $curDate = null;
                $date = new DateTime();
                $uri = new Uri($href);
                $explodeList = explode('&', $uri->getQuery());
                $dateParams = null;

                foreach ($explodeList as $item) {
                    [$name, $val] = explode('=', $item);
                    if ($name === 'startparam2') {
                        $dateParams = new DateTime(urldecode($val), 'Y-m-d H:i:s');
                        break;
                    }

                    if ($name === 'startparam1') {
                        if (preg_match('~^[78+]~', $val) == 0) {
                            return;
                        }
                    }
                }

                if ($dateParams === null || $date->getTimestamp() >= $dateParams->getTimestamp()) {
                    $date->add('+30seconds'); // добавляем 30 секунд, роли не играет, но еслио тправка задержится спасет
                    $curDate = $date->format('Y-m-d H:i:s');
                }

                if ($curDate !== null) {
                    preg_match_all('/^https?:\/\/(.*):(.*)@' . $uri->getHost() . '/', $href, $matches);
                    $uri->deleteParams(['startparam2']);
                    $uri->addParams(['startparam2' => $curDate]);
                    $uri->setPass($matches[2]);
                    $uri->setUser($matches[1]);
                    $uri->setHost($uri->getUser() . ':' . $uri->getPass() . '@' . $uri->getHost());
                    $href = $uri->getUri();
                }

                $res = $this->guzzle->send(new Request('get', $href));
            } catch (GuzzleException $e) {
                $this->log()->error('Сервис обартного звонка ответил ошибкой' . $e->getMessage() . ' на ссылку ' . $href);die;
                return true;
            } catch (\Exception $e) {
                $this->log()->error('Ошибка: ' . $e->getMessage() . ' на ссылку ' . $href);
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
