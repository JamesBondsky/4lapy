<?php

namespace FourPaws\ProductAutoSort;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\EventManager;
use Bitrix\Main\Page\Asset;
use CIBlockElement;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\ProductAutoSort\UserType\ElementPropertyConditionUserType;
use Psr\Log\LoggerInterface;

/**
 * Class Event
 *
 * @package FourPaws\ProductAutoSort
 */
abstract class Event implements ServiceHandlerInterface
{
    /**
     * @param EventManager $eventManager
     * @return void
     */
    public static function initHandlers(EventManager $eventManager): void
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

        foreach (['OnAfterIBlockElementUpdate', 'OnAfterIblockElementAdd'] as $eventTYpe) {
            $eventManager->addEventHandler('iblock', $eventTYpe, [self::class, 'autosortProduct']);
        }
    }

    /**
     * Подключить jQuery для административной панели, чтобы обогатить функциональность кастомного свойства.
     */
    public static function includeJquery()
    {
        if (!\defined('ADMIN_SECTION')) {
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

    /**
     * Запустить автоматическое определение категорий для одного продукта.
     *
     * @param $arFields
     */
    public static function autosortProduct($arFields)
    {
        try {
            $productIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);

            $applyAutosortPropId = IblockUtils::getPropertyId($productIblockId, 'APPLY_AUTOSORT');

            if (
                !isset($arFields['IBLOCK_ID'], $arFields['ID'], $arFields['PROPERTY_VALUES'][$applyAutosortPropId])
                || $arFields['IBLOCK_ID'] != $productIblockId
                //Не отмечен флажок "Автоматически определить категории"
                || reset($arFields['PROPERTY_VALUES'][$applyAutosortPropId])['VALUE'] == 0
            ) {
                return;
            }

            /** @var \FourPaws\ProductAutoSort\ProductAutoSortService $autosort */
            $autosort = Application::getInstance()->getContainer()->get('product.autosort.service');

            $result = $autosort->defineProductsCategories([$arFields['ID']]);

            //Результат вызова не имеет смысла, т.к. не позволяет определить ошибку
            (new CIBlockElement())->SetElementSection($arFields['ID'], $result[$arFields['ID']], false, 0, $result[$arFields['ID']][0]);

            //Снятие флажка
            CIBlockElement::SetPropertyValuesEx($arFields['ID'], $arFields['IBLOCK_ID'], ['APPLY_AUTOSORT' => 0]);
        } catch (Exception $exception) {
            self::log()->error(
                sprintf(
                    "[%s] %s (%s)\n%s\n",
                    \get_class($exception),
                    $exception->getMessage(),
                    $exception->getCode(),
                    $exception->getTraceAsString()
                )
            );
        }
    }

    /**
     * @return LoggerInterface
     */
    private static function log(): LoggerInterface
    {
        return LoggerFactory::create('ProductAutoSortEvent');
    }
}
