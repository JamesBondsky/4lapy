<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\External;

class ApplePushNotificationService
{
    const CERT_PATH = '/var/www/release/app/config/apple-push-notification-cert-old.pem';
    const PROCESSES_AMOUNT = 5; // количество потоков для отправки push'ей

    /** @var \ApnsPHP_Push_Server $server */
    private $server;

    /**
     * ApplePushNotificationService constructor.
     * @throws \ApnsPHP_Exception
     * @throws \ApnsPHP_Push_Server_Exception
     */
    public function __construct()
    {
        $this->server = new ApplePushNotificationServer(
            \ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION,
            static::CERT_PATH
        );

        $this->server->setLogger(new ApplePushNotificationLogger());
        // $this->server->setLogger(new \ApnsPHP_Log_Embedded());
        $this->server->setProviderCertificatePassphrase('lapy');
        $this->server->setProcesses(static::PROCESSES_AMOUNT);
        $this->server->start();
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
        $message = (new ApplePushNotificationMessage($token));
        $message->setBadge(1);
        $message->setSound();
        $message->setText($messageText);
        $message->setCustomProperty('type', $messageType);
        $message->setCustomProperty('id', $messageId);

        $this->server->add($message);
    }

    /**
     * @return array
     */
    public function getLogMessages()
    {
        /**
         * @var $logger ApplePushNotificationLogger
         */
        $logger = $this->server->getLogger();
        return $logger->getLogMessages();
    }
}