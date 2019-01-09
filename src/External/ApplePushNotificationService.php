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

class ApplePushNotificationService
{
    const CERT_PATH = '/var/www/release/common/ssl/push/lapy_prod_merge.pem';
    const PROCESSES_AMOUNT = 5; // количество потоков для отправки push'ей
    const DELAY_BETWEEN_SEND = 100000; // задержка между отправками push'ей (в микросекундах)

    /** @var \ApnsPHP_Push_Server $server */
    private $server;

    /**
     * ApplePushNotificationService constructor.
     * @throws \ApnsPHP_Exception
     * @throws \ApnsPHP_Push_Server_Exception
     */
    public function __construct()
    {
        // $this->server = new \ApnsPHP_Push_Server(\ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION,static::CERT_PATH);

        // $this->server->setLogger(new \ApnsPHP_Log_Embedded());
        // $this->server->getLogger()->lockSend();
        // $this->server->setProviderCertificatePassphrase('lapy');
        // $this->server->setProcesses(static::PROCESSES_AMOUNT);
        // $this->server->start();
    }

    /**
     * @param $token
     * @param $messageText
     * @param $messageId
     * @param $messageType
     * @throws \ApnsPHP_Message_Exception
     */
    public function sendNotification($token, $messageText, $messageId, $messageType)
    {
        $message = (new \ApnsPHP_Message($token));
        $message->setBadge(1);
        $message->setSound();
        $message->setText($messageText);
        $message->setCustomProperty('type', $messageType);
        $message->setCustomProperty('id', $messageId);

        $this->server->add($message);

        // делаем задержку между отправками
        usleep(static::DELAY_BETWEEN_SEND);
    }
}