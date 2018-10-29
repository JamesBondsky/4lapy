<?php

namespace FourPaws\Search;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Elastica\Exception\InvalidException;
use Elastica\Query;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\QueryBuilder;
use Elastica\Suggest;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Model\Filter\FilterInterface;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Model\Sorting;
use FourPaws\CatalogBundle\Service\SortService;
use FourPaws\Search\Helper\AggsHelper;
use FourPaws\Search\Helper\IndexHelper;
use FourPaws\Search\Model\Navigation;
use FourPaws\Search\Model\ProductSearchResult;
use FourPaws\Search\Model\ProductSuggestResult;
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
     * @param Sorting          $sorting
     * @param Navigation       $navigation
     * @param string           $searchString
     *
     * @return ProductSearchResult
     * @throws InvalidException
     * @throws ApplicationCreateException
     * @throws InvalidArgumentException
     * @throws ArgumentException
     */
    public function searchProducts(
        FilterCollection $filters,
        Sorting $sorting,
        Navigation $navigation,
        string $searchString = ''
    ): ProductSearchResult
    {
        $search = $this->getIndexHelper()->createProductSearch();

        if ($searchString !== '') {
            $search->getQuery()->setMinScore(0.9);
        }

        $search->getQuery()
               ->setFrom($navigation->getFrom())
               ->setSize($navigation->getSize())
               ->setSort($sorting->getRule())
               ->setParam('query', $this->getFullQueryRule($filters, $searchString))
               ->setHighlight(['fields' => ['offers.XML_ID' => (object)[]]]);

        $this->getAggsHelper()->setAggs($search->getQuery(), $filters);

        $resultSet = $search->search();
        // если задана строка поиска и не найдено совпадений, то пробуем в другой раскладке
        if ($searchString && !$resultSet->getTotalHits()) {
            $from = preg_match('/[ЁёА-я]/u', $searchString) ? 'ru' : 'en';
            $to = $from === 'ru' ? 'en' : 'ru';
            $searchString = \CSearchLanguage::ConvertKeyboardLayout($searchString, $from, $to);
            $search->getQuery()->setParam('query', $this->getFullQueryRule($filters, $searchString));
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

        return new ProductSearchResult($resultSet, $navigation, $searchString);
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
        $completion->setParam('fuzzy', ['fuzziness' => 2]);
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
     *
     * @return BoolQuery
     * @throws InvalidException
     */
    public function getQueryRule(string $searchString): BoolQuery
    {
        $queryBuilder = new QueryBuilder();
        $boolQuery = $queryBuilder->query()->bool();

        if ($searchString === '') {
            return $boolQuery;
        }

        $textFields = [
            'PREVIEW_TEXT',
            'DETAIL_TEXT',
            'PROPERTY_SPECIFICATIONS.TEXT',
        ];

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
                                         'boost' => 100.0,
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
                                         'boost' => 100.0,
                                         '_name' => 'barcode',
                                     ],
                                 ]
                             )
                         )
        );

        /*
         * 1 Бренд
         */

        //Нечёткое по бренду
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                         ->setQuery($searchString)
                         ->setFields(['brand.NAME'])
                         ->setType('best_fields')
                         ->setFuzziness('AUTO')
                         ->setAnalyzer('full-text-search')
                         ->setParam('boost', 90.0)
                         ->setParam('_name', 'brand-fuzzy')
        );

        /*
         * 2 Название товара
         */

        //Точное по фразе в названии
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                         ->setQuery($searchString)
                         ->setFields(['NAME'])
                         ->setType('phrase')
                         ->setAnalyzer('default')
                         ->setParam('boost', 80.0)
                         ->setParam('_name', 'name-phrase')
        );

        //Точное по слову в названии
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                         ->setQuery($searchString)
                         ->setFields(['NAME'])
                         ->setType('best_fields')
                         ->setFuzziness(0)
                         ->setAnalyzer('default')
                         ->setParam('boost', 70.0)
                         ->setParam('_name', 'name-exact-word')

        );

        //Нечёткое совпадение с учётом опечаток в названии
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                         ->setQuery($searchString)
                         ->setFields(['NAME'])
                         ->setType('best_fields')
                         ->setFuzziness('AUTO')
                         ->setAnalyzer('full-text-search')
                         ->setParam('boost', 60.0)
                         ->setParam('_name', 'name-fuzzy-word')

        );

        //Совпадение по звучанию в названии
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                         ->setQuery($searchString)
                         ->setFields(['product.NAME.phonetic'])
                         ->setParam('boost', 50.0)
                         ->setParam('_name', 'name-sounds-similar')
        );

        /*
         * 4 Описание товара
         */

        //Точное по фразе
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                         ->setQuery($searchString)
                         ->setFields($textFields)
                         ->setType('phrase')
                         ->setAnalyzer('full-text-search')
                         ->setParam('boost', 0.5)
                         ->setParam('_name', 'desc-phrase')
        );

        //Точное по тексту
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                         ->setQuery($searchString)
                         ->setFields($textFields)
                         ->setType('best_fields')
                         ->setFuzziness(0)
                         ->setAnalyzer('default')
                         ->setParam('boost', 0.5)
                         ->setParam('_name', 'desc-exact-word')
        );

        /**
         * Отключено для большей релевантности поиска
         */
        /*
        //Нечёткое совпадение с учётом опечаток
        $boolQuery->addShould(
            $queryBuilder->query()->multi_match()
                ->setQuery($searchString)
                ->setFields($textFields)
                ->setType('best_fields')
                ->setFuzziness(1)
                ->setAnalyzer('full-text-search')
                ->setParam('boost', 0.5)
                ->setParam('_name', 'desc-fuzzy-word')
        );
        */

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

        $boolQuery = $this->getQueryRule($searchString);
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
                500,
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
            // собственная торговая марка +50
            ->addWeightFunction(
                50,
                $queryBuilder
                    ->query()
                    ->match()
                    ->setField('PROPERTY_STM', true)
            )
            // популярные товары +50
            ->addWeightFunction(
                50,
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
            // товар, имеющий акции +100
            ->addWeightFunction(
                100,
                $queryBuilder
                    ->query()
                    ->match()
                    ->setField('hasActions', true)
            )
            // новинки +50
            ->addWeightFunction(
                50,
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
            // товары с шильдиками +20
            ->addWeightFunction(
                20,
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
}
