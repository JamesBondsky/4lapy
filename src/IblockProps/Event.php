<?php

namespace FourPaws\IblockProps;

use Bitrix\Main\EventManager;
use FourPaws\App\ServiceHandlerInterface;

class Event implements ServiceHandlerInterface
{
    public static function initHandlers(EventManager $eventManager)
    {
        $eventManager->addEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            [
                Location::class,
                'GetUserTypeDescription',
            ]
        );
    }
}
