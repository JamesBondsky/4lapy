<?php

namespace FourPaws\IblockProps;

use Bitrix\Main\EventManager;
use FourPaws\App\BaseServiceHandler;

/**
 * Class Event
 * @package FourPaws\IblockProps
 */
class Event extends BaseServiceHandler
{
    /**
     *
     *
     * @param EventManager $eventManager
     *
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);
        $module = 'iblock';
        static::initHandlerCompatible('OnIBlockPropertyBuildList', [Location::class, 'GetUserTypeDescription'], $module);
        static::initHandlerCompatible('OnIBlockPropertyBuildList', [LinkToBasketRules::class, 'GetUserTypeDescription'],
            $module);
    }
}
