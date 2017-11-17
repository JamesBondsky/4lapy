<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Elastica\Exception\ResponseException;
use FourPaws\App\Application;

class Catalog_Elasitc20171103183357 extends SprintMigrationBase
{

    /**
     * @var \FourPaws\Search\SearchService
     */
    protected $searchService;

    public function __construct()
    {
        parent::__construct();
        /** @noinspection SqlNoDataSourceInspection */
        $this->description = "Create index in Elasticsearch for catalog";

        $this->searchService = Application::getInstance()->getContainer()->get('search.service');

    }

    public function up()
    {
        $catalogIndex = $this->searchService->getCatalogIndex();

        try {

            //Удаляем индекс для дополнительной идемпотентности
            $catalogIndex->delete();

        } catch (ResponseException $exception) {

            $this->log()->warning(
                sprintf(
                    'Error deleting index: %s',
                    $exception->getMessage()
                )
            );

        }

        $catalogIndex->create(
            [
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
                                    'XML_ID'                   => ['type' => 'keyword'],
                                    'SORT'                     => ['type' => 'integer'],
                                    'NAME'                     => ['type' => 'text'],
                                    'PROPERTY_VOLUME'          => ['type' => 'float'],
                                    'PROPERTY_BARCODE'         => ['type' => 'keyword'],
                                    'PROPERTY_KIND_OF_PACKING' => ['type' => 'keyword'],
                                    'PROPERTY_REWARD_TYPE'     => ['type' => 'keyword'],
                                ],
                            ],
                            'active'                           => ['type' => 'boolean'],
                            'ID'                               => ['type' => 'integer'],
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
            ]
        );

    }

    public function down()
    {

    }

}
