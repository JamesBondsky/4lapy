<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\CatalogBundle\EventController;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\EventManager;
use FourPaws\App\Application;
use FourPaws\App\BaseServiceHandler;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\Search\Helper\IndexHelper;
use RuntimeException;

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
        static::initHandlerCompatible('OnStoreProductUpdate', [static::class, 'clearProductCache'], $module);
        static::initHandlerCompatible('OnStoreProductAdd', [static::class, 'clearProductCache'], $module);
        static::initHandlerCompatible('OnProductUpdate', [static::class, 'clearProductCache'], $module);
        static::initHandlerCompatible('OnProductAdd', [static::class, 'clearProductCache'], $module);

        $module = 'iblock';

        /** очистка кеша при изменении элемента инфоблока */
        static::initHandlerCompatible('OnAfterIBlockElementUpdate', [static::class, 'clearIblockItemCache'], $module);

        /** запуск переиндексации товаров при изменении товара */
        static::initHandlerCompatible('OnAfterIBlockElementUpdate', [static::class, 'reindexProduct'], $module);
        static::initHandlerCompatible('OnAfterIBlockElementUpdate', [static::class, 'reindexOffer'], $module);
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
     * @param array $arFields
     */
    public static function clearIblockItemCache($arFields): void
    {
        if (!self::isLockEvents()) {
            TaggedCacheHelper::clearManagedCache([
                'iblock:item:' . $arFields['ID'],
            ]);
        }
    }

    /**
     * @param $fields
     *
     * @throws RuntimeException
     * @throws IblockNotFoundException
     * @throws ApplicationCreateException
     */
    public static function reindexProduct($fields): void
    {
        if ((int)$fields['IBLOCK_ID'] === (int)IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS)) {
            /** @var IndexHelper $indexHelper */
            $indexHelper = Application::getInstance()->getContainer()->get('search.index_helper');
            $product = (new ProductQuery())->withFilterParameter('ID', $fields['ID'])->exec()->first();
            if ($product) {
                $indexHelper->indexProduct($product);
            }
        }
    }

    /**
     * @param $fields
     *
     * @throws RuntimeException
     * @throws IblockNotFoundException
     * @throws ApplicationCreateException
     */
    public static function reindexOffer($fields): void
    {
        if ((int)$fields['IBLOCK_ID'] === (int)IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS)) {
            /** @var IndexHelper $indexHelper */
            $indexHelper = Application::getInstance()->getContainer()->get('search.index_helper');
            /** @var Offer $offer */
            $offer = OfferQuery::getById((int)$fields['ID']);
            if ($offer) {
                $indexHelper->indexProduct($offer->getProduct());
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
}
