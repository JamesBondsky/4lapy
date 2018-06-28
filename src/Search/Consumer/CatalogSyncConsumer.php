<?php

namespace FourPaws\Search\Consumer;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Elastica\Exception\InvalidException;
use FourPaws\Catalog\Model\Brand;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\BrandQuery;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\Search\Model\CatalogSyncMsg;
use FourPaws\Search\SearchService;
use JMS\Serializer\Serializer;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Class CatalogSyncConsumer
 *
 * @package FourPaws\Search\Consumer
 */
class CatalogSyncConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * CatalogSyncConsumer constructor.
     *
     * @param Serializer    $serializer
     * @param SearchService $searchService
     *
     * @throws RuntimeException
     */
    public function __construct(Serializer $serializer, SearchService $searchService)
    {
        $this->includeBitrix();
        $this->serializer = $serializer;
        $this->searchService = $searchService;
        $this->setLogger(LoggerFactory::create('CatalogSyncConsumer'));
    }

    /**
     * @param AMQPMessage $msg
     *
     * @throws RuntimeException
     * @return mixed|void
     */
    public function execute(AMQPMessage $msg)
    {
        /** @var CatalogSyncMsg $catalogSyncMessage */
        $catalogSyncMessage = $this->extractMessageBody($msg);

        //Если сообщение свежее
        if (time() === $catalogSyncMessage->getTimestamp()) {
            /**
             * Добавить задержку, чтобы MySQL успел закомитить все изменения по товару
             * и избежать ситуации, когда из базы будет прочитано неактуальное состояние.
             */
            $sleepSeconds = 1;
            $this->log()->debug(sprintf('Sleep for %ss', $sleepSeconds));
            sleep($sleepSeconds);
        }

        if (
            $catalogSyncMessage->isForProductEntity()
            && ($catalogSyncMessage->isForAddAction() || $catalogSyncMessage->isForUpdateAction())
        ) {
            $this->updateProduct($catalogSyncMessage->getEntityId());
        } elseif ($catalogSyncMessage->isForProductEntity() && $catalogSyncMessage->isForDeleteAction()) {
            $this->deleteProduct($catalogSyncMessage->getEntityId());
        } elseif (
            $catalogSyncMessage->isForOfferEntity()
            && ($catalogSyncMessage->isForAddAction() || $catalogSyncMessage->isForUpdateAction())
        ) {
            $this->updateOffer($catalogSyncMessage->getEntityId());
        } elseif ($catalogSyncMessage->isForOfferEntity() && $catalogSyncMessage->isForDeleteAction()) {
            $this->deleteOffer($catalogSyncMessage->getEntityId());
        } elseif (
            $catalogSyncMessage->isForBrandEntity()
            && ($catalogSyncMessage->isForAddAction() || $catalogSyncMessage->isForUpdateAction())
        ) {
            $this->updateBrand($catalogSyncMessage->getEntityId());
        } elseif ($catalogSyncMessage->isForBrandEntity() && $catalogSyncMessage->isForDeleteAction()) {
            $this->deleteBrand($catalogSyncMessage->getEntityId());
        } else {
            $this->log()->alert(
                sprintf(
                    'Неподдерживаемый тип синхронизационного сообщения: type= %s , action = %s',
                    $catalogSyncMessage->getEntityType(),
                    $catalogSyncMessage->getAction()
                )
            );
        }
    }

    /**
     * @param int $productId
     *
     * @throws RuntimeException
     */
    private function updateProduct(int $productId)
    {
        $product = ProductQuery::getById($productId);

        if ($product === null) {
            $this->log()->error(
                sprintf(
                    'Продукт #%d не найден',
                    $productId
                )
            );

            return;
        }

        try {
            $indexProductResult = $this->searchService->getIndexHelper()->indexProduct($product);
        } catch (InvalidException $e) {
            $this->log()->error(
                sprintf(
                    'Ошибка: %s',
                    $e->getMessage()
                )
            );

            return;
        }

        TaggedCacheHelper::clearManagedCache([
            'iblock:item:' . $product->getId(),
        ]);

        $this->log()->debug(
            sprintf(
                'Обновление продукта #%d: %s',
                $productId,
                $indexProductResult ? 'успех' : 'ошибка'
            )
        );
    }

    /**
     * @param int $productId
     *
     * @throws RuntimeException
     */
    private function deleteProduct(int $productId)
    {
        try {
            $deleteProductResult = $this->searchService->getIndexHelper()->deleteProduct($productId);
        } catch (InvalidException $e) {
            $this->log()->error(
                sprintf(
                    'Ошибка: %s',
                    $e->getMessage()
                )
            );

            return;
        }

        TaggedCacheHelper::clearManagedCache([
            'iblock:item:' . $productId,
        ]);

        $this->log()->debug(
            sprintf(
                'Удаление продукта #%d: %s',
                $productId,
                ($deleteProductResult ? 'успех' : 'ошибка')
            )
        );
    }

    /**
     * @param int $offerId
     *
     * @throws RuntimeException
     */
    private function updateOffer(int $offerId)
    {
        $offer = OfferQuery::getById($offerId);

        if (!($offer instanceof Offer)) {
            $this->log()->error(
                sprintf(
                    'Оффер #%d не найден',
                    $offerId
                )
            );

            return;
        }

        $product = $offer->getProduct();

        if ($product->getId() <= 0) {
            $this->log()->error(
                sprintf(
                    'По офферу #%d не найден продукт',
                    $offerId
                )
            );

            return;
        }

        try {
            $indexProductResult = $this->searchService->getIndexHelper()->indexProduct($product);
        } catch (InvalidException $e) {
            $this->log()->error(
                sprintf(
                    'Ошибка: %s',
                    $e->getMessage()
                )
            );

            return;
        }

        TaggedCacheHelper::clearManagedCache([
            'iblock:item:' . $offer->getId(),
            'iblock:item:' . $product->getId(),
        ]);

        $this->log()->debug(
            sprintf(
                'Обновление продукта #%d по офферу #%d: %s',
                $product->getId(),
                $offerId,
                $indexProductResult ? 'успех' : 'ошибка'
            )
        );
    }

    /**
     * @param int $offerId
     *
     * @throws RuntimeException
     */
    public function deleteOffer(int $offerId)
    {
        //Удаление оффера сводится к обновлению продукта с новым списком офферов
        $this->updateOffer($offerId);
    }

    /**
     * @param int $brandId
     */
    public function updateBrand(int $brandId)
    {
        //Обновление бренда сводится к обновлению всех его продуктов.
        $brand = (new BrandQuery())->withFilter(['=ID' => $brandId])->exec()->current();

        if (!($brand instanceof Brand)) {
            $this->log()->error(
                sprintf(
                    'Бренд #%d не найден',
                    $brandId
                )
            );

            return;
        }

        $catSyncMsg = new CatalogSyncMsg(
            CatalogSyncMsg::ACTION_UPDATE,
            CatalogSyncMsg::ENTITY_TYPE_PRODUCT,
            0
        );

        $dbProductList = (new ProductQuery())->withSelect(['ID'])
            ->withFilter(['=PROPERTY_BRAND' => $brand->getId()])
            ->doExec();
        while ($arProduct = $dbProductList->Fetch()) {
            $productId = (int)$arProduct['ID'];

            $catSyncMsg->withEntityId($productId);

            try {
                $this->searchService->getIndexHelper()->publishSyncMessage($catSyncMsg);
            } catch (InvalidException $e) {
                $this->log()->error(
                    sprintf(
                        'Ошибка: %s',
                        $e->getMessage()
                    )
                );

                return;
            }

            $this->log()->debug(
                sprintf(
                    'Обновление продукта #%d по бренду #%d поставлено в очередь',
                    $productId,
                    $brandId
                )
            );
        }
    }

    /**
     * @param int $brandId
     *
     * @throws RuntimeException
     */
    public function deleteBrand(int $brandId)
    {
        try {
            $deleteBrandResult = $this->searchService->getIndexHelper()->deleteBrand($brandId);
        } catch (InvalidException $e) {
            $this->log()->error(
                sprintf(
                    'Ошибка: %s',
                    $e->getMessage()
                )
            );

            return;
        }

        $this->log()->debug(
            sprintf(
                'Удаление бренда #%d: %s',
                $brandId,
                ($deleteBrandResult ? 'успех' : 'ошибка')
            )
        );
    }

    /**
     * @param AMQPMessage $msg
     *
     * @return CatalogSyncMsg
     */
    protected function extractMessageBody(AMQPMessage $msg): CatalogSyncMsg
    {
        return $this->serializer->deserialize(
            $msg->getBody(),
            CatalogSyncMsg::class,
            'json'
        );
    }

    private function includeBitrix()
    {
        \defined('NO_KEEP_STATISTIC') || \define('NO_KEEP_STATISTIC', 'Y');
        \defined('NOT_CHECK_PERMISSIONS') || \define('NOT_CHECK_PERMISSIONS', true);
        \defined('NO_AGENT_CHECK') || \define('NO_AGENT_CHECK', true);
        \defined('PUBLIC_AJAX_MODE') || \define('PUBLIC_AJAX_MODE', true);

        if (empty($_SERVER['DOCUMENT_ROOT'])) {
            $_SERVER['DOCUMENT_ROOT'] = \dirname(__DIR__, 5) . '/';
        }

        $GLOBALS['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'];

        /** @noinspection PhpIncludeInspection */
        require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
    }

    /**
     * @return LoggerInterface
     */
    protected function log(): LoggerInterface
    {
        return $this->logger;
    }
}
