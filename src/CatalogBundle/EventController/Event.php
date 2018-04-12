<?php

namespace FourPaws\CatalogBundle\EventController;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\EventManager;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\ServiceHandlerInterface;
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
class Event implements ServiceHandlerInterface
{
    /**
     * Блокировка событий, для очистки кеша.
     *
     * @var bool
     */
    protected static $lockEvents = false;

    /**
     * @var EventManager
     */
    protected static $eventManager;

    /**
     * @param EventManager $eventManager
     *
     * @return mixed|void
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        self::$eventManager = $eventManager;
        /** Очистка кеша при изменении количества и оффера*/
        self::initHandler('OnStoreProductUpdate', [static::class, 'clearProductCache']);
        self::initHandler('OnStoreProductAdd', [static::class, 'clearProductCache']);
        self::initHandler('OnProductUpdate', [static::class, 'clearProductCache']);
        self::initHandler('OnProductAdd', [static::class, 'clearProductCache']);

        /** очистка кеша при изменении элемента инфоблока */
        self::initHandler('OnAfterIBlockElementUpdate', [static::class, 'clearIblockItemCache'], 'iblock');

        /** запуск переиндексации товаров при изменении товара */
        self::initHandler('OnAfterIBlockElementUpdate', [static::class, 'reindexProduct'], 'iblock');
        self::initHandler('OnAfterIBlockElementUpdate', [static::class, 'reindexOffer'], 'iblock');
    }

    /**
     *
     *
     * @param string $eventName
     * @param callable $callback
     * @param string $module
     *
     */
    public static function initHandler(string $eventName, callable $callback, string $module = 'catalog'): void
    {
        self::$eventManager->addEventHandler(
            $module,
            $eventName,
            $callback
        );
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
                'catalog:product:' . $id
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
            $offer = (new OfferQuery())->withFilterParameter('ID', $fields['ID'])->exec()->first();
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
