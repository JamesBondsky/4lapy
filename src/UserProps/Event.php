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
        static::initHandler('OnUserTypeBuildList', [UserPropStoreList::class, 'getUserTypeDescription'], $module);
        static::initHandler('OnUserTypeBuildList', [UserPropLocation::class, 'getUserTypeDescription'], $module);
        static::initHandler('OnUserTypeBuildList', [UserPropWeekDay::class, 'getUserTypeDescription'], $module);
        static::initHandler('OnEpilog', [__CLASS__, 'addAdminScriptPropLocation'], $module);
        static::initHandler('OnAdminListDisplay', [__CLASS__, 'OnAdminListDisplayHandler'], $module);
    }

    public function addAdminScriptPropLocation()
    {
        Asset::getInstance()->addJs(
            '/local/templates/.default/components/bitrix/system.field.edit/sale_location/editScript.js'
        );
    }

    public function OnAdminListDisplayHandler()
    {
        \CUtil::InitJSCore(['jquery']);
    }
}
