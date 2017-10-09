<?php

namespace FourPaws\App;

use Bitrix\Main\EventManager;

/**
 * Class AbstractEvent
 *
 * @package FourPaws\App
 */
abstract class AbstractEvent implements EventHandlerInterface
{
    protected $eventManager;
    
    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }
    
    /**
     * @param $fromModuleId
     * @param $eventType
     * @param $callback
     * @param $includeFile
     * @param $sort
     */
    protected function addEventHandler($fromModuleId, $eventType, $callback, $includeFile = '', $sort = 100)
    {
        $this->eventManager->addEventHandler($fromModuleId, $eventType, $callback, $includeFile, $sort);
        $this->eventManager;
    }
}