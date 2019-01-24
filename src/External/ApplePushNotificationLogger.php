<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\External;

class ApplePushNotificationLogger extends \ApnsPHP_Log_Embedded
{
    protected $logMessages = [];

    public function log($sMessage)
    {
        $this->logMessages[] = $sMessage;
    }

    public function getLogMessages()
    {
        return $this->logMessages;
    }
}