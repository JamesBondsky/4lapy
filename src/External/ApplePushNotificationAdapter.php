<?php


namespace FourPaws\External;


use Sly\NotificationPusher\Collection\DeviceCollection;
use Sly\NotificationPusher\Exception\PushException;
use Sly\NotificationPusher\Model\PushInterface;
use ZendService\Apple\Apns\Response\Message as ServiceResponse;

class ApplePushNotificationAdapter extends \Sly\NotificationPusher\Adapter\Apns
{
    protected $openedClient;

    protected function getOpenedServiceClient()
    {
        if (!isset($this->openedClient)) {
            $this->openedClient = $this->getOpenedClient(new ApplePushNotificationServiceClient());
        }

        return $this->openedClient;
    }


}
