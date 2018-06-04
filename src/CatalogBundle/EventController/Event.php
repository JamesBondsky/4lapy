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
use FourPaws\App\Application;
use FourPaws\App\BaseServiceHandler;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Model\Share;
use FourPaws\BitrixOrm\Query\ShareQuery;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\TaggedCacheHelper;
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
        static::initHandlerCompatible('OnStoreProductUpdate', [self::class, 'clearProductCache'], $module);
        static::initHandlerCompatible('OnStoreProductAdd', [self::class, 'clearProductCache'], $module);
        static::initHandlerCompatible('OnProductUpdate', [self::class, 'clearProductCache'], $module);
        static::initHandlerCompatible('OnProductAdd', [self::class, 'clearProductCache'], $module);

        $module = 'iblock';

        /** задание нулевой цены при создании оффера */
        static::initHandler('OnAfterIBlockElementAdd', [self::class, 'createOfferPrice'], $module);

        /** очистка кеша при изменении элемента инфоблока */
        static::initHandlerCompatible('OnAfterIBlockElementUpdate', [self::class, 'clearIblockItemCache'], $module);

        /** запуск переиндексации товаров при изменении товара */
        static::initHandlerCompatible('OnAfterIBlockElementUpdate', [self::class, 'reindexProduct'], $module);
        static::initHandlerCompatible('OnAfterIBlockElementUpdate', [self::class, 'reindexOffer'], $module);
        static::initHandlerCompatible('OnAfterIBlockElementAdd', [self::class, 'reindexShareOffers'], $module);
        static::initHandlerCompatible('OnAfterIBlockElementUpdate', [self::class, 'reindexShareOffers'], $module);
        static::initHandlerCompatible('OnAfterIBlockElementDelete', [self::class, 'reindexShareOffers'], $module);
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
            if ($product = (new ProductQuery())->withFilterParameter('ID', $fields['ID'])->exec()->first()) {
                self::doReindexProduct($product);
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
            /** @var Offer $offer */
            if ($offer = OfferQuery::getById((int)$fields['ID'])) {
                self::doReindexProduct($offer->getProduct());
            }
        }
    }

    /**
     * @param $fields
     * @throws ApplicationCreateException
     * @throws IblockNotFoundException
     */
    public static function reindexShareOffers($fields): void
    {
        if (isset($fields['ACTIVE']) &&
            (int)$fields['IBLOCK_ID'] === (int)IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS)
        ) {
            /** @var Share $share */
            if ($share = (new ShareQuery())
                ->withFilter(['ID' => $fields['ID']])
                ->exec()
                ->first()
            ) {

                $productIds = [];
                /** @var Offer $offer */
                foreach ($share->getProducts() as $offer) {
                    $productId = $offer->getCml2Link();
                    $productIds[$productId] = $productId;
                }

                if (!empty($productIds)) {
                    $products = (new ProductQuery())->withFilterParameter('ID', $productIds)->exec();
                    foreach ($products as $product) {
                        self::doReindexProduct($product);
                    }
                }
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

    /**
     * @param Product $product
     * @throws ApplicationCreateException
     */
    protected static function doReindexProduct(Product $product)
    {
        Application::getInstance()->getContainer()->get('search.index_helper')
            ->indexProduct($product);
    }
}
