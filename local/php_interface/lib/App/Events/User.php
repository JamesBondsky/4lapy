<?php

namespace FourPaws\App\Events;

use FourPaws\App\AbstractEvent;

class User extends AbstractEvent
{
    const MODULE_ID = 'main';
    
    /**
     * @param $eventType
     * @param $callback
     * @param $includeFile
     * @param $sort
     */
    protected function addMainEventHandler($eventType, $callback, $includeFile = '', $sort = 100)
    {
        $this->addEventHandler('main',
                               $eventType,
                               $callback,
                               $includeFile,
                               $sort);
    }
    
    public function addEvents()
    {
        $this->addMainEventHandler('OnBeforeUserAdd', ['\FourPaws\User\UserService', 'checkSocserviseRegisterHandler']);
    }
}