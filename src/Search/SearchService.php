<?php

namespace FourPaws\Search;

use Adv\Bitrixtools\Tools\EnvType;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Elastica\Client;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Search;
use FourPaws\Catalog\Model\Product;
use FourPaws\Search\Enum\DocumentType;
use FourPaws\Search\Model\CatalogSyncMsg;
use FourPaws\Search\Model\ProductSearchResult;
use JMS\Serializer\Serializer;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class SearchService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Index
     */
    protected $catalogIndex;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Producer
     */
    private $catalogSyncProducer;

    public function __construct(Client $client, Serializer $serializer, Factory $factory, Producer $catalogSyncProducer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->setLogger(LoggerFactory::create('SearchService'));
        $this->factory = $factory;
        $this->catalogSyncProducer = $catalogSyncProducer;
    }

    /**
     * @return Index
     */
    public function getCatalogIndex()
    {
        if (is_null($this->catalogIndex)) {
            $this->catalogIndex = $this->client->getIndex($this->getIndexName('catalog'));
        }

        return $this->catalogIndex;
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function indexProduct(Product $product)
    {
        $responseSet = $this->getCatalogIndex()->addDocuments(
            [$this->factory->makeProductDocument($product)]
        );

        if (!$responseSet->isOk()) {

            $this->log()->error(
                $responseSet->getError(),
                [
                    'productId' => $product->getId(),
                ]
            );

            return false;
        }

        return true;
    }

    public function deleteProduct(int $productId)
    {

        $responseSet = $this->getCatalogIndex()->deleteDocuments([new Document($productId)]);

        if (!$responseSet->isOk()) {
            $this->log()->error(
                $responseSet->getError(),
                [
                    'productId' => $productId,
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * @param CatalogSyncMsg $catalogSyncMsg
     */
    public function publishSyncMessage(CatalogSyncMsg $catalogSyncMsg)
    {
        $this->catalogSyncProducer->publish(
            $this->serializer->serialize($catalogSyncMsg, 'json')
        );
    }

    public function searchProducts(
        array $filter,
        array $sort,
        array $nav,
        array $aggs = [],
        string $searchString = ''
    ): ProductSearchResult {

        $search = $this->createProductSearch();

        $search->getQuery()
               ->setFrom(0)
               ->setSize(25);

        //$this->setPagination($search->getQuery(), $nav);

        $resultSet = $search->search();

        /**
         * TODO Постепенно усложнять код метода, чтобы получить готовый универсальный код
         */

        // $multiSearch = new \Elastica\Multi\Search($this->client);
        //
        // $multiSearch->addSearch($this->createProductSearch())

        return new ProductSearchResult($resultSet);
    }

    /**
     * @return Search
     */
    protected function createProductSearch(): Search
    {
        /*
         * Обязательно надо создавать явно новый объект Query,
         * иначе даже при создании новых объектов Search они
         * будут разделять общий объект Query и выставление
         * size = 0 для дозапросов аггрегаций будет ломать
         * постраничную навигацию каталога.
         */
        return (new Search($this->client))
            ->setQuery(new Query())
            ->addIndex($this->getCatalogIndex())
            ->addType(DocumentType::PRODUCT);
    }

    /**
     * @param string $indexName
     *
     * @return string
     */
    private function getIndexName(string $indexName)
    {
        $prefix = '';
        if (EnvType::isDev()) {
            $prefix = EnvType::DEV . '-';
        }

        return $prefix . $indexName;
    }

    /**
     * @return LoggerInterface
     */
    protected function log()
    {
        return $this->logger;
    }

    /**
     * @param int $brandId
     *
     * @return bool
     */
    public function deleteBrand(int $brandId)
    {
        $overallResult = true;

        $productSearch = $this->createProductSearch();

        $productSearch->getQuery()
                      ->setFrom(0)
                      ->setSize(500)
                      ->setSource(false)
                      ->setSort(['_doc'])
                      ->setParam('query', ['term' => ['brand.ID' => $brandId]]);

        $scroll = $productSearch->scroll();

        foreach ($scroll as $resultSet) {

            $documentsToDelete = [];

            foreach ($resultSet as $result) {

                $documentsToDelete[] = new Document($result->getId());

            }

            $responseSet = $this->getCatalogIndex()->deleteDocuments($documentsToDelete);

            if (!$responseSet->isOk()) {
                $this->log()->error(
                    $responseSet->getError(),
                    [
                        'brandId' => $brandId,
                    ]
                );

                $overallResult = false;
            }

        }

        return $overallResult;
    }

}
