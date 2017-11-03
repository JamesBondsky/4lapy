<?php

namespace FourPaws\Catalog;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\EventManager;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

abstract class Event implements ServiceHandlerInterface
{
    public static function initHandlers(EventManager $eventManager)
    {
        foreach (['OnAfterIBlockElementUpdate', 'OnAfterIblockElementAdd'] as $eventTYpe) {
            $eventManager->addEventHandler('iblock', $eventTYpe, [self::class, 'updateProductInElastic']);
        }

        //TODO Удаление товара

        //TODO Добавление, обновление, удаление торгового предложения

        //TODO Добавление, обновление, удаление торгового бренда

    }

    /**
     * @param $arFields
     */
    public static function updateProductInElastic($arFields)
    {
        //Обработка только продуктов
        if (
            !isset($arFields['IBLOCK_ID'], $arFields['ID'])
            || $arFields['IBLOCK_ID'] != IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS)
        ) {
            return;
        }



    }

}
