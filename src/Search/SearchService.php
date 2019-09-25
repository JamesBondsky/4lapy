<?php

namespace FourPaws\Search;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Type\Collection;
use Elastica\Exception\InvalidException;
use Elastica\Query;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Ids;
use Elastica\QueryBuilder;
use Elastica\Search;
use Elastica\Suggest;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Filter\DeliveryAvailabilityFilter;
use FourPaws\Catalog\Model\Filter\FilterInterface;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Model\Sorting;
use FourPaws\Catalog\Model\Variant;
use FourPaws\CatalogBundle\Service\SortService;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Handler\DeliveryHandlerBase;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Search\Enum\DocumentType;
use FourPaws\Search\Helper\AggsHelper;
use FourPaws\Search\Helper\IndexHelper;
use FourPaws\Search\Model\CombinedSearchResult;
use FourPaws\Search\Model\Navigation;
use FourPaws\Search\Model\ProductSearchResult;
use FourPaws\Search\Model\ProductSuggestResult;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StockService;
use FourPaws\StoreBundle\Service\StoreService;
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

/**
 * Class SearchService
 *
 * @package FourPaws\Search
 */
class SearchService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const BRAND_EXACT_MATCH_QUERY_NAME = 'name-fuzzy-word-brand-0';

    /**
     * @var IndexHelper
     */
    private $indexHelper;
    /**
     * @var AggsHelper
     */
    private $aggsHelper;
    /**
     * @var SortService
     */
    private $sortService;
    /**
     * @var string
     */
    private $searchUrl;

    /**
     * SearchService constructor.
     *
     * @param IndexHelper $indexHelper
     * @param SortService $sortService
     */
    public function __construct(IndexHelper $indexHelper, SortService $sortService)
    {
        $this->indexHelper = $indexHelper;
        $this->sortService = $sortService;
    }

    /**
     * @param FilterCollection $filters
     *
     * @return Product
     *
     * @throws InvalidArgumentException
     * @throws ApplicationCreateException
     * @throws InvalidException
     * @throws ArgumentException
     * @throws RuntimeException
     */
    public function searchOneWithMinPrice(FilterCollection $filters): Product
    {
        return $this->searchProducts(
            $filters,
            $this->sortService->getPriceSort(),
            (new Navigation())->withPage(1)->withPageSize(1)
        )->getProductCollection()->first();
    }

    /**
     * Возвращает результат поиска товаров, а также обновляет состояние коллекции фильтров так, что учитываются
     * аггрегации: и фильтры соответствующим образом "схлопываются", обеспечивая настоящий фасетный поиск по каталогу.
     *
     * @param FilterCollection $filters
     * @param Sorting $sorting
     * @param Navigation $navigation
     * @param string $searchString
     *
     * @return ProductSearchResult
     * @throws InvalidException
     * @throws ApplicationCreateException
     * @throws InvalidArgumentException
     * @throws ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public function searchProducts(
        FilterCollection $filters,
        Sorting $sorting,
        Navigation $navigation,
        $queryTerm = ''
    ): ProductSearchResult
    {
        $search = $this->getIndexHelper()->createProductSearch();

        // модификация для поиска сразу по id товаров
        if(is_array($queryTerm)){
            $queryRule = $this->getIdsQueryRule($filters, $queryTerm);
        } else {
            $queryRule = $this->getFullQueryRule($filters, $queryTerm);
            if ($queryTerm !== '') {
                $search->getQuery()->setMinScore(0.9);
            }
        }

        //$queryBuilder = new QueryBuilder();
        //$termsQuery = $queryBuilder->query()->multi_match();
        //$queryRule->addParam('filter', $termsQuery);

        $search->getQuery()
            ->setFrom($navigation->getFrom())
            ->setSize($navigation->getSize())
            ->setSort($sorting->getRule())
            ->setParam('query', $queryRule)
            ->setHighlight(['fields' => ['offers.XML_ID' => (object)[]]]);

        $this->getAggsHelper()->setAggs($search->getQuery(), $filters);

        $resultSet = $search->search();

        // если задана строка поиска и не найдено совпадений, то пробуем в другой раскладке
        if ($queryTerm && !is_array($queryTerm) && !$resultSet->getTotalHits()) {
            $from = preg_match('/[ЁёА-я]/u', $queryTerm) ? 'ru' : 'en';
            $to = $from === 'ru' ? 'en' : 'ru';
            $queryTerm = \CSearchLanguage::ConvertKeyboardLayout($queryTerm, $from, $to);
            $search->getQuery()->setParam('query', $this->getFullQueryRule($filters, $queryTerm));
            $newResultSet = $search->search();
            if ($newResultSet->getTotalHits()) {
                $resultSet = $newResultSet;
            }
        }

        if ($resultSet->getTotalHits() && ($resultSet->getTotalHits() < $navigation->getFrom())) {
            $navigation->withPage(1);
            $search->getQuery()->setFrom($navigation->getFrom());
            $resultSet = $search->search();
        }

        if (!$filters->isEmpty()) {
            $this->getAggsHelper()->collapseFilters($filters, $resultSet);
        }

        $productSearchResult = new ProductSearchResult($resultSet, $navigation, $queryTerm);

        // фильтр доступности
        if (!$filters->isEmpty()) {
            $filterAvaliable = $filters->getDeliveryAvailabilityFilter();
            if($filterAvaliable && $filterAvaliable->hasCheckedVariants()){
                $productSearchResult = $this->filterByDeliveryAvailability($productSearchResult, $filterAvaliable);
            }
        }

        return $productSearchResult;
    }

    /**
     * @param FilterCollection $filters
     * @param Sorting $sorting
     * @param Navigation $navigation
     * @param string $searchString
     * @return CombinedSearchResult
     * @throws ApplicationCreateException
     * @throws ArgumentException
     */
    public function searchAll(
        FilterCollection $filters,
        Sorting $sorting,
        Navigation $navigation,
        string $searchString = ''
    )
    {
        $multiSearch = $this->getIndexHelper()->createAllTypesSearch();

        $productSearch = $this->getIndexHelper()->createProductSearch();
        $brandSearch = $this->getIndexHelper()->createBrandSearch();
        $suggestSearch = $this->getIndexHelper()->createSuggestSearch();

        if ($searchString !== '') {
            $productSearch->getQuery()->setMinScore(100);
            $brandSearch->getQuery()->setMinScore(100);
            $suggestSearch->getQuery()->setMinScore(1000);
        }

        $cntResults = (count(explode(' ', $searchString)) >= 4) ? 10 : 10;

        $productSearch->getQuery()
            ->setFrom($navigation->getFrom())
            ->setSize(100)
            ->setSort($sorting->getRule())
            ->setParam('query', $this->getFullQueryRule($filters, $searchString))
            ->setHighlight(['fields' => ['offers.XML_ID' => (object)[]]]);

        $brandSearch->getQuery()
            ->setFrom($navigation->getFrom())
            ->setSize($cntResults)
            ->setSort($sorting->getRule())
            ->setParam('query', $this->getBrandFullQueryRule($searchString));

        $suggestSearch->getQuery()
            ->setFrom($navigation->getFrom())
            ->setSize(500)
            ->setSort($sorting->getRule())
            ->setParam('query', $this->getSuggestionsMax($searchString));


        $suggestSearch->addType(DocumentType::PRODUCT);

        $this->getAggsHelper()->setAggs($productSearch->getQuery(), $filters);

        $multiSearch->setSearches([
            'brands' => $brandSearch,
            'products' => $productSearch,
            'suggests' => $suggestSearch
        ]);

        $resultSet = $multiSearch->search();

        return new CombinedSearchResult($resultSet, $navigation);
    }



    /**
     * Автокомплит для товаров
     *
     * @param Navigation $navigation
     * @param string     $searchString
     *
     * @return ProductSuggestResult
     * @throws ApplicationCreateException
     * @throws InvalidException
     */
    public function productsAutocomplete(Navigation $navigation, string $searchString): ProductSuggestResult
    {
        $suggest = new Suggest();

        $completion = new Suggest\Completion('product_suggest', 'suggest');
        $completion->setText($searchString);
        $completion->setParam('fuzzy', ['fuzziness' => 'auto']);
        $completion->setParam('size', $navigation->getSize());
        $suggest->addSuggestion($completion);

        $index = $this->getIndexHelper()->getCatalogIndex();
        $query = Query::create($suggest);
        $query->setMinScore(0.9);
        $result = $index->search($query);

        return new ProductSuggestResult($result);
    }

    /**
     * Возвращает массив с условиями фильтрации для категории с её выбранными фильтрами на языке Elasticsearch
     *
     * @param FilterCollection $filterCollection
     *
     * @return AbstractQuery[]
     */
    public function getFilterRule(FilterCollection $filterCollection): array
    {
        $filterSet = [];

        /** @var FilterInterface $filter */
        foreach ($filterCollection as $filter) {
            if ($filter->hasCheckedVariants()) {
                $filterSet[] = $filter->getFilterRule();
            }
        }

        return $filterSet;
    }

    /**
     * @param string $searchString
     * @return BoolQuery
     */
    public function getBrandQueryRule(string $searchString): BoolQuery
    {
        $queryBuilder = new QueryBuilder();
        $boolQuery = $queryBuilder->query()->bool();
        if ($searchString === '') {
            return $boolQuery;
        }

        //0 ошибок
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['NAME', 'PROPERTY_TRANSLITS'])
                ->setType('best_fields')
                ->setFuzziness(0)
                ->setAnalyzer('default')
                ->setParam('boost', 100.0)
                ->setParam('_name', self::BRAND_EXACT_MATCH_QUERY_NAME)
                ->setOperator('and')
        );

        //1 ошибка
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['NAME', 'PROPERTY_TRANSLITS'])
                ->setType('best_fields')
                ->setFuzziness(1)
                ->setAnalyzer('default')
                ->setParam('boost', 45.0)
                ->setParam('_name', 'name-fuzzy-word-brand-1')
                ->setOperator('and')
        );

        //0 ошибок
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['NAME', 'PROPERTY_TRANSLITS'])
                ->setType('best_fields')
                ->setFuzziness(0)
                ->setAnalyzer('full-text-brand-hard-search')
                ->setParam('boost', 10.0)
                ->setParam('_name', 'name-fuzzy-word-brand-stemmer')
                ->setOperator('or')
        );

        return $boolQuery;
    }

    /**
     * @param string $searchString
     *
     * @return BoolQuery
     * @throws InvalidException
     */
    public function getProductQueryRule(string $searchString): BoolQuery
    {
        $queryBuilder = new QueryBuilder();
        $boolQuery = $queryBuilder->query()->bool();

        if ($searchString === '') {
            return $boolQuery;
        }

        /*
         * 0 Артикул и штрихкод
         */

        //Точное по артикулу
        $boolQuery->addShould(
            $queryBuilder->query()->nested()
                ->setPath('offers')
                ->setQuery(
                    $queryBuilder->query()->term(
                        [
                            'offers.XML_ID' => [
                                'value' => $searchString,
                                'boost' => 200.0,
                                '_name' => 'skuId',
                            ],
                        ]
                    )
                )
        );

        //Точное по штрихкоду
        $boolQuery->addShould(
            $queryBuilder->query()->nested()
                ->setPath('offers')
                ->setQuery(
                    $queryBuilder->query()->term(
                        [
                            'offers.PROPERTY_BARCODE' => [
                                'value' => $searchString,
                                'boost' => 200.0,
                                '_name' => 'barcode',
                            ],
                        ]
                    )
                )
        );

        //спец. поле для буста
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['searchBooster'])
                ->setType('best_fields')
                ->setFuzziness(0)
                ->setAnalyzer('detail-text-analyzator')
                ->setParam('boost', 700.0)
                ->setParam('_name', 'name-fuzzy-word-searchBooster')
                ->setOperator('and')
        );

        //спец. поле для буста 1 ошибка
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['searchBooster'])
                ->setType('best_fields')
                ->setFuzziness(1)
                ->setAnalyzer('detail-text-analyzator')
                ->setParam('boost', 350.0)
                ->setParam('_name', 'name-fuzzy-word-searchBooster')
                ->setOperator('and')
        );

        //бренды 0 ошибок
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['brand.NAME', 'brand.PROPERTY_TRANSLITS'])
                ->setType('best_fields')
                ->setFuzziness(0)
                ->setAnalyzer('default')
                ->setParam('boost', 100.0)
                ->setParam('_name', 'name-fuzzy-word-brand-0')
                ->setOperator('and')
        );

        //бренды 1 ошибка
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['brand.NAME', 'brand.PROPERTY_TRANSLITS'])
                ->setType('best_fields')
                ->setFuzziness(1)
                ->setAnalyzer('default')
                ->setParam('boost', 45.0)
                ->setParam('_name', 'name-fuzzy-word-brand-1')
                ->setOperator('and')
        );

        //////////////////////////
        /// Разделы и названия ///
        //////////////////////////
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['sectionName'])
                ->setType('best_fields')
                ->setFuzziness(0)
                ->setAnalyzer('default')
                ->setParam('boost', 12.0)
                ->setParam('_name', 'name-fuzzy-word-section-0')
                ->setOperator('and')
        );

        //1 ошибка
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['sectionName'])
                ->setType('best_fields')
                ->setFuzziness(1)
                ->setAnalyzer('default')
                ->setParam('boost', 6.0)
                ->setParam('_name', 'name-fuzzy-word-section-1')
                ->setOperator('and')
        );

        //2 ошибка
//        $boolQuery->addShould(
//            $queryBuilder->query()->multi_match()
//                ->setQuery($searchString)
//                ->setFields(['sectionName'])
//                ->setType('best_fields')
//                ->setFuzziness(2)
//                ->setAnalyzer('default')
//                ->setParam('boost', 3.0)
//                ->setParam('_name', 'name-fuzzy-word-section-2')
//                ->setOperator('and')
//        );

        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['NAME.synonym'])
                ->setType('best_fields')
                ->setFuzziness(0)
//                ->setAnalyzer('default')
                ->setParam('boost', 80)
                ->setParam('_name', 'name-fuzzy-word-name-0')
                ->setOperator('and')
        );


        //1 ошибка
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['NAME.synonym'])
                ->setType('best_fields')
                ->setFuzziness(1)
//                ->setAnalyzer('default')
                ->setParam('boost', 60)
                ->setParam('_name', 'name-fuzzy-word-name-1')
                ->setOperator('and')
        );

        //2 ошибка
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['NAME.synonym'])
                ->setType('best_fields')
                ->setFuzziness(2)
//                ->setAnalyzer('default')
                ->setParam('boost', 30)
                ->setParam('_name', 'name-fuzzy-word-name-2')
                ->setOperator('and')
        );

        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['PREVIEW_TEXT', 'DETAIL_TEXT'])
                ->setType('best_fields')
                ->setFuzziness(0)
//                ->setAnalyzer('default')
                ->setParam('boost', 35)
                ->setParam('_name', 'name-fuzzy-word-name-0')
                ->setOperator('and')
        );


        //1 ошибка
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['PREVIEW_TEXT', 'DETAIL_TEXT'])
                ->setType('best_fields')
                ->setFuzziness(1)
//                ->setAnalyzer('default')
                ->setParam('boost', 10)
                ->setParam('_name', 'name-fuzzy-word-name-1')
                ->setOperator('and')
        );

        //2 ошибка
//        $boolQuery->addShould(
//            $queryBuilder->query()->multi_match()
//                ->setQuery($searchString)
//                ->setFields(['PREVIEW_TEXT', 'DETAIL_TEXT'])
//                ->setType('best_fields')
//                ->setFuzziness(2)
//                ->setAnalyzer('default')
//                ->setParam('boost', 5)
//                ->setParam('_name', 'name-fuzzy-word-name-2')
//                ->setOperator('and')
//        );

        return $boolQuery;
    }

    /**
     * @param string $searchString
     *
     * @return BoolQuery
     * @throws InvalidException
     */
    public function getSuggestRules(string $searchString): BoolQuery
    {
        $queryBuilder = new QueryBuilder();
        $boolQuery = $queryBuilder->query()->bool();

        if ($searchString === '') {
            return $boolQuery;
        }

        //спец. поле для буста
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['searchBooster'])
                ->setType('best_fields')
                ->setFuzziness(0)
                ->setAnalyzer('detail-text-analyzator')
                ->setParam('boost', 700.0)
                ->setParam('_name', 'name-fuzzy-word-searchBooster')
                ->setOperator('and')
        );

        //спец. поле для буста 1 ошибка
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['searchBooster'])
                ->setType('best_fields')
                ->setFuzziness(1)
                ->setAnalyzer('detail-text-analyzator')
                ->setParam('boost', 350.0)
                ->setParam('_name', 'name-fuzzy-word-searchBooster')
                ->setOperator('and')
        );

        //бренды 0 ошибок
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['brand.NAME', 'brand.PROPERTY_TRANSLITS'])
                ->setType('best_fields')
                ->setFuzziness(0)
                ->setAnalyzer('default')
                ->setParam('boost', 25.0)
                ->setParam('_name', 'name-fuzzy-word-brand-0')
                ->setOperator('and')
        );

        //бренды 1 ошибка
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['brand.NAME', 'brand.PROPERTY_TRANSLITS'])
                ->setType('best_fields')
                ->setFuzziness(1)
                ->setAnalyzer('default')
                ->setParam('boost', 12.0)
                ->setParam('_name', 'name-fuzzy-word-brand-1')
                ->setOperator('and')
        );


        //////////////////////////
        /// Разделы и названия ///
        //////////////////////////
//        $boolQuery->addShould(
//            $queryBuilder->query()->multi_match()
//                ->setQuery($searchString)
//                ->setFields(['sectionName'])
//                ->setType('best_fields')
//                ->setFuzziness(0)
//                ->setAnalyzer('full-text-brand-hard-search')
//                ->setParam('boost', 100.0)
//                ->setParam('_name', 'name-fuzzy-word-section-0')
//                ->setOperator('and')
//        );
//
//        //1 ошибка
//        $boolQuery->addShould(
//            $queryBuilder->query()->multi_match()
//                ->setQuery($searchString)
//                ->setFields(['sectionName'])
//                ->setType('best_fields')
//                ->setFuzziness(1)
//                ->setAnalyzer('full-text-brand-hard-search')
//                ->setParam('boost', 50)
//                ->setParam('_name', 'name-fuzzy-word-section-1')
//                ->setOperator('and')
//        );

        //2 ошибка
//        $boolQuery->addShould(
//            $queryBuilder->query()->multi_match()
//                ->setQuery($searchString)
//                ->setFields(['sectionName'])
//                ->setType('best_fields')
//                ->setFuzziness(2)
//                ->setAnalyzer('default')
//                ->setParam('boost', 25)
//                ->setParam('_name', 'name-fuzzy-word-section-2')
//                ->setOperator('and')
//        );

        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['NAME.synonym'])
                ->setType('best_fields')
                ->setFuzziness(0)
//                ->setAnalyzer('detail-text-analyzator')
                ->setParam('boost', 100.0)
                ->setParam('_name', 'name-fuzzy-word-name-0')
                ->setOperator('and')
        );


        //1 ошибка
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields(['NAME.synonym'])
                ->setType('best_fields')
                ->setFuzziness(1)
//                ->setAnalyzer('detail-text-analyzator')
                ->setParam('boost', 50)
                ->setParam('_name', 'name-fuzzy-word-name-1')
                ->setOperator('and')
        );

        //2 ошибка
//        $boolQuery->addShould(
//            $queryBuilder->query()->multi_match()
//                ->setQuery($searchString)
//                ->setFields(['NAME.synonym'])
//                ->setType('best_fields')
//                ->setFuzziness(2)
////                ->setAnalyzer('detail-text-analyzator')
//                ->setParam('boost', 15)
//                ->setParam('_name', 'name-fuzzy-word-name-2')
//                ->setOperator('and')
//        );

        return $boolQuery;
    }

    /**
     * @param FilterCollection $filters
     * @param string           $searchString
     *
     * @return AbstractQuery
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws InvalidException
     */
    public function getFullQueryRule(FilterCollection $filters, string $searchString = ''): AbstractQuery
    {
        $searchQuery = new Query\FunctionScore();

        $boolQuery = $this->getProductQueryRule($searchString);
        $searchQuery->setQuery($boolQuery);

        /** @var AbstractQuery[] $filterSet */
        $filterSet = $this->getFilterRule($filters);
        foreach ($filterSet as $filterQuery) {
            $boolQuery->addParam('filter', $filterQuery);
        }

        $this->addWeightFunctions($searchQuery);
        if ('' === $searchString) {
            $searchQuery->setBoostMode('sum');
        }

        return $searchQuery;
    }

    /**
     * @param FilterCollection $filters
     * @param string           $searchString
     *
     * @return AbstractQuery
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws InvalidException
     */
    public function getIdsQueryRule(FilterCollection $filters, array $ids): AbstractQuery
    {
        $searchQuery = new Query\FunctionScore();

        $queryBuilder = new QueryBuilder();
        $boolQuery = $queryBuilder->query()->bool();
        $boolQuery->addMust(
            $queryBuilder->query()->nested()
                ->setPath('offers')
                ->setQuery(
                    $queryBuilder->query()->terms('offers.XML_ID', $ids)
                )
        );
        $searchQuery->setQuery($boolQuery);

        /** @var AbstractQuery[] $filterSet */
        $filterSet = $this->getFilterRule($filters);
        foreach ($filterSet as $filterQuery) {
            $boolQuery->addParam('filter', $filterQuery);
        }

        $this->addWeightFunctions($searchQuery);

        return $searchQuery;
    }

    /**
     * @param string $searchString
     *
     * @return AbstractQuery
     * @throws InvalidException
     */
    public function getSuggestionsMax(string $searchString = ''): AbstractQuery
    {
        $searchQuery = new Query\FunctionScore();

        $boolQuery = $this->getSuggestRules($searchString);
        $searchQuery->setQuery($boolQuery);

        $this->addWeightSuggestions($searchQuery);
        if ('' === $searchString) {
            $searchQuery->setBoostMode('sum');
        }

        return $searchQuery;
    }

    /**
     * @param string $searchString
     * @return AbstractQuery
     */
    public function getBrandFullQueryRule(string $searchString = ''): AbstractQuery
    {
        $searchQuery = new Query\FunctionScore();

        $boolQuery = $this->getBrandQueryRule($searchString);
        $searchQuery->setQuery($boolQuery);

        if ('' === $searchString) {
            $searchQuery->setBoostMode('sum');
        }

        return $searchQuery;
    }

    /**
     * @return IndexHelper
     */
    public function getIndexHelper(): IndexHelper
    {
        return $this->indexHelper;
    }

    /**
     * @return AggsHelper
     */
    public function getAggsHelper(): AggsHelper
    {
        if (null === $this->aggsHelper) {
            $this->aggsHelper = new AggsHelper();
        }

        return $this->aggsHelper;
    }

    /**
     * @param Query\FunctionScore $query
     *
     * @throws InvalidException
     */
    protected function addWeightFunctions(Query\FunctionScore $query): void
    {
        $queryBuilder = new QueryBuilder();
        $query
            // товары, имеющие остатки и картинки
            ->addWeightFunction(
                10,
                $queryBuilder
                    ->query()
                    ->bool()
                    ->addMust(
                        $queryBuilder
                            ->query()
                            ->match('hasImages', true)
                    )
                    ->addMust(
                        $queryBuilder
                            ->query()
                            ->match('hasStocks', true)
                    )
            )
            // собственная торговая марка +5
            ->addWeightFunction(
                5,
                $queryBuilder
                    ->query()
                    ->match()
                    ->setField('PROPERTY_STM', true)
            )
            // популярные товары +5
            ->addWeightFunction(
                5,
                $queryBuilder
                    ->query()
                    ->nested()
                    ->setPath('offers')
                    ->setQuery(
                        $queryBuilder
                            ->query()
                            ->match()
                            ->setField('offers.PROPERTY_IS_POPULAR', true)
                    )
            )
            // товар, имеющий акции +5
            ->addWeightFunction(
                5,
                $queryBuilder
                    ->query()
                    ->match()
                    ->setField('hasActions', true)
            )
            // новинки +5
            ->addWeightFunction(
                5,
                $queryBuilder
                    ->query()
                    ->nested()
                    ->setPath('offers')
                    ->setQuery(
                        $queryBuilder
                            ->query()
                            ->match()
                            ->setField('offers.PROPERTY_IS_NEW', true)
                    )
            )
            // товары с шильдиками +5
            ->addWeightFunction(
                5,
                $queryBuilder
                    ->query()
                    ->nested()
                    ->setPath('offers')
                    ->setQuery(
                        $queryBuilder
                            ->query()
                            ->multi_match()
                            ->setFields([
                                'offers.PROPERTY_IS_POPULAR',
                                'offers.PROPERTY_IS_HIT',
                                'offers.PROPERTY_IS_NEW',
                                'offers.PROPERTY_IS_SALE',
                            ])
                            ->setQuery(true)
                    )
            )
            ->setScoreMode('sum');
    }

    /**
     * @param Query\FunctionScore $query
     *
     * @throws InvalidException
     */
    protected function addWeightSuggestions(Query\FunctionScore $query): void
    {
        $queryBuilder = new QueryBuilder();
        $query
            // товары, имеющие остатки и картинки
            ->addWeightFunction(
                10,
                $queryBuilder
                    ->query()
                    ->bool()
                    ->addMust(
                        $queryBuilder
                            ->query()
                            ->match('hasImages', true)
                    )
                    ->addMust(
                        $queryBuilder
                            ->query()
                            ->match('hasStocks', true)
                    )
            )
            // собственная торговая марка +5
            ->addWeightFunction(
                5,
                $queryBuilder
                    ->query()
                    ->match()
                    ->setField('PROPERTY_STM', true)
            )
            // популярные товары +5
            ->addWeightFunction(
                5,
                $queryBuilder
                    ->query()
                    ->nested()
                    ->setPath('offers')
                    ->setQuery(
                        $queryBuilder
                            ->query()
                            ->match()
                            ->setField('offers.PROPERTY_IS_POPULAR', true)
                    )
            )
            // товар, имеющий акции +5
            ->addWeightFunction(
                5,
                $queryBuilder
                    ->query()
                    ->match()
                    ->setField('hasActions', true)
            )
            // новинки +50
            ->addWeightFunction(
                5,
                $queryBuilder
                    ->query()
                    ->nested()
                    ->setPath('offers')
                    ->setQuery(
                        $queryBuilder
                            ->query()
                            ->match()
                            ->setField('offers.PROPERTY_IS_NEW', true)
                    )
            )
            // товары с шильдиками +5
            ->addWeightFunction(
                5,
                $queryBuilder
                    ->query()
                    ->nested()
                    ->setPath('offers')
                    ->setQuery(
                        $queryBuilder
                            ->query()
                            ->multi_match()
                            ->setFields([
                                'offers.PROPERTY_IS_POPULAR',
                                'offers.PROPERTY_IS_HIT',
                                'offers.PROPERTY_IS_NEW',
                                'offers.PROPERTY_IS_SALE',
                            ])
                            ->setQuery(true)
                    )
            )
            ->setScoreMode('sum');
    }

    /**
     * @param string $query
     *
     * @return array
     */
    public function getHLSearchSuggestions(string $query): array
    {
        if (!$query)
        {
            return [];
        }

        $container = App::getInstance()->getContainer();
        /** @var DataManager $searchSuggestionsManager */
        $searchSuggestionsManager = $container->get('bx.hlblock.searchsuggestions');

        $searchSuggestions = $searchSuggestionsManager::query()
            ->setSelect([
                'UF_SUGGESTION',
            ])
            ->setFilter([
                'UF_SUGGESTION' => $query . '%',
            ])
            ->setLimit(5)
            ->setOrder([
                'UF_RATE' => 'DESC',
            ])
            ->exec()
            ->fetchAll();

        return $searchSuggestions;
    }

    /**
     * @return string
     */
    public function getSearchUrl(): string
    {
        if ($this->searchUrl) {
            return $this->searchUrl;
        }

        /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router */
        $router = App::getInstance()->getContainer()->get('router');
        /** @var Symfony\Component\Routing\RouteCollection $routes */
        $routes = $router->getRouteCollection();
        $route = $routes->get('fourpaws_catalog_catalog_search');
        $this->searchUrl = $route->getPath();

        return $this->searchUrl;
    }

    /**
     * Фильтрует товары, удаляя из них конкретные офферы, не подходящие по фильтру
     *
     * @param ProductSearchResult $productSearchResult
     * @param DeliveryAvailabilityFilter $filterAvaliable
     * @return ProductSearchResult
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function filterByDeliveryAvailability(ProductSearchResult $productSearchResult, DeliveryAvailabilityFilter $filterAvaliable) : ProductSearchResult
    {
        /** @var DeliveryService $deliveryService */
        $deliveryService = App::getInstance()->getContainer()->get('delivery.service');
        /** @var StockService $stockService */
        $stockService = App::getInstance()->getContainer()->get(StockService::class);

        $variants = $filterAvaliable->getCheckedVariants();
        $availabilityFlags = [];
        /** @var Variant $variant */
        foreach ($variants as $variant){
            $availabilityFlags[] = substr($variant->getValue(), -1);
        }

//                $delivery = null;
//                $pickup = null;
//                $deliveryZone = $deliveryService->getCurrentDeliveryZone();
//
//                $quantities = [];
//                $offers = [];
//                foreach ($productSearchResult->getProductCollection() as $key => $product) {
//                    $offers = $product->getOffers();
//                    foreach ($offers as $offer){
//                        $offers[] = $offer;
//                        $quantities[$offer->getXmlId()] = 1;
//                    }
//                }
//
//                $deliveries = $deliveryService->getByOfferCollection($offers, $quantities);

        $offerIds = [];
        foreach ($productSearchResult->getProductCollection() as $key => $product) {
            $offers = $product->getOffers();
            foreach ($offers as $offer){
                $offerIds[] = $offer->getId();
            }
        }

        $stores = [
            'delivery' => new StoreCollection(),
            'pickup'   => new StoreCollection(),
            'request'  => new StoreCollection(),
        ];
        $storeIdsAll = [];
        $delivery = null;
        $pickup = null;
        $deliveryZone = $deliveryService->getCurrentDeliveryZone();
        $deliveries = $deliveryService->getByLocation();

        /** @var CalculationResultInterface $calculationResult */
        foreach($deliveries as $calculationResult){
            if(!$delivery && $deliveryService->isDelivery($calculationResult)){
                $delivery = $calculationResult;
            }
            if(!$pickup && $deliveryService->isPickup($calculationResult)){
                $pickup = $calculationResult;
            }
        }

        if(in_array('d', $availabilityFlags) && $delivery){
            $storesAvailable = DeliveryHandlerBase::getAvailableStores($delivery->getDeliveryCode(), $deliveryZone);
            /** @var Store $store */
            foreach ($storesAvailable as $store){
                $stores['delivery']->add($store);
                $storeIdsAll[] = $store->getId();
            }
        }
        if(in_array('p', $availabilityFlags) && $pickup){
            $storesAvailable = DeliveryHandlerBase::getAvailableStores($pickup->getDeliveryCode(), $deliveryZone);
            /** @var Store $stock */
            foreach ($storesAvailable as $store){
                $stores['pickup']->add($store);
                $storeIdsAll[] = $store->getId();
            }
        }
        if(in_array('r', $availabilityFlags)){
            /** @var StoreService $storeService */
            $storeService = App::getInstance()->getContainer()->get('store.service');
            $storesAvailable = $storeService->getStores(StoreService::TYPE_SUPPLIER);

            /** @var Store $stock */
            foreach ($storesAvailable as $store){
                $stores['request']->add($store);
                $storeIdsAll[] = $store->getId();
            }
        }

        $stockCollection = $stockService->getStocksByOfferIds($offerIds, $storeIdsAll);

        /** @var Product $product */
        foreach ($productSearchResult->getProductCollection() as $key => $product){
            $offers = $product->getOffers();
            foreach ($offers as $offer){
                $remove = true;

                if(in_array('d', $availabilityFlags)
                    && !$offer->getProduct()->isDeliveryForbidden()
                    && !$stockCollection->filterByOffer($offer)->filterByStores($stores['delivery'])->isEmpty()
                ){
                    $remove = false;
                }
                if(in_array('p', $availabilityFlags)
                    && !$stockCollection->filterByOffer($offer)->filterByStores($stores['pickup'])->isEmpty()
                ){
                    $remove = false;
                }
                if(in_array('r', $availabilityFlags)
                    && !$stockCollection->filterByOffer($offer)->filterByStores($stores['request'])->isEmpty()
                ){
                    $remove = false;
                }

                if($remove){
                    $productSearchResult->getProductCollection()->get($key)->removeOffer($offer->getId());
                }
            }
        }

        return $productSearchResult;
    }
}
