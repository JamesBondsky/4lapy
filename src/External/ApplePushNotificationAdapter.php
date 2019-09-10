<?php


namespace FourPaws\External;


use Sly\NotificationPusher\Collection\DeviceCollection;
use Sly\NotificationPusher\Exception\PushException;
use Sly\NotificationPusher\Model\PushInterface;
use ZendService\Apple\Apns\Client\Message as ServiceClient;
use ZendService\Apple\Apns\Response\Message as ServiceResponse;

class ApplePushNotificationAdapter extends \Sly\NotificationPusher\Adapter\Apns
{
    /**
     * @var ServiceClient
     */
    protected $openedClient;

    protected function getOpenedServiceClient()
    {
        if (!isset($this->openedClient)) {
            $this->openedClient = $this->getOpenedClient(new ApplePushNotificationServiceClient());
        }

        return $this->openedClient;
    }

    public function push(PushInterface $push)
    {
        $client = $this->getOpenedServiceClient();

        $pushedDevices = new DeviceCollection();

        foreach ($push->getDevices() as $device) {
            /** @var \ZendService\Apple\Apns\Message $message */
            $message = $this->getServiceMessageFromOrigin($device, $push->getMessage());

            try {
                /** @var \ZendService\Apple\Apns\Response\Message $response */
                $response = $client->send($message);

                $responseArr = [
                    'id'    => $response->getId(),
                    'token' => $response->getCode(),
                ];
                $push->addResponse($device, $responseArr);

                if (ServiceResponse::RESULT_OK === $response->getCode()) {
                    $pushedDevices->add($device);
                } else {
                    $client->close();
                    unset($this->openedClient, $client);
                    // Assign returned new client to the in-scope/in-use $client variable
                    $client = $this->getOpenedServiceClient();
                }

                $this->response->addOriginalResponse($device, $response);
                $this->response->addParsedResponse($device, $responseArr);
            } catch (\RuntimeException $e) {
//                throw new PushException($e->getMessage());
            }
        }

        return $pushedDevices;
    }
}
