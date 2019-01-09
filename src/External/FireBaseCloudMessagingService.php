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

        $note = new Notification($messageText, '');
        $note->setIcon('notification_icon_resource_name')
            ->setColor('#ffffff')
            ->setBadge(1);

        $message = new Message();
        $message->addRecipient(new Device($token));
        $message->setNotification($note)
            ->setData([
                'id' => $messageId,
                'type' => $messageType,
            ]);

        $response = $client->send($message);
        if ($response->getStatusCode() !== 200) {
            throw new FireBaseCloudMessagingException($response->getReasonPhrase(), $response->getStatusCode());
        }
        return $response;
    }
}