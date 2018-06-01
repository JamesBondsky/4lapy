<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserProps;

use Bitrix\Main\EventManager;
use Bitrix\Main\Page\Asset;
use FourPaws\App\BaseServiceHandler;

class Event extends BaseServiceHandler
{
    /**
     * @param EventManager $eventManager
     *
     * @return void
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);

        $module = 'main';
        static::initHandlerCompatible('OnUserTypeBuildList', [UserPropStoreList::class, 'getUserTypeDescription'],
            $module);
        static::initHandlerCompatible('OnUserTypeBuildList', [UserPropLocation::class, 'getUserTypeDescription'],
            $module);
        static::initHandlerCompatible('OnUserTypeBuildList', [UserPropWeekDay::class, 'getUserTypeDescription'],
            $module);
        static::initHandlerCompatible('OnEpilog', [self::class, 'addAdminScriptPropLocation'], $module);
        static::initHandlerCompatible('OnAdminListDisplay', [self::class, 'OnAdminListDisplayHandler'], $module);
    }

    public static function addAdminScriptPropLocation(): void
    {
        Asset::getInstance()->addJs(
            '/local/templates/.default/components/bitrix/system.field.edit/sale_location/editScript.js'
        );
    }

    public static function OnAdminListDisplayHandler(): void
    {
        \CUtil::InitJSCore(['jquery']);
    }
}
