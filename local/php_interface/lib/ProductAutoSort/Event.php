<?php

namespace FourPaws\ProductAutoSort;

use Bitrix\Main\EventManager;
use Bitrix\Main\Page\Asset;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\ProductAutoSort\UserType\ElementPropertyConditionUserType;

abstract class Event implements ServiceHandlerInterface
{
    public static function initHandlers(EventManager $eventManager)
    {
        $eventManager->addEventHandler(
            'main',
            'OnUserTypeBuildList',
            [ElementPropertyConditionUserType::class, 'getUserTypeDescription'],
            false,
            1000
        );

        $eventManager->addEventHandler('main', 'OnProlog', [self::class, 'includeJquery']);

    }

    /**
     * Подключить jQuery для административной панели, чтобы обогатить функциональность кастомного свойства.
     */
    public static function includeJquery()
    {
        if (!defined('ADMIN_SECTION')) {
            return;
        }
        Asset::getInstance()->addJs('/local/include/js/jquery.min.js');
    }

}
