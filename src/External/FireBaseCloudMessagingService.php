<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\External;

use FourPaws\External\Exception\FireBaseCloudMessagingException;
use GuzzleHttp\Client as HttpClient;
use paragraph1\phpFCM\Client;
use paragraph1\phpFCM\Message;
use paragraph1\phpFCM\Recipient\Device;
use paragraph1\phpFCM\Notification;

class FireBaseCloudMessagingService
{
    const API_KEY = 'AAAAxDpl7KU:APA91bF5dwjOWylTSf7v5CzXcWUkVdZIYHyoj78W1uhfo5TOXXuNrJDCMC7loFO2xnAiw1sFz0khQaZuNgLuLr2La9cGjkA88YpM0zB2QDmavShPJ-sFbZd2jmMrLp1Ki9fJS8T6_WWE';

    /**
     * @var HttpClient
     */
    protected $transport;

    /**
     * FireBaseCloudMessagingService constructor.
     */
    public function __construct()
    {
        $this->transport = new HttpClient();
    }

    /**
     * @param $token
     * @param $messageText
     * @param $messageId
     * @param $messageType
     * @return \Psr\Http\Message\ResponseInterface
     * @throws FireBaseCloudMessagingException
     */
    public function sendNotification($token, $messageText, $messageId, $messageType)
    {
        $client = new Client();
        $client->setApiKey(static::API_KEY);
        $client->injectHttpClient(new HttpClient());

        $message = new Message();
        $message->addRecipient(new Device($token));
        $message->setData([
            'body' => [
                // Обязательная часть (названия полей в данном случае важно) :
                'aps' => [
                    'badge' => 1, // красный кружок на иконке приложения с количеством оповещений
                    'alert' => $messageText, // текст, который будет показан пользователю в push- сообщении
                    'sound' => 'default', // можно указать звук при получении пуша
                ],
                // Опции
                'options' => [
                    'id' => $messageId, // Идентификатор события
                    'type'=> $messageType // Тип события
                ]
            ]
        ]);

        $response = $client->send($message);
        if ($response->getStatusCode() !== 200) {
            throw new FireBaseCloudMessagingException($response->getReasonPhrase(), $response->getStatusCode());
        }
        return $response;
    }
}