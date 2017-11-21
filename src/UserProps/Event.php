<?php
/**
 * Created by PhpStorm.
 * User: Vampi
 * Date: 21.11.2017
 * Time: 21:47
 */

namespace FourPaws\UserProps;

use Bitrix\Main\EventManager;
use Bitrix\Main\Page\Asset;
use FourPaws\App\ServiceHandlerInterface;

abstract class Event implements ServiceHandlerInterface
{
    /**
     * @param EventManager $eventManager
     *
     * @return void
     */
    public static function initHandlers(EventManager $eventManager)
    {
        $eventManager->addEventHandler('main',
                                       'OnUserTypeBuildList',
                                       [
                                           UserPropLocation::class,
                                           'GetUserTypeDescription',
                                       ]);
        
        $eventManager->addEventHandler('iblock',
                                       'OnIBlockPropertyBuildList',
                                       [
                                           'IblockPropLocation',
                                           'GetUserTypeDescription',
                                       ]);
        
        $eventManager->addEventHandler('main',
                                       'OnEpilog',
                                       [
                                           __CLASS__,
                                           'addAdminScriptPropLocation',
                                       ]);
    }
    
    public function addAdminScriptPropLocation()
    {
        Asset::getInstance()
             ->addJs('/local/templates/.default/components/bitrix/system.field.edit/sale_location/editScript.js');
    }
}
