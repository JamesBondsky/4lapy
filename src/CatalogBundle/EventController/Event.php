<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\CatalogBundle\EventController;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Catalog\PriceTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Entity\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\BaseServiceHandler;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\CatalogBundle\Exception\NoSectionsForProductException;
use FourPaws\CatalogBundle\Service\CategoriesService;
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

        $module = 'main';
        static::initHandlerCompatible('OnBuildGlobalMenu', [
            self::class,
            'addProductReportToAdminMenu',
        ], $module);

        $module = 'catalog';

        /** Очистка кеша при изменении количества и оффера*/
        static::initHandlerCompatible('OnStoreProductUpdate', [
            self::class,
            'clearProductCache',
        ], $module);
        static::initHandlerCompatible('OnStoreProductAdd', [
            self::class,
            'clearProductCache',
        ], $module);
        static::initHandlerCompatible('OnProductUpdate', [
            self::class,
            'clearProductCache',
        ], $module);
        static::initHandlerCompatible('OnProductAdd', [
            self::class,
            'clearProductCache',
        ], $module);

        $module = 'iblock';

        /** задание нулевой цены при создании оффера */
        static::initHandler('OnAfterIBlockElementAdd', [
            self::class,
            'createOfferPrice',
        ], $module);

        /** очистка кеша при изменении элемента инфоблока */
        static::initHandlerCompatible('OnAfterIBlockElementUpdate', [
            self::class,
            'clearIblockItemCache',
        ], $module);

        static::initHandler('SectionOnAfterUpdate', [
            self::class,
            'updateMainProductSectionD7',
        ], $module);
        static::initHandlerCompatible('OnAfterIBlockSectionUpdate', [
            self::class,
            'updateMainProductSection',
        ], $module);

        /** Замена домена svg для лендингов */
        static::initHandler('OnEndBufferContent', [
            self::class,
            'fixLandingSvg',
        ], 'main');
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
                'PRICE_SCALE' => 0,
            ]);
        }
    }

    /**
     * @param array $fields
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function updateMainProductSection(array $fields): void
    {
        if ($fields['ID'] && $fields['ACTIVE'] && $fields['ACTIVE'] === BitrixUtils::BX_BOOL_FALSE) {
            static::updateProductSections($fields['ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentTypeException
     */
    public static function updateMainProductSectionD7(BitrixEvent $event)
    {
        $id = $event->getParameter('id');
        $fields = $event->getParameter('fields');
        if ($id['ID'] && $fields['ACTIVE'] && $fields['ACTIVE'] === BitrixUtils::BX_BOOL_FALSE) {
            static::updateProductSections($id['ID']);
        }
    }

    /**
     * @param int $categoryId
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ApplicationCreateException
     * @throws LoaderException
     */
    protected static function updateProductSections(int $categoryId): void
    {
        $categoryProducts = (new ProductQuery())
            ->withFilterParameter('IBLOCK_SECTION_ID', $categoryId)
            ->exec();

        foreach ($categoryProducts as $product) {
            /** @var CategoriesService $categoryService */
            $categoryService = Application::getInstance()->getContainer()->get(CategoriesService::class);
            try {
                $categories = $categoryService->getActiveByProduct($product);
                /** @var Category $maxDepthCategory */
                $maxDepthCategory = null;
                /** @var Category $category */
                foreach ($categories as $category) {
                    if ((null === $maxDepthCategory) ||
                        ($maxDepthCategory->getDepthLevel() < $category->getDepthLevel())
                    ) {
                        $maxDepthCategory = $category;
                    }
                    $category->getDepthLevel();
                }
                $e = new \CIBlockElement();
                $e->Update($product->getId(), ['IBLOCK_SECTION_ID' => $maxDepthCategory->getId()]);
            } catch (NoSectionsForProductException $e) {
                // ничего не нужно делать
            }
        }
    }

    /**
     * @param $adminMenu
     * @param $moduleMenu
     */
    public static function addProductReportToAdminMenu(&$adminMenu, &$moduleMenu)
    {
        foreach ($moduleMenu as $i => $menuItem) {
            if ($menuItem['parent_menu'] === 'global_menu_store' &&
                $menuItem['items_id'] === 'menu_sale_stat'
            ) {
                $moduleMenu[$i]['items'][] = [
                    'text' => 'Отчет по наличию товаров',
                    'title' => 'Отчет по наличию товаров',
                    'url' => '/local/admin/products_report.php?lang=' . LANG,
                    'more_url' => ''
                ];
            }
        }
    }

    /**
     * @todo HARD CODE
     *
     * @param $buffer
     */
    public static function fixLandingSvg(&$buffer)
    {
        $context = \Bitrix\Main\Application::getInstance()->getContext();

        if ($context->getRequest()->get('landing')) {
            $buffer = \preg_replace(
                \sprintf(
                    '~(xlink:href="https?://)%s/~',
                    $context->getServer()->getHttpHost()
                ),
                \sprintf('$1%s/',
                    $context->getRequest()->get('landing')
                ), $buffer
            );
        }
    }
}
