<?php

namespace FourPaws\Search;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\EventManager;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\BaseServiceHandler;
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

    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);
        static::$searchService = Application::getInstance()->getContainer()->get('search.service');

        /**
         * Т.к. эластику без разницы добавление или обновление, используется соединение этих событий.
         */

        $module = 'iblock';
        foreach (['OnAfterIBlockElementUpdate', 'OnAfterIblockElementAdd'] as $eventType) {
            static::initHandler($eventType, [static::class, 'updateInElastic'], $module);
        }
        static::initHandler('OnAfterIBlockElementDelete', [static::class, 'deleteInElastic'], $module);

        $module = 'catalog';
        foreach (['OnPriceUpdate', 'OnPriceAdd'] as $eventType) {
            static::initHandler($eventType, [static::class, 'updateOfferInElasticOnPriceChange'], $module);
        }

        foreach (['OnProductUpdate', 'OnProductAdd'] as $eventType) {
            static::initHandler($eventType, [static::class, 'updateOfferInElasticOnCatalogProductChange'], $module);
        }
    }

    /**
     * @param $arFields
     */
    public function updateInElastic($arFields): void
    {
        try {
            $this->doActionInElastic(CatalogSyncMsg::ACTION_UPDATE, $arFields);
        } catch (Exception $exception) {
            $this->logException($exception);
        }
    }

    /**
     * Обновление торгового предложения, когда у него меняется цена.
     *
     * @param array $arFields
     */
    public function updateOfferInElasticOnPriceChange($arFields): void
    {
        try {
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

            $this->updateInElastic($offerFields);
        } catch (Exception $exception) {
            $this->logException($exception);
        }
    }

    /**
     * Обновление торгового предложения, когда у него меняются поля сущности "товар" из "торгового каталога".
     *
     * @param $productId
     */
    public function updateOfferInElasticOnCatalogProductChange($productId): void
    {
        $this->updateOfferInElasticOnPriceChange(['PRODUCT_ID' => (int)$productId]);
    }

    /**
     * @param $arFields
     */
    public function deleteInElastic($arFields): void
    {
        try {
            $this->doActionInElastic(CatalogSyncMsg::ACTION_DELETE, $arFields);
        } catch (Exception $exception) {
            $this->logException($exception);
        }
    }

    /**
     * @param string $action
     * @param array  $arFields
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    protected function doActionInElastic(string $action, array $arFields): void
    {
        if (!isset($arFields['ID'], $arFields['IBLOCK_ID'])) {
            return;
        }

        $entityType = $this->recognizeEntityType($arFields);
        if ('' == $entityType) {
            return;
        }

        $this->publishCatSyncMsg($action, $entityType, (int)$arFields['ID']);
    }

    /**
     * @param $exception
     */
    protected function logException(Throwable $exception): void
    {
        $this->logger->error(
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
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    protected function recognizeEntityType(array $arFields): string
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
     * @param string $action
     * @param string $entityType
     * @param int    $entityId
     */
    private function publishCatSyncMsg(string $action, string $entityType, int $entityId): void
    {
        $newCatSyncMsg = new CatalogSyncMsg($action, $entityType, $entityId);

        /** @noinspection PhpNonStrictObjectEqualityInspection */
        if (null !== self::$lastSyncMessage && $newCatSyncMsg->equals(self::$lastSyncMessage)) {
            /**
             * Предотвращение дублирования, если только что
             * было отправлено точно такое же (по содержимому!)
             * синхронизационное сообщение.
             */
            return;
        }

        static::$searchService->getIndexHelper()->publishSyncMessage($newCatSyncMsg);
        self::$lastSyncMessage = $newCatSyncMsg;
    }
}
