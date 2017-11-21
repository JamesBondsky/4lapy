<?php

namespace FourPaws\Search;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\EventManager;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Search\Model\CatalogSyncMsg;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class EventHandlers implements ServiceHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var CatalogSyncMsg
     */
    private static $lastSyncMessage;

    /**
     * @var SearchService
     */
    protected $searchService;

    public function __construct()
    {
        $this->searchService = Application::getInstance()->getContainer()->get('search.service');
        $this->setLogger(LoggerFactory::create('CatalogEvent'));
    }

    public static function initHandlers(EventManager $eventManager)
    {
        $myself = new self;

        /**
         * Т.к. эластику без разницы добавление или обновление, используется соединение этих событий.
         */

        foreach (['OnAfterIBlockElementUpdate', 'OnAfterIblockElementAdd'] as $eventType) {

            $eventManager->addEventHandler('iblock', $eventType, [$myself, 'updateInElastic']);
        }
        $eventManager->addEventHandler(
            'iblock',
            'OnAfterIBlockElementDelete',
            [$myself, 'deleteInElastic']
        );

        foreach (['OnPriceUpdate', 'OnPriceAdd'] as $eventType) {
            $eventManager->addEventHandler(
                'catalog',
                $eventType,
                [$myself, 'updateOfferInElasticOnPriceChange']
            );
        }

        foreach (['OnProductUpdate', 'OnProductAdd'] as $eventType) {
            $eventManager->addEventHandler(
                'catalog',
                $eventType,
                [$myself, 'updateOfferInElasticOnCatalogProductChange']
            );
        }
    }

    /**
     * @param $arFields
     */
    public function updateInElastic($arFields)
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
    public function updateOfferInElasticOnPriceChange($arFields)
    {
        try {
            if (!isset($arFields['PRODUCT_ID']) || $arFields['PRODUCT_ID'] <= 0) {
                return;
            }

            $offerFields = (new OfferQuery())->withFilter(['=ID' => (int)$arFields['PRODUCT_ID']])
                                             ->withSelect(['IBLOCK_ID', 'ID'])
                                             ->doExec()
                                             ->Fetch();

            if (false == $offerFields) {
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
    public function updateOfferInElasticOnCatalogProductChange($productId)
    {
        $this->updateOfferInElasticOnPriceChange(['PRODUCT_ID' => (int)$productId]);
    }

    /**
     * @param $arFields
     */
    public function deleteInElastic($arFields)
    {
        try {

            $this->doActionInElastic(CatalogSyncMsg::ACTION_DELETE, $arFields);

        } catch (Exception $exception) {

            $this->logException($exception);

        }

    }

    /**
     * @param string $action
     * @param array $arFields
     */
    protected function doActionInElastic(string $action, array $arFields)
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
     * @param string $action
     * @param string $entityType
     * @param int $entityId
     */
    private function publishCatSyncMsg(string $action, string $entityType, int $entityId)
    {
        $newCatSyncMsg = new CatalogSyncMsg($action, $entityType, $entityId);

        /** @noinspection PhpNonStrictObjectEqualityInspection */
        if (!is_null(self::$lastSyncMessage) && $newCatSyncMsg->equals(self::$lastSyncMessage)) {
            /**
             * Предотвращение дублирования, если только что
             * было отправлено точно такое же (по содержимому!)
             * синхронизационное сообщение.
             */
            return;
        }

        $this->searchService->publishSyncMessage($newCatSyncMsg);
        self::$lastSyncMessage = $newCatSyncMsg;
    }

    /**
     * @param $exception
     */
    protected function logException(Throwable $exception)
    {
        $this->logger->error(
            sprintf(
                "[%s] %s (%s)\n%s\n",
                get_class($exception),
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
     */
    protected function recognizeEntityType(array $arFields): string
    {
        if (!isset($arFields['IBLOCK_ID'])) {
            return '';
        }
        $iblockId = (int)$arFields['IBLOCK_ID'];

        if (IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS) === $iblockId) {

            return CatalogSyncMsg::ENTITY_TYPE_PRODUCT;

        } elseif (IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS) === $iblockId) {

            return CatalogSyncMsg::ENTITY_TYPE_OFFER;

        } elseif (IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::BRANDS) === $iblockId) {

            return CatalogSyncMsg::ENTITY_TYPE_BRAND;

        }

        return '';
    }
}
