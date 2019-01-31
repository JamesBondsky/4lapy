<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AdminBundle\EventController;

use Bitrix\Main\EventManager;
use Bitrix\Main\EventResult;
use FourPaws\App\BaseServiceHandler;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;

/**
 * Class Event
 *
 * @package FourPaws\DeliveryBundle
 */
class Event extends BaseServiceHandler
{
    /**
     * @param EventManager $eventManager
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);
        $module = 'main';
        static::initHandler('OnAdminContextMenuShow', [self::class, 'addImportButton'],
            $module);
    }

    /**
     * @return EventResult
     */
    public static function addImportButton(&$items): void
    {
        $comparingIblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::COMPARING);
        if(strpos($GLOBALS["APPLICATION"]->GetCurPageParam(), 'iblock_list_admin.php?IBLOCK_ID='.$comparingIblockId) !== false){
            $items[] = array("TEXT"=>"Импорт/экспорт", "TITLE"=>"Импорт/экспорт", "LINK"=>'/bitrix/admin/compare_import.php');
        }
    }
}
