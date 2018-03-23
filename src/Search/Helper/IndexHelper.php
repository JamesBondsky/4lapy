<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Search\Helper;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Query;
use Elastica\Search;
use Exception;
use FourPaws\App\Env;
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
     * @var Index
     */
    protected $catalogIndex;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var Factory
     */
    private $factory;
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
     * @param Client     $client
     * @param Factory    $factory
     *
     * @param Serializer $serializer
     * @param Producer   $catalogSyncProducer
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
        if (null === $this->catalogIndex) {
            $this->catalogIndex = $this->client->getIndex($this->getIndexName('catalog'));
        }

        return $this->catalogIndex;
    }

    /**
     * @param bool $force
     *
     * @throws \Elastica\Exception\InvalidException
     * @throws RuntimeException
     * @return bool
     */
    public function createCatalogIndex(bool $force = false): bool
    {
        $catalogIndex = $this->getCatalogIndex();
        $indexExists = $catalogIndex->exists();
        if ($indexExists && !$force) {
            return false;
        }

        try {
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
        $qwertyRu = mb_split('\s', 'й ц у к е н г ш щ з х ъ ф ы в а п р о л д ж э я ч с м и т ь б ю');
        $qwertyEn = mb_split('\s', 'q w e r t y u i o p [ ] a s d f g h j k l ; \' z x c v b n m , .');
        $characterMap = function ($value1, $value2) {
            return $value1 . ' => ' . $value2;
        };
        $enToRuMapping = array_map($characterMap, $qwertyEn, $qwertyRu);
        $ruToEnMapping = array_map($characterMap, $qwertyRu, $qwertyEn);

        return [
            'settings' => [
                'number_of_shards' => 1,
                'analysis'         =>
                    [
                        'analyzer'    => [
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
                        'filter'      => [
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
                        'char_filter' => [
                            'ru_en' => [
                                'type'     => 'mapping',
                                'mappings' => $ruToEnMapping,
                            ],
                            'en_ru' => [
                                'type'     => 'mapping',
                                'mappings' => $enToRuMapping,
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
                            'type'       => 'nested',
                            'properties' => [
                                'active'                    => ['type' => 'boolean'],
                                'dateActiveFrom'            => ['type' => 'date', 'format' => 'date_optional_time'],
                                'dateActiveTo'              => ['type' => 'date', 'format' => 'date_optional_time'],
                                'ID'                        => ['type' => 'integer'],
                                'CODE'                      => ['type' => 'keyword'],
                                'XML_ID'                    => ['type' => 'keyword'],
                                'SORT'                      => ['type' => 'integer'],
                                'NAME'                      => ['type' => 'text'],
                                'PROPERTY_VOLUME'           => ['type' => 'float'],
                                'PROPERTY_VOLUME_REFERENCE' => ['type' => 'keyword'],
                                'PROPERTY_COLOUR'           => ['type' => 'keyword'],
                                'PROPERTY_CLOTHING_SIZE'    => ['type' => 'keyword'],
                                'PROPERTY_BARCODE'          => ['type' => 'keyword'],
                                'PROPERTY_KIND_OF_PACKING'  => ['type' => 'keyword'],
                                'PROPERTY_REWARD_TYPE'      => ['type' => 'keyword'],
                                'price'                     => ['type' => 'scaled_float', 'scaling_factor' => 100,],
                                'currency'                  => ['type' => 'keyword'],
//                                'prices'                   => [
//                                    'type'       => 'nested',
//                                    'properties' => [
//                                        'REGION_ID' => ['type' => 'keyword'],
//                                        'PRICE'     => ['type' => 'scaled_float', 'scaling_factor' => 100,],
//                                        'CURRENCY'  => ['type' => 'keyword'],
//                                    ],
//                                ],
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
                        'PROPERTY_PET_TYPE'                => ['type' => 'keyword'],
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
                        'PROPERTY_COUNTRY'                 => ['type' => 'keyword'],
                        'PROPERTY_CONSISTENCE'             => ['type' => 'keyword'],
                        'PROPERTY_FEED_SPECIFICATION'      => ['type' => 'keyword'],
                        'PROPERTY_PHARMA_GROUP'            => ['type' => 'keyword'],
                        'hasActions'                       => ['type' => 'boolean'],
                        'deliveryAvailability'             => ['type' => 'keyword'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param Product $product
     *
     * @throws RuntimeException
     * @return bool
     */
    public function indexProduct(Product $product): bool
    {
        return $this->indexProducts([$product]);
    }

    public function indexProducts(array $products): bool
    {
        $products = array_filter($products, function ($data) {
            return $data && $data instanceof Product && !$data->getOffers()->isEmpty();
        });
        $documents = array_map(function (Product $product) {
            return $this->factory->makeProductDocument($product);
        }, $products);

        if (!$products) {
            return true;
        }
        $responseSet = $this->getCatalogIndex()->addDocuments($documents);

        if (!$responseSet->isOk()) {
            $this->log()->error(
                $responseSet->getError(),
                [
                    'products' => array_map(function (Product $product) {
                        return $product->getId();
                    }, $products),
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * **Синхронно** индексирует товары в Elasticsearch
     *
     * @param bool $flushBaseFilter
     *
     * @param int  $batchSize
     */
    public function indexAll(bool $flushBaseFilter = false, int $batchSize = 500)
    {
        $query = (new ProductQuery())
            ->withOrder(['ID' => 'DESC']);

        if ($flushBaseFilter) {
            $query->withFilter([]);
        }

        $allProducts = $query->exec();

        $indexOk = 0;
        $indexError = 0;
        $indexTotal = $allProducts->count();

        $this->log()->info(
            sprintf(
                'Всего товаров: %d. Идёт индексация товаров... Ждите.',
                $indexTotal
            )
        );

        $allProductsChunked = array_chunk($allProducts->toArray(), $batchSize);
        unset($allProducts);
        unset($query);

        $this->log()->debug(sprintf('memory: %s, memory_pick_usage: %s', memory_get_usage(true), memory_get_peak_usage(true)));
        foreach ($allProductsChunked as $i => $allProductsChunk) {
            if ($this->indexProducts($allProductsChunk)) {
                $indexOk += \count($allProductsChunk);
            } else {
                $indexError +=\count($allProductsChunk);
            }
            unset($allProductsChunked[$i]);

            $this->log()->info(sprintf('Индексировано товаров %d...', $indexOk));
            $this->log()->debug(sprintf('memory: %s, memory_pick_usage: %s', memory_get_usage(true), memory_get_peak_usage(true)));
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
     * @throws RuntimeException
     * @return bool
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
     * @throws RuntimeException
     * @return bool
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
     * @throws \Elastica\Exception\InvalidException
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
     * @param bool $flushBaseFilter
     *
     * @throws \RuntimeException
     * @return bool
     */
    public function cleanup(bool $flushBaseFilter = false): bool
    {
        try {
            $totalDocumentsCount = 0;
            $deletedDocumentsCount = 0;

            $productQuery = (new ProductQuery())
                ->withSelect(['ID']);
            if ($flushBaseFilter) {
                $productQuery->withFilter([]);
            }

            $productSearch = $this->createProductSearch();

            $productSearch->getQuery()
                ->setFrom(0)
                ->setSize(500)
                ->setSource(false)
                ->setSort(['_doc']);

            $scroll = $productSearch->scroll();

            //По всем пачкам из Elastic
            foreach ($scroll as $resultSet) {
                if ($totalDocumentsCount === 0) {
                    $totalDocumentsCount = $resultSet->getTotalHits();
                }

                $productFromElasticIdList = [];
                //По всем документам из пачки
                foreach ($resultSet as $result) {
                    $productFromElasticIdList[] = $result->getId();
                }

                if (\count($productFromElasticIdList) <= 0) {
                    continue;
                }

                $productFromDbIdList = [];
                $dbProductList = $productQuery->withFilterParameter('=ID', $productFromElasticIdList)
                    ->doExec();

                while ($fields = $dbProductList->Fetch()) {
                    $productFromDbIdList[] = (int)$fields['ID'];
                }

                $deleteIdList = array_diff($productFromElasticIdList, $productFromDbIdList);

                if (\count($deleteIdList) <= 0) {
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
                    \get_class($exception),
                    $exception->getMessage(),
                    $exception->getCode()
                )
            );

            return false;
        }

        return true;
    }

    /**
     * @param string $indexName
     *
     * @return string
     */
    private function getIndexName(string $indexName): string
    {
        $prefix = '';
        if (!Env::isProd()) {
            $prefix = Env::getServerType() . '-';
        }

        return $prefix . $indexName;
    }
}
