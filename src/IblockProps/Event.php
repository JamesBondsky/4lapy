<?php

namespace FourPaws\IblockProps;

use Bitrix\Main\EventManager;
use FourPaws\App\ServiceHandlerInterface;

/**
 * Class Event
 * @package FourPaws\IblockProps
 */
class Event implements ServiceHandlerInterface
{
    /**
     *
     *
     * @param EventManager $eventManager
     *
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        $eventManager->addEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            [
                Location::class,
                'GetUserTypeDescription',
            ]
        );
        $eventManager->addEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            [
                LinkToBasketRules::class,
                'GetUserTypeDescription',
            ]
        );
    }
}
