<?php

namespace FourPaws\Search\Helper;

use Elastica\Aggregation\AbstractAggregation;
use Elastica\Query;
use Elastica\QueryBuilder\DSL\Aggregation;
use Elastica\ResultSet;
use FourPaws\Catalog\Collection\AggCollection;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Model\Filter\FilterInterface;
use FourPaws\Catalog\Model\Filter\InternalFilter;
use FourPaws\Search\Collection\BucketCollection;
use FourPaws\Search\Model\Bucket;
use LogicException;
use UnexpectedValueException;

class AggsHelper
{

    /**
     * Применяет аггрегации к запросу.
     *
     * @param                  $query
     * @param FilterCollection $filters
     *
     * @return Query
     */
    public function setAggs(Query $query, FilterCollection $filters)
    {
        /** @var AbstractAggregation $aggregation */
        foreach ($this->getAggs($filters) as $aggregation) {
            $query->addAggregation($aggregation);
        }

        return $query;
    }

    /**
     * Схлопывает фильтры по результатам запроса по поиску товаров.
     *
     * @param FilterCollection $filterCollection
     * @param ResultSet        $resultSet
     *
     * @throws UnexpectedValueException
     */
    public function collapseFilters(FilterCollection $filterCollection, ResultSet $resultSet)
    {
        if (!$resultSet->hasAggregations()) {
            throw new LogicException('Результат поиска не содержит результатов аггрегаций.');
        }

        $filterByAggNameIndex = [];
        /** @var FilterInterface $filter */
        foreach ($filterCollection as $filter) {

            /** @var AbstractAggregation $agg */
            foreach ($filter->getAggs() as $agg) {
                $filterByAggNameIndex[$agg->getName()] = $filter;
            }
        }

        $aggregations = $resultSet->getAggregations();
        $this->findAndApplyFilterAggResult($filterByAggNameIndex, $aggregations);
    }

    /**
     * @param array $filterByAggNameIndex
     * @param array $aggregations
     *
     * @throws UnexpectedValueException
     */
    private function findAndApplyFilterAggResult(array $filterByAggNameIndex, array $aggregations)
    {
        foreach ($aggregations as $aggName => $aggResult) {
            if (array_key_exists($aggName, $filterByAggNameIndex)) {

                /** @var FilterInterface $filter */
                $filter = $filterByAggNameIndex[$aggName];
                $filter->collapse($aggName, $aggResult);
            } elseif (\is_array($aggResult)) {
                $this->findAndApplyFilterAggResult($filterByAggNameIndex, $aggResult);
            }
        }
    }

    /**
     * @param array $buckets
     *
     * @throws UnexpectedValueException
     * @return BucketCollection
     */
    public static function makeBucketCollection(array $buckets): BucketCollection
    {
        $bucketCollection = new BucketCollection();

        foreach ($buckets as $bucket) {
            if (!isset($bucket['key'], $bucket['doc_count'])) {
                throw new UnexpectedValueException('Плохой bucket: нет key или doc_count');
            }

            $bucketObject = (new Bucket())->withKey(trim($bucket['key']))
                ->withDocCount((int)$bucket['doc_count']);

            $bucketCollection->set($bucketObject->getKey(), $bucketObject);
        }

        return $bucketCollection;
    }

    /**
     * Возвращает массив аггрегаций применительно к текущему состоянию выбранных фильтров.
     *
     * @param FilterCollection $filterCollection
     *
     * @return AggCollection
     *
     */
    public function getAggs(FilterCollection $filterCollection): AggCollection
    {
        /**
         *
         * Правила построения аггрегаций для настоящего фасетного поиска:
         *
         * 1 Если фильтр не выбран, то аггрегация по нему идёт обычная.
         * 2 Если выбран 1 фильтр, то аггрегация по нему должна быть в global-аггрегации.
         * 3 Если выбрано 2 и более фильтров, то аггрегация по каждому из них должна быть в global-аггрегации,
         * в которую вложено соответствующее количество filter-аггрегаций по всем выбранным фильтрам, кроме текущего.
         *
         */
        $aggBuilder = new Aggregation();

        $result = new AggCollection();

        $subCnt = 0;

        /** @var FilterInterface $currentFilter */
        foreach ($filterCollection as $currentFilter) {

            //У внутреннего фильтра не может быть аггрегаций
            if ($currentFilter instanceof InternalFilter) {
                continue;
            }

            //Если фильтр не выбран
            if (!$currentFilter->hasCheckedVariants()) {

                //По нему обычные аггрегации
                foreach ($currentFilter->getAggs() as $aggregation) {
                    $result->add($aggregation);
                }
            } else {
                
                /**
                 * Подготовить глобальную аггрегацию
                 */
                $globAgg = $aggBuilder->global_agg('glob');
                $result->add($globAgg);

                //Если выбран, то будет вложенная агрегация с фильтрацией по остальным выбранным фильтрам
                foreach ($currentFilter->getAggs() as $aggregation) {


                    //Выбранные фильтры, среди которых исключён текущий
                    $otherCheckedFilters = $filterCollection->filter(
                        function (FilterInterface $filter) use ($currentFilter) {
                            return $filter !== $currentFilter && $filter->hasCheckedVariants();
                        }
                    );

                    $curNode = $globAgg;

                    /** @var FilterInterface $filter */
                    foreach ($otherCheckedFilters as $filter) {
                        $subFilterAgg = $aggBuilder->filter(
                            'subFilter_' . ++$subCnt,
                            $filter->getFilterRule()
                        );

                        $curNode->addAggregation($subFilterAgg);

                        $curNode = $subFilterAgg;
                    }

                    $curNode->addAggregation($aggregation);
                }
            }
        }

        return $result;
    }
}
