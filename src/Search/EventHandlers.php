<?php

namespace FourPaws\Search;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\BaseServiceHandler;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Model\Share;
use FourPaws\BitrixOrm\Query\ShareQuery;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Search\Model\CatalogSyncMsg;
use Throwable;

class EventHandlers extends BaseServiceHandler
{

    protected static $loggerName = 'event_catalog';
    /**
     * @var SearchService
     */
    protected static $searchService;
    /**
     * @var CatalogSyncMsg
     */
    private static $lastSyncMessage;

    /**
     * @param EventManager $eventManager
     *
     * @throws ApplicationCreateException
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);
        static::$searchService = Application::getInstance()->getContainer()->get('search.service');

        /**
         * Т.к. эластику без разницы добавление или обновление, используется соединение этих событий.
         */

        $module = 'iblock';
        foreach (['OnAfterIBlockElementUpdate', 'OnAfterIblockElementAdd'] as $eventType) {
            static::initHandlerCompatible($eventType, [self::class, 'updateInElastic'], $module);
        }
        static::initHandlerCompatible('OnAfterIBlockElementDelete', [self::class, 'deleteInElastic'], $module);

        $module = 'catalog';
        foreach (['PriceOnUpdate', 'PriceOnAdd'] as $eventType) {
            static::initHandler($eventType, [self::class, 'updateOfferInElasticOnPriceChange'], $module);
        }

        /** Не переиндексируем на обновлении продукта каталога
        foreach (['OnProductUpdate', 'OnProductAdd'] as $eventType) {
            static::initHandlerCompatible($eventType, [self::class, 'updateOfferInElasticOnCatalogProductChange'], $module);
        } */

        foreach (['StoreProductOnUpdate', 'StoreProductOnAdd'] as $eventType) {
//            static::initHandler($eventType, [self::class, 'updateOfferInElasticOnStocksChange'], $module);
        }
    }

    /**
     * @param $arFields
     */
    public static function updateInElastic($arFields): void
    {
        try {
            if ($arFields['ACTIVE'] === 'N' &&
                (int)$arFields['IBLOCK_ID'] === IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS)
            ) {
                self::deleteInElastic($arFields);
            } else {
                self::doActionInElastic(CatalogSyncMsg::ACTION_UPDATE, $arFields);
            }
        } catch (Exception $exception) {
            self::logException($exception);
        }
    }

    /**
     * Обновление торгового предложения, когда у него меняется цена.
     *
     * @param Event $event
     */
    public static function updateOfferInElasticOnPriceChange(Event $event): void
    {
        try {
            $arFields = $event->getParameter('fields');
            if (!isset($arFields['PRODUCT_ID']) || $arFields['PRODUCT_ID'] <= 0) {
                return;
            }

            $offerFields = [];
            $offer = OfferQuery::getById((int)$arFields['PRODUCT_ID']);
            if ($offer !== null) {
                $offerFields = $offer->toArray();
            }

            if (empty($offerFields)) {
                return;
            }

            self::updateInElastic($offerFields);
        } catch (Exception $exception) {
            self::logException($exception);
        }
    }

    /**
     * @param Event $event
     */
    public static function updateOfferInElasticOnStocksChange(Event $event): void
    {
        $fields = $event->getParameter('fields');
        if (!$fields['PRODUCT_ID']) {
            return;
        }

        $message = new CatalogSyncMsg(
            CatalogSyncMsg::ACTION_UPDATE_STOCKS,
            CatalogSyncMsg::ENTITY_TYPE_OFFER,
            $fields['PRODUCT_ID']
        );

        self::publishCatSyncMsg($message);
    }

    /**
     * Обновление торгового предложения, когда у него меняются поля сущности "товар" из "торгового каталога".
     *
     * @param $productId
     */
    public static function updateOfferInElasticOnCatalogProductChange($productId): void
    {
        self::updateOfferInElasticOnPriceChange(['PRODUCT_ID' => (int)$productId]);
    }

    /**
     * @param $arFields
     */
    public static function deleteInElastic($arFields): void
    {
        try {
            self::doActionInElastic(CatalogSyncMsg::ACTION_DELETE, $arFields);
        } catch (Exception $exception) {
            self::logException($exception);
        }
    }

    /**
     * @param string $action
     * @param array  $arFields
     *
     * @throws IblockNotFoundException
     */
    protected static function doActionInElastic(string $action, array $arFields): void
    {
        if (!isset($arFields['ID'], $arFields['IBLOCK_ID'])) {
            return;
        }

        $entityType = self::recognizeEntityType($arFields);
        if ('' !== $entityType) {
            $message = new CatalogSyncMsg($action, $entityType, (int)$arFields['ID']);
            self::publishCatSyncMsg($message);
        } else {
            foreach (self::getDependantEntities($arFields) as $id => $entityType) {
                $message = new CatalogSyncMsg($action, $entityType, $id);
                self::publishCatSyncMsg($message);
            }
        }
    }

    /**
     * @param $exception
     */
    protected static function logException(Throwable $exception): void
    {
        static::getLogger()->error(
            sprintf(
                "[%s] %s (%s)\n%s\n",
                \get_class($exception),
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getTraceAsString()
            )
        );
    }

    /**
     * @param array $arFields
     *
     * @return string
     * @throws IblockNotFoundException
     */
    protected static function recognizeEntityType(array $arFields): string
    {
        if (!isset($arFields['IBLOCK_ID'])) {
            return '';
        }
        $iblockId = (int)$arFields['IBLOCK_ID'];

        if (IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS) === $iblockId) {
            return CatalogSyncMsg::ENTITY_TYPE_PRODUCT;
        }

        if (IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS) === $iblockId) {
            return CatalogSyncMsg::ENTITY_TYPE_OFFER;
        }

        if (IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::BRANDS) === $iblockId) {
            return CatalogSyncMsg::ENTITY_TYPE_BRAND;
        }

        return '';
    }

    /**
     * @param array $arFields
     * @return array
     * @throws IblockNotFoundException
     */
    private static function getDependantEntities(array $arFields): array
    {
        $result = [];
        if (isset($arFields['IBLOCK_ID'])) {
            $iblockId = (int)$arFields['IBLOCK_ID'];
            if (isset($arFields['ACTIVE']) &&
                (IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES) === $iblockId)
            ) {
                /** @var Share $share */
                if ($share = (new ShareQuery())
                    ->withFilter(['ID' => $arFields['ID']])
                    ->exec()
                    ->first()
                ) {
                    /** @var Offer $offer */
                    foreach ($share->getProducts() as $offer) {
                        $result[$offer->getId()] = CatalogSyncMsg::ENTITY_TYPE_OFFER;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param CatalogSyncMsg $message
     */
    private static function publishCatSyncMsg(CatalogSyncMsg $message): void
    {
        /** @noinspection PhpNonStrictObjectEqualityInspection */
        if (null !== self::$lastSyncMessage && $message->equals(self::$lastSyncMessage)) {
            /**
             * Предотвращение дублирования, если только что
             * было отправлено точно такое же (по содержимому!)
             * синхронизационное сообщение.
             */
            return;
        }

        static::$searchService->getIndexHelper()->publishSyncMessage($message);
        self::$lastSyncMessage = $message;
    }
}
