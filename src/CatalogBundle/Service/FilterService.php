<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\CatalogBundle\Service;

use Elastica\QueryBuilder;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Filter\InternalFilter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class FilterService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var FilterHelper
     */
    private $filterHelper;

    /**
     * FilterService constructor.
     * @param FilterHelper $filterHelper
     */
    public function __construct(FilterHelper $filterHelper)
    {
        $this->filterHelper = $filterHelper;
    }

    public function getCategoryFilters(Category $category)
    {
        $filterCollection = new FilterCollection();

        /**
         * При фильтрации по категории всегда фильтруем с ней
         */
        if ($category->getId()) {
            $filterCollection->add($category);
        }
        /**
         * Фильтры активности
         */
        foreach ($this->getInternalFilters() as $filter) {
            $filterCollection->add($filter);
        }

        /**
         * Загружаем фильтры по категории
         */
        $categoryFilters = $this->filterHelper->getCategoryFilters($category->getId(), $category->getIblockId());
        foreach ($categoryFilters as $categoryFilter) {
            $filterCollection->add($categoryFilter);
        }
        return $filterCollection;
    }

    /**
     * Возвращает внутренние неотключаемые фильтры, которые должны добавляться к любому запросу товаров из
     * Elasticsearch, чтобы обеспечить корректность выборки: активные бренды, товары, офферы, цена правильного региона.
     *
     * @return FilterCollection
     */
    public function getInternalFilters(): FilterCollection
    {
        // В будущем можно добавить учёт дат активности элементов инфоблоков.
        // See: https://www.elastic.co/guide/en/elasticsearch/reference/5.5/query-dsl-range-query.html

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $internalFilterCollection = new FilterCollection();


        try {
            foreach ($this->getActiveFilters() as $filter) {
                $internalFilterCollection->add($filter);
            }
            /**
             * @todo Фильтр по региональной цене
             */
//            $internalFilterCollection->add($this->getRegionInternalFilter());
        } catch (\InvalidArgumentException $exception) {
            /**
             * @todo log exception
             */
        }

        return $internalFilterCollection;
    }

    /**
     * @return FilterHelper
     */
    public function getFilterHelper(): FilterHelper
    {
        return $this->filterHelper;
    }

    /**
     * Фильтры отвечающие за активность продукта, бренда, офферов
     *
     * @return FilterCollection
     */
    protected function getActiveFilters(): FilterCollection
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $activeFilterCollection = new FilterCollection();

        $queryBuilder = new QueryBuilder();
        try {
            $activeFilterCollection->add(
                InternalFilter::create(
                    'ProductActive',
                    $queryBuilder->query()->term(['active' => true])
                )
            );

            $activeFilterCollection->add(
                InternalFilter::create(
                    'BrandActive',
                    $queryBuilder->query()->term(['brand.active' => true])
                )
            );

            $activeFilterCollection->add(
                InternalFilter::create(
                    'OffersActive',
                    $queryBuilder->query()->nested()
                        ->setPath('offers')
                        ->setQuery($queryBuilder->query()->term(['offers.active' => true]))
                )
            );
        } catch (\InvalidArgumentException $exception) {
        }
        return $activeFilterCollection;
    }

    protected function getRegionInternalFilter(): InternalFilter
    {
        //$currentRegionCode = $this->locationService->getCurrentRegionCode();
//        $currentRegionCode = LocationService::DEFAULT_REGION_CODE;
//
//        return InternalFilter::create(
//            'CurrentRegion',
//            (new Nested())->setPath('offers.prices')
//                ->setQuery(new Term(['offers.prices.REGION_ID' => $currentRegionCode]))
//        );
    }
}
