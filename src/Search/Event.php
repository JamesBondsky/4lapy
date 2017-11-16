<?php

namespace FourPaws\Search;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\EventManager;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Search\Model\CatalogSyncMsg;
use JMS\Serializer\Serializer;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class Event implements ServiceHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Producer
     */
    protected $catalogSyncProducer;

    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct()
    {
        $this->catalogSyncProducer = Application::getInstance()
                                                ->getContainer()
                                                ->get('old_sound_rabbit_mq.catalog_sync_producer');
        $this->serializer = Application::getInstance()
                                       ->getContainer()
                                       ->get('jms_serializer');
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
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementDelete', [$myself, 'deleteInElastic']);
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
        $this->catalogSyncProducer->publish(
            $this->serializer->serialize(
                new CatalogSyncMsg($action, $entityType, $entityId),
                'json'
            )
        );
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
