<?php

namespace FourPaws\ProductAutoSort;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\EventManager;
use Bitrix\Main\Page\Asset;
use CIBlockElement;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\BaseServiceHandler;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\ProductAutoSort\UserType\ElementPropertyConditionUserType;
use Psr\Log\LoggerInterface;

/**
 * Class Event
 *
 * @package FourPaws\ProductAutoSort
 */
class Event extends BaseServiceHandler
{
    protected static $lockEvents = false;

    /**
     * @param EventManager $eventManager
     *
     * @return void
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);

        $module = 'main';
        //sort 1000;
        static::initHandlerCompatible('OnUserTypeBuildList',
            [ElementPropertyConditionUserType::class, 'getUserTypeDescription'], $module);

        static::initHandler('OnProlog', [self::class, 'includeJquery'], $module);

        $module = 'iblock';
        static::initHandler('OnAfterIBlockSectionDelete', [self::class, 'deleteEPCValue'], $module);

        static::initHandlerCompatible('OnAfterIBlockElementAdd', [self::class, 'autoSortProductOnCreate'], $module);
        static::initHandlerCompatible('OnAfterIBlockElementUpdate', [self::class, 'autoSortProductOnUpdate'], $module);
    }

    /**
     * Lock all events with cache
     */
    public static function lockEvents(): void
    {
        self::$lockEvents = true;
    }

    /**
     * Unlock all events with cache
     */
    public static function unlockEvents(): void
    {
        self::$lockEvents = false;
    }

    /**
     * @return bool
     */
    public static function isLockEvents(): bool
    {
        return self::$lockEvents;
    }

    /**
     * Подключить jQuery для административной панели, чтобы обогатить функциональность кастомного свойства.
     */
    public static function includeJquery(): void
    {
        if (static::isLockEvents()) {
            return;
        }

        if (!\defined('ADMIN_SECTION')) {
            return;
        }
        Asset::getInstance()->addJs('/local/include/js/jquery.min.js');
    }

    /**
     * Удаляет значения свойств элемента
     *
     * @param $arFields
     *
     * @throws ApplicationCreateException
     */
    public static function deleteEPCValue($arFields): void
    {
        if (static::isLockEvents()) {
            return;
        }

        if (!isset($arFields['ID']) || $arFields['ID'] <= 0) {
            return;
        }

        /** @var ProductAutoSortService $productAutoSortService */
        $productAutoSortService = Application::getInstance()->getContainer()->get('product.autosort.service');
        $productAutoSortService->deleteValue((int)$arFields['ID']);
    }

    public static function autoSortProductOnCreate($arFields): void
    {
        if (static::isLockEvents()) {
            return;
        }

        try {
            $productIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);

            if (
                !isset($arFields['IBLOCK_ID'], $arFields['ID'], $arFields['PROPERTY_VALUES']['APPLY_AUTOSORT'])
                || (int)$arFields['IBLOCK_ID'] !== $productIblockId
                //Не отмечен флажок "Автоматически определить категории"
                || (int)$arFields['PROPERTY_VALUES']['APPLY_AUTOSORT'] === 0
            ) {
                return;
            }

            static::autoSortProduct($arFields['ID'], $arFields['IBLOCK_ID']);
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
     * Запустить автоматическое определение категорий для одного продукта.
     *
     * @param $arFields
     */
    public static function autoSortProductOnUpdate($arFields): void
    {
        if (static::isLockEvents()) {
            return;
        }

        try {
            $productIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);

            $applyAutosortPropId = IblockUtils::getPropertyId($productIblockId, 'APPLY_AUTOSORT');

            if (
                !isset($arFields['IBLOCK_ID'], $arFields['ID'], $arFields['PROPERTY_VALUES'][$applyAutosortPropId])
                || (int)$arFields['IBLOCK_ID'] !== $productIblockId
                //Не отмечен флажок "Автоматически определить категории"
                || (int)reset($arFields['PROPERTY_VALUES'][$applyAutosortPropId])['VALUE'] === 0
            ) {
                return;
            }

            static::autoSortProduct($arFields['ID'], $arFields['IBLOCK_ID']);
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
     * @param int $id
     * @param int $iblockId
     *
     * @throws ApplicationCreateException
     */
    private static function autoSortProduct(int $id, int $iblockId) {
        /** @var \FourPaws\ProductAutoSort\ProductAutoSortService $autosort */
        $autosort = Application::getInstance()->getContainer()->get('product.autosort.service');

        $result = $autosort->defineProductsCategories([$id]);

        //Результат вызова не имеет смысла, т.к. не позволяет определить ошибку
        (new CIBlockElement())->SetElementSection($id, $result[$id], false, 0,
            $result[$id][0]);

        //Снятие флажка
        CIBlockElement::SetPropertyValuesEx($id, $iblockId, ['APPLY_AUTOSORT' => 0]);
    }

    /**
     * @return LoggerInterface
     */
    private static function log(): LoggerInterface
    {
        return LoggerFactory::create('ProductAutoSortEvent');
    }
}
