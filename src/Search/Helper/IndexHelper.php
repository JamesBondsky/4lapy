<?php

namespace FourPaws\Search\Helper;

use Adv\Bitrixtools\Tools\EnvType;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Query;
use Elastica\Search;
use Exception;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Search\Enum\DocumentType;
use FourPaws\Search\Factory;
use FourPaws\Search\Model\CatalogSyncMsg;
use JMS\Serializer\Serializer;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

class IndexHelper implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Index
     */
    protected $catalogIndex;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var Producer
     */
    private $catalogSyncProducer;

    /**
     * IndexHelper constructor.
     *
     * @param Client $client
     * @param Factory $factory
     *
     * @throws RuntimeException
     */
    public function __construct(Client $client, Factory $factory, Serializer $serializer, Producer $catalogSyncProducer)
    {
        $this->client = $client;
        $this->factory = $factory;
        $this->serializer = $serializer;
        $this->catalogSyncProducer = $catalogSyncProducer;
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

    /**
     * @return Index
     */
    public function getCatalogIndex(): Index
    {
        if (is_null($this->catalogIndex)) {
            $this->catalogIndex = $this->client->getIndex($this->getIndexName('catalog'));
        }

        return $this->catalogIndex;
    }

    /**
     * @param bool $force
     *
     * @return bool
     * @throws RuntimeException
     */
    public function createCatalogIndex(bool $force = false): bool
    {
        try {

            $catalogIndex = $this->getCatalogIndex();

            $indexExists = $catalogIndex->exists();

            if ($indexExists && !$force) {
                return false;
            }

            if ($indexExists && $force) {
                $catalogIndex->delete();
            }

            $catalogIndex->create($this->getCatalogIndexSettings());

        } catch (ResponseException $exception) {

            $this->log()->error(
                sprintf(
                    'Ошибка создания индекса %s: [%s] %s',
                    $catalogIndex->getName(),
                    $exception->getCode(),
                    $exception->getMessage()
                )
            );

            return false;

        }

        return true;
    }

    /**
     * @return array
     */
    public function getCatalogIndexSettings(): array
    {
        return [
            'settings' => [
                'number_of_shards' => 1,
                'analysis'         =>
                    [
                        'analyzer' => [
                            'default'          => [
                                'type'      => 'custom',
                                'tokenizer' => 'standard',
                                'filter'    => [
                                    'lowercase',
                                ],
                            ],
                            'autocomplete'     => [
                                'type'      => 'custom',
                                'tokenizer' => 'standard',
                                'filter'    => [
                                    'lowercase',
                                    'synonym',
                                    'russian_stop',
                                    'russian_stemmer',
                                    'autocomplete_filter',
                                ],
                            ],
                            'full-text-search' => [
                                'type'      => 'custom',
                                'tokenizer' => 'standard',
                                'filter'    => [
                                    'lowercase',
                                    'synonym',
                                    'russian_stop',
                                    'russian_stemmer',
                                ],
                            ],
                            'sounds-similar'   => [
                                'tokenizer' => 'standard',
                                'filter'    => [
                                    'lowercase',
                                    'synonym',
                                    'transform-to-latin',
                                    'sounds-similar',
                                ],
                            ],
                        ],
                        'filter'   => [
                            'autocomplete_filter' => [
                                'type'     => 'edge_ngram',
                                'min_gram' => 1,
                                'max_gram' => 20,
                            ],
                            'russian_stop'        => [
                                'type'      => 'stop',
                                'stopwords' => '_russian_',
                            ],
                            'russian_stemmer'     => [
                                'type'     => 'stemmer',
                                'language' => 'russian',
                            ],
                            'sounds-similar'      => [
                                'type'    => 'phonetic',
                                'encoder' => 'double_metaphone',
                            ],
                            'transform-to-latin'  => [
                                'type' => 'icu_transform',
                                'id'   => 'Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC',
                            ],
                            'synonym'             => [
                                'type'          => 'synonym',
                                'synonyms_path' => 'resources/synonym.txt',
                            ],
                        ],
                    ],
            ],
            'mappings' => [
                'product' => [
                    '_all'       => ['enabled' => false],
                    'properties' => [
                        'suggest'                          => [
                            'type'            => 'completion',
                            'analyzer'        => 'autocomplete',
                            'search_analyzer' => 'standard',
                        ],
                        'brand'                            => [
                            'properties' => [
                                'active'             => ['type' => 'boolean'],
                                'dateActiveFrom'     => ['type' => 'date', 'format' => 'date_optional_time'],
                                'dateActiveTo'       => ['type' => 'date', 'format' => 'date_optional_time'],
                                'ID'                 => ['type' => 'integer'],
                                'CODE'               => ['type' => 'keyword'],
                                'XML_ID'             => ['type' => 'keyword'],
                                'SORT'               => ['type' => 'integer'],
                                'PREVIEW_TEXT'       => ['type' => 'text'],
                                'PREVIEW_TEXT_TYPE'  => ['type' => 'keyword', 'index' => false],
                                'DETAIL_TEXT'        => ['type' => 'text'],
                                'DETAIL_TEXT_TYPE'   => ['type' => 'keyword', 'index' => false],
                                'DETAIL_PAGE_URL'    => ['type' => 'text', 'index' => false],
                                'CANONICAL_PAGE_URL' => ['type' => 'text', 'index' => false],
                                'NAME'               => ['type' => 'text'],
                                'PROPERTY_POPULAR'   => ['type' => 'boolean'],
                            ],
                        ],
                        'offers'                           => [
                            'properties' => [
                                'active'                   => ['type' => 'boolean'],
                                'dateActiveFrom'           => ['type' => 'date', 'format' => 'date_optional_time'],
                                'dateActiveTo'             => ['type' => 'date', 'format' => 'date_optional_time'],
                                'ID'                       => ['type' => 'integer'],
                                'CODE'                     => ['type' => 'keyword'],
                                'XML_ID'                   => ['type' => 'keyword'],
                                'SORT'                     => ['type' => 'integer'],
                                'NAME'                     => ['type' => 'text'],
                                'PROPERTY_VOLUME'          => ['type' => 'float'],
                                'PROPERTY_BARCODE'         => ['type' => 'keyword'],
                                'PROPERTY_KIND_OF_PACKING' => ['type' => 'keyword'],
                                'PROPERTY_REWARD_TYPE'     => ['type' => 'keyword'],
                                'prices'                   => [
                                    'properties' => [
                                        'REGION_ID' => ['type' => 'keyword'],
                                        'PRICE'     => ['type' => 'scaled_float', 'scaling_factor' => 100,],
                                        'CURRENCY'  => ['type' => 'keyword'],
                                    ],
                                ],
                            ],
                        ],
                        'active'                           => ['type' => 'boolean'],
                        'sectionIdList'                    => ['type' => 'integer'],
                        'ID'                               => ['type' => 'integer'],
                        'CODE'                             => ['type' => 'keyword'],
                        'XML_ID'                           => ['type' => 'keyword'],
                        'SORT'                             => ['type' => 'integer'],
                        'PREVIEW_TEXT'                     => ['type' => 'text'],
                        'PREVIEW_TEXT_TYPE'                => ['type' => 'keyword', 'index' => false],
                        'DETAIL_TEXT'                      => ['type' => 'text'],
                        'DETAIL_TEXT_TYPE'                 => ['type' => 'keyword', 'index' => false],
                        'DETAIL_PAGE_URL'                  => ['type' => 'text', 'index' => false],
                        'CANONICAL_PAGE_URL'               => ['type' => 'text', 'index' => false],
                        'dateActiveFrom'                   => ['type' => 'date', 'format' => 'date_optional_time'],
                        'dateActiveTo'                     => ['type' => 'date', 'format' => 'date_optional_time'],
                        'NAME'                             => ['type' => 'text'],
                        'PROPERTY_BRAND'                   => ['type' => 'integer'],
                        'PROPERTY_FOR_WHO'                 => ['type' => 'keyword'],
                        'PROPERTY_PET_SIZE'                => ['type' => 'keyword'],
                        'PROPERTY_PET_AGE'                 => ['type' => 'keyword'],
                        'PROPERTY_PET_AGE_ADDITIONAL'      => ['type' => 'keyword'],
                        'PROPERTY_PET_GENDER'              => ['type' => 'keyword'],
                        'PROPERTY_CATEGORY'                => ['type' => 'keyword'],
                        'PROPERTY_PURPOSE'                 => ['type' => 'keyword'],
                        'PROPERTY_LABEL'                   => ['type' => 'keyword'],
                        'PROPERTY_STM'                     => ['type' => 'boolean'],
                        'PROPERTY_TRADE_NAME'              => ['type' => 'keyword'],
                        'PROPERTY_MAKER'                   => ['type' => 'keyword'],
                        'PROPERTY_MANAGER_OF_CATEGORY'     => ['type' => 'keyword'],
                        'PROPERTY_MANUFACTURE_MATERIAL'    => ['type' => 'keyword'],
                        'PROPERTY_SEASON_CLOTHES'          => ['type' => 'keyword'],
                        'PROPERTY_WEIGHT_CAPACITY_PACKING' => ['type' => 'text', 'index' => false],
                        'PROPERTY_LICENSE'                 => ['type' => 'boolean'],
                        'PROPERTY_LOW_TEMPERATURE'         => ['type' => 'boolean'],
                        'PROPERTY_FOOD'                    => ['type' => 'boolean'],
                        'PROPERTY_FLAVOUR'                 => ['type' => 'keyword'],
                        'PROPERTY_FEATURES_OF_INGREDIENTS' => ['type' => 'keyword'],
                        'PROPERTY_PRODUCT_FORM'            => ['type' => 'keyword'],
                        'PROPERTY_TYPE_OF_PARASITE'        => ['type' => 'keyword'],
                        'PROPERTY_GROUP'                   => ['type' => 'text', 'index' => false],
                        'PROPERTY_GROUP_NAME'              => ['type' => 'text', 'index' => false],
                        'PROPERTY_PRODUCED_BY_HOLDER'      => ['type' => 'boolean'],
                        'PROPERTY_SPECIFICATIONS'          => [
                            'properties' => [
                                'TEXT' => ['type' => 'text'],
                                'TYPE' => ['type' => 'keyword', 'index' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param Product $product
     *
     * @return bool
     * @throws RuntimeException
     */
    public function indexProduct(Product $product): bool
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

    /**
     * **Синхронно** индексирует все товары в Elasticsearch
     *
     * @throws RuntimeException
     */
    public function indexAll()
    {
        $dbAllProducts = (new ProductQuery())->withFilter([])
                                             ->doExec();
        $indexOk = 0;
        $indexError = 0;
        $indexTotal = $dbAllProducts->SelectedRowsCount();

        $this->log()->info(
            sprintf(
                'Всего товаров: %d. Идёт индексация товаров... Ждите.',
                $indexTotal
            )
        );

        while ($productFields = $dbAllProducts->GetNext()) {
            if ($this->indexProduct(new Product($productFields))) {
                $indexOk++;
            } else {
                $indexError++;
            }
            if ($indexOk % 500 == 0) {
                $this->log()->info(sprintf('Индексировано товаров %d...', $indexOk));
            }
        }

        $this->log()->info(
            sprintf(
                "Товаров: %d;\tиндексировано: %d;\tошибок: %d;",
                $indexTotal,
                $indexOk,
                $indexError
            )
        );
    }

    /**
     * @param int $productId
     *
     * @return bool
     * @throws RuntimeException
     */
    public function deleteProduct(int $productId): bool
    {

        $document = (new Document($productId))->setType(DocumentType::PRODUCT);
        $responseSet = $this->getCatalogIndex()->deleteDocuments([$document]);

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
     * @param int $brandId
     *
     * @return bool
     * @throws RuntimeException
     */
    public function deleteBrand(int $brandId): bool
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

                $documentsToDelete[] = (new Document($result->getId()))->setType(DocumentType::PRODUCT);

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

    /**
     * @param string $indexName
     *
     * @return string
     */
    private function getIndexName(string $indexName): string
    {
        $prefix = '';
        if (EnvType::isDev()) {
            $prefix = EnvType::DEV . '-';
        }

        return $prefix . $indexName;
    }

    /**
     * @return Search
     */
    public function createProductSearch(): Search
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
     * Удаляет из Elasticsearch отсутствующие в БД товары
     *
     * @return bool
     * @throws RuntimeException
     */
    public function cleanup(): bool
    {
        try {
            $totalDocumentsCount = 0;
            $deletedDocumentsCount = 0;

            $productQuery = (new ProductQuery())->withFilter([])
                                                ->withSelect(['ID']);

            $productSearch = $this->createProductSearch();

            $productSearch->getQuery()
                          ->setFrom(0)
                          ->setSize(500)
                          ->setSource(false)
                          ->setSort(['_doc']);

            $scroll = $productSearch->scroll();

            //По всем пачкам из Elastic
            foreach ($scroll as $resultSet) {

                if ($totalDocumentsCount == 0) {
                    $totalDocumentsCount = $resultSet->getTotalHits();
                }

                $productFromElasticIdList = [];
                //По всем документам из пачки
                foreach ($resultSet as $result) {
                    $productFromElasticIdList[] = $result->getId();
                }

                if (count($productFromElasticIdList) <= 0) {
                    continue;
                }

                $productFromDbIdList = [];
                $dbProductList = $productQuery->withFilterParameter('=ID', $productFromElasticIdList)
                                              ->doExec();

                while ($fields = $dbProductList->Fetch()) {
                    $productFromDbIdList[] = (int)$fields['ID'];
                }

                $deleteIdList = array_diff($productFromElasticIdList, $productFromDbIdList);

                if (count($deleteIdList) <= 0) {
                    continue;
                }

                $deleteIdIndex = array_flip($deleteIdList);

                $deleteDocumentList = [];

                foreach ($resultSet as $result) {
                    if (!isset($deleteIdIndex[$result->getId()])) {
                        continue;
                    }

                    $deleteDocumentList[] = $result->getDocument();
                }

                $deleteDocumentsResponseSet = $this->getCatalogIndex()->deleteDocuments($deleteDocumentList);

                if ($deleteDocumentsResponseSet->isOk()) {
                    $deletedDocumentsCount += $deleteDocumentsResponseSet->count();
                }

            }

            $this->log()->info('Cleanup done.');
            $this->log()->info('Check documents: ' . $totalDocumentsCount);
            $this->log()->info('Removed documents: ' . $deletedDocumentsCount);

        } catch (Exception $exception) {

            $this->log()->error(
                sprintf(
                    '[%s] %s (%s)',
                    get_class($exception),
                    $exception->getMessage(),
                    $exception->getCode()
                )
            );

            return false;
        }

        return true;
    }

}
