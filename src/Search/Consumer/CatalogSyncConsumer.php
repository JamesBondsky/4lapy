<?php

namespace FourPaws\Search\Consumer;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\Catalog\Model\Brand;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\BrandQuery;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Search\Model\CatalogSyncMsg;
use FourPaws\Search\SearchService;
use JMS\Serializer\Serializer;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

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

    public function __construct(Serializer $serializer, SearchService $searchService)
    {
        $this->includeBitrix();
        $this->serializer = $serializer;
        $this->searchService = $searchService;
        $this->setLogger(LoggerFactory::create('CatalogSyncConsumer'));
    }

    /**
     * @inheritdoc
     */
    public function execute(AMQPMessage $msg)
    {

        /** @var CatalogSyncMsg $cagalogSyncMessage */
        $cagalogSyncMessage = $this->extractMessageBody($msg);

        if (
            $cagalogSyncMessage->isForProductEntity()
            && ($cagalogSyncMessage->isForAddAction() || $cagalogSyncMessage->isForUpdateAction())
        ) {

            $this->updateProduct($cagalogSyncMessage->getEntityId());

        } elseif ($cagalogSyncMessage->isForProductEntity() && $cagalogSyncMessage->isForDeleteAction()) {

            $this->deleteProduct($cagalogSyncMessage->getEntityId());

        } elseif (
            $cagalogSyncMessage->isForOfferEntity()
            && ($cagalogSyncMessage->isForAddAction() || $cagalogSyncMessage->isForUpdateAction())
        ) {

            $this->updateOffer($cagalogSyncMessage->getEntityId());

        } elseif ($cagalogSyncMessage->isForOfferEntity() && $cagalogSyncMessage->isForDeleteAction()) {

            $this->deleteOffer($cagalogSyncMessage->getEntityId());

        } elseif (
            $cagalogSyncMessage->isForBrandEntity()
            && ($cagalogSyncMessage->isForAddAction() || $cagalogSyncMessage->isForUpdateAction())
        ) {

            $this->updateBrand($cagalogSyncMessage->getEntityId());

        } elseif ($cagalogSyncMessage->isForBrandEntity() && $cagalogSyncMessage->isForDeleteAction()) {

            $this->deleteBrand($cagalogSyncMessage->getEntityId());

        } else {

            $this->log()->alert(
                sprintf(
                    'Неподдерживаемый тип синхронизационного сообщения: type= %s , action = %s',
                    $cagalogSyncMessage->getEntityType(),
                    $cagalogSyncMessage->getAction()
                )
            );

        }

    }

    /**
     * @param AMQPMessage $msg
     *
     * @return CatalogSyncMsg
     */
    protected function extractMessageBody(AMQPMessage $msg)
    {
        return $this->serializer->deserialize(
            $msg->getBody(),
            CatalogSyncMsg::class,
            'json'
        );
    }

    /**
     * @param int $productId
     */
    private function updateProduct(int $productId)
    {
        $product = (new ProductQuery())->withFilter(['=ID' => $productId])
                                       ->exec()
                                       ->current();

        if (!($product instanceof Product)) {
            $this->log()->error(
                sprintf(
                    'Продукт #%d не найден',
                    $productId
                )
            );

            return;
        }

        $indexProductResult = $this->searchService->indexProduct($product);

        $this->log()->debug(
            sprintf(
                'Обновление продукта #%d: %s',
                $productId,
                ($indexProductResult) ? 'успех' : 'ошибка'
            )
        );

    }

    /**
     * @param int $productId
     *
     * @return bool
     */
    private function deleteProduct(int $productId)
    {
        $deleteProductResult = $this->searchService->deleteProduct($productId);

        $this->log()->debug(
            sprintf(
                'Удаление продукта #%d: %s',
                $productId,
                ($deleteProductResult ? 'успех' : 'ошибка')
            )
        );

        return true;
    }

    /**
     * @param int $offerId
     */
    private function updateOffer(int $offerId)
    {
        $offer = (new OfferQuery())->withFilter(['=ID' => $offerId])->exec()->current();

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

        $indexProductResult = $this->searchService->indexProduct($product);

        $this->log()->debug(
            sprintf(
                'Обновление продукта #%d по офферу #%d: %s',
                $product->getId(),
                $offerId,
                ($indexProductResult) ? 'успех' : 'ошибка'
            )
        );

    }

    /**
     * @param int $offerId
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
            CatalogSyncMsg::ACTION_UPDATE, CatalogSyncMsg::ENTITY_TYPE_PRODUCT,
            0
        );

        $dbProductList = (new ProductQuery())->withSelect(['ID'])
                                             ->withFilter(['=PROPERTY_BRAND' => $brand->getId()])
                                             ->doExec();
        while ($arProduct = $dbProductList->Fetch()) {

            $productId = (int)$arProduct['ID'];

            $catSyncMsg->withEntityId($productId);

            $this->searchService->publishSyncMessage($catSyncMsg);

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
     */
    public function deleteBrand(int $brandId)
    {
        //TODO Сделать запрос к эластику товаров этого бренда и удалить их
    }

    private function includeBitrix()
    {

        /**
         * TODO Вынести подключение Битрикса в пакет adv/bitrix-tools ?
         */
        defined('NO_KEEP_STATISTIC') || define('NO_KEEP_STATISTIC', 'Y');
        defined('NOT_CHECK_PERMISSIONS') || define('NOT_CHECK_PERMISSIONS', true);
        defined('NO_AGENT_CHECK') || define('NO_AGENT_CHECK', true);
        defined('PUBLIC_AJAX_MODE') || define('PUBLIC_AJAX_MODE', true);

        if (empty($_SERVER['DOCUMENT_ROOT'])) {
            $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../../../../');
        }

        $GLOBALS['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'];

        /** @noinspection PhpIncludeInspection */
        require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

    }

    /**
     * @return LoggerInterface
     */
    protected function log()
    {
        return $this->logger;
    }

}
