<?php

namespace FourPaws\Search\Helper;

use Elastica\Aggregation\AbstractAggregation;
use Elastica\Aggregation\GlobalAggregation;
use Elastica\Query;
use Elastica\QueryBuilder\DSL\Aggregation;
use Elastica\ResultSet;
use FourPaws\Catalog\Collection\AggCollection;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Model\Filter\FilterInterface;
use FourPaws\Catalog\Model\Variant;
use FourPaws\Search\Collection\BucketCollection;
use FourPaws\Search\Model\Bucket;
use LogicException;
use UnexpectedValueException;

class AggsHelper
{

    /**
     * Применяет аггрегации к запросу.
     *
     * @param $query
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
     * @param ResultSet $resultSet
     *
     * @throws UnexpectedValueException
     */
    public function collapseFilters(FilterCollection $filterCollection, ResultSet $resultSet)
    {
        if (!$resultSet->hasAggregations()) {
            throw new LogicException('Результат поиска не содержит аггрегаций.');
        }

        $filterByCodeIndex = [];
        /** @var FilterInterface $filter */
        foreach ($filterCollection as $filter) {
            $filterByCodeIndex[$filter->getFilterCode()] = $filter;
        }

        $this->findAndApplyFilterAgg($filterByCodeIndex, $resultSet->getAggregations());

    }

    /**
     * @param array $filterByCodeIndex
     * @param array $aggs
     *
     * @throws UnexpectedValueException
     */
    private function findAndApplyFilterAgg(array $filterByCodeIndex, array $aggs)
    {
        foreach ($aggs as $agName => $aggBody) {
            if (
                array_key_exists($agName, $filterByCodeIndex)
                && array_key_exists('buckets', $aggBody)
                && is_array($aggBody['buckets'])
            ) {

                $this->collapseFilter($filterByCodeIndex[$agName], self::makeBucketCollection($aggBody['buckets']));

            } elseif (is_array($aggBody)) {

                $this->findAndApplyFilterAgg($filterByCodeIndex, $aggBody);

            }
        }
    }

    /**
     * @param array $buckets
     *
     * @return BucketCollection
     * @throws UnexpectedValueException
     */
    private static function makeBucketCollection(array $buckets): BucketCollection
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

        /**
         * Подготовить глобальную аггрегацию, если выбран хотя бы один фильтр.
         */
        if ($filterCollection->hasCheckedFilter()) {
            $globAgg = $aggBuilder->global_agg('glob');
            $result->add($globAgg);
        }

        $subCnt = 0;

        /** @var FilterInterface $currentFilter */
        foreach ($filterCollection as $currentFilter) {

            //Если фильтр не выбран
            if (!$currentFilter->hasCheckedVariants()) {

                //По нему обычные аггрегации
                foreach ($currentFilter->getAggs() as $aggregation) {
                    $result->add($aggregation);
                }

            } else {

                if (!isset($globAgg) || !($globAgg instanceof GlobalAggregation)) {
                    throw new LogicException('Не подготовлена глобальная аггрегация.');
                }

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

    /**
     * @param FilterInterface $filter
     * @param BucketCollection $bucketCollection
     */
    private function collapseFilter(FilterInterface $filter, BucketCollection $bucketCollection)
    {
        $filter->getAllVariants()->map(
            function (Variant $variant) use ($bucketCollection) {

                if ($bucketCollection->containsKey($variant->getValue())) {

                    /** @var Bucket $bucket */
                    $bucket = $bucketCollection->get($variant->getValue());

                    $variant->withAvailable(true)
                            ->withCount($bucket->getDocCount());

                } else {
                    $variant->withAvailable(false)
                            ->withCount(0);
                }

                return $variant;
            }
        );
    }

}
