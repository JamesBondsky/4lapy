<?php

namespace FourPaws\ProductAutoSort;

use Bitrix\Main\EventManager;
use Bitrix\Main\Page\Asset;
use FourPaws\App\Application;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\ProductAutoSort\UserType\ElementPropertyConditionUserType;

abstract class Event implements ServiceHandlerInterface
{
    /**
     * @param EventManager $eventManager
     *
     * @return void
     */
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

        $eventManager->addEventHandler(
            'iblock',
            'OnAfterIBlockSectionDelete',
            [self::class, 'deleteEPCValue']
        );
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

    /**
     * Удаляет значения свойств элемента
     *
     * @param $arFields
     */
    public static function deleteEPCValue($arFields)
    {
        if (!isset($arFields['ID']) || $arFields['ID'] <= 0) {
            return;
        }

        /** @var ProductAutoSortService $productAutoSortService */
        $productAutoSortService = Application::getInstance()->getContainer()->get('product.autosort.service');
        $productAutoSortService->deleteValue((int)$arFields['ID']);
    }
}
