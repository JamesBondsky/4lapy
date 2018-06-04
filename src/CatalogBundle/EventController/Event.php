<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\CatalogBundle\EventController;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Catalog\PriceTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\EventManager;
use FourPaws\App\BaseServiceHandler;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\TaggedCacheHelper;

/**
 * Class Event
 *
 * Обработчики событий
 *
 * @package FourPaws\CatalogBundle\EventController
 */
class Event extends BaseServiceHandler
{
    /**
     * Блокировка событий, для очистки кеша.
     *
     * @var bool
     */
    protected static $lockEvents = false;

    /**
     * @param EventManager $eventManager
     *
     * @return mixed|void
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);

        $module = 'catalog';

        /** Очистка кеша при изменении количества и оффера*/
        static::initHandlerCompatible('OnStoreProductUpdate', [self::class, 'clearProductCache'], $module);
        static::initHandlerCompatible('OnStoreProductAdd', [self::class, 'clearProductCache'], $module);
        static::initHandlerCompatible('OnProductUpdate', [self::class, 'clearProductCache'], $module);
        static::initHandlerCompatible('OnProductAdd', [self::class, 'clearProductCache'], $module);

        $module = 'iblock';

        /** задание нулевой цены при создании оффера */
        static::initHandler('OnAfterIBlockElementAdd', [self::class, 'createOfferPrice'], $module);

        /** очистка кеша при изменении элемента инфоблока */
        static::initHandlerCompatible('OnAfterIBlockElementUpdate', [self::class, 'clearIblockItemCache'], $module);
    }

    /**
     * @param $id
     */
    public static function clearProductCache($id): void
    {
        if (!self::isLockEvents()) {
            TaggedCacheHelper::clearManagedCache([
                'catalog:offer:' . $id,
                'catalog:stocks:' . $id,
                'catalog:product:' . $id,
            ]);
        }
    }

    /**
     * @param $arFields
     * @throws IblockNotFoundException
     */
    public static function clearIblockItemCache($arFields): void
    {
        if (!self::isLockEvents()) {
            if (isset($arFields['IBLOCK_ID']) &&
                !\in_array((int)$arFields['IBLOCK_ID'], [
                    IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
                    IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS),
                ], true)
            ) {
                TaggedCacheHelper::clearManagedCache([
                    'iblock:item:' . $arFields['ID'],
                ]);
            }
        }
    }

    /**
     * @return bool
     */
    public static function isLockEvents(): bool
    {
        return self::$lockEvents;
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
     * @param $fields
     * @throws IblockNotFoundException
     * @throws \Exception
     */
    public static function createOfferPrice($fields): void
    {
        if ((int)$fields['IBLOCK_ID'] === (int)IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS)) {
            PriceTable::add([
                'PRODUCT_ID' => $fields['ID'],
                'CATALOG_GROUP_ID' => '2',
                'PRICE' => 0,
                'CURRENCY' => CurrencyManager::getBaseCurrency(),
                'PRICE_SCALE' => 0
            ]);
        }
    }
}
