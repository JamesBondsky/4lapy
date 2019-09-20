<?php


namespace FourPaws\External;

use Zend\Json\Encoder as JsonEncoder;
use ZendService\Apple\Apns\Client\Message as ServiceClient;
use ZendService\Apple\Apns\Message as ApnsMessage;
use ZendService\Apple\Apns\Response\Message as MessageResponse;
use ZendService\Apple\Exception;


class ApplePushNotificationServiceClient extends ServiceClient
{
    public function send(ApnsMessage $message)
    {
        if (! $this->isConnected()) {
            throw new Exception\RuntimeException('You must first open the connection by calling open()');
        }

        $ret = $this->write($this->getPayloadJson($message->getPayload(), $message));
        if ($ret === false) {
            throw new Exception\RuntimeException('Server is unavailable; please retry later');
        }

        return new MessageResponse($this->read());
    }

    private function getPayloadJson($payload, $message)
    {
        // don't escape utf8 payloads unless json_encode does not exist.

        $payload = json_encode($payload);
        $length = strlen($payload);

        return pack('CNNnH*', 1, $message->getId(), $message->getExpire(), 32, $message->getToken())
            . pack('n', $length)
            . $payload;
    }
}
