<?php

namespace FourPaws\App;

use Bitrix\Main\EventManager;

interface EventHandlerInterface
{
    public function addEvents();
    
    /**
     * EventHandlerInterface constructor.
     *
     * @param \Bitrix\Main\EventManager $eventManager
     */
    public function __construct(EventManager $eventManager);
}