<?php
namespace FourPaws\UserProps;

use Bitrix\Main\{
    EventManager, Page\Asset
};
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
                                           'getUserTypeDescription',
                                       ]);
        
        $eventManager->addEventHandler('iblock',
                                       'OnIBlockPropertyBuildList',
                                       [
                                           IblockPropLocation::class,
                                           'getUserTypeDescription',
                                       ]);
        
        $eventManager->addEventHandler('main',
                                       'OnEpilog',
                                       [
                                           __CLASS__,
                                           'addAdminScriptPropLocation',
                                       ]);
    
        $eventManager->addEventHandler('main',
                                      'OnAdminListDisplay',
                                      [
                                          __CLASS__,
                                          'OnAdminListDisplayHandler',
                                      ]);
    }
    
    public function addAdminScriptPropLocation()
    {
        Asset::getInstance()
             ->addJs('/local/templates/.default/components/bitrix/system.field.edit/sale_location/editScript.js');
    }
    public function OnAdminListDisplayHandler()
    {
        \CUtil::InitJSCore( array('jquery' ));
    }
}
