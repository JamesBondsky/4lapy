<?php

namespace FourPaws\Catalog;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Highloadblock\DataManager;
use CIBlockFindTools;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Elastica\Query\Nested;
use Elastica\Query\Term;
use Elastica\QueryBuilder;
use Exception;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Exception\BrandNotFoundException;
use FourPaws\Catalog\Exception\CategoryNotFoundException;
use FourPaws\Catalog\Helper\FilterHelper;
use FourPaws\Catalog\Model\Brand;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\Filter\BrandFilter;
use FourPaws\Catalog\Model\Filter\FilterInterface;
use FourPaws\Catalog\Model\Filter\InternalFilter;
use FourPaws\Catalog\Model\Sorting;
use FourPaws\Catalog\Query\BrandQuery;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Location\LocationService;
use FourPaws\Search\Model\Navigation;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use WebArch\BitrixCache\BitrixCache;

class CatalogService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var DataManager для HL-блока Filter aka класс \FilterTable
     */
    protected $filterTable;

    /**
     * @var FilterHelper
     */
    protected $filterHelper;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * CatalogService constructor.
     *
     * @param DataManager $filterTable
     * @param Serializer $serializer
     * @param LocationService $locationService
     */
    public function __construct(DataManager $filterTable, Serializer $serializer, LocationService $locationService)
    {
        $this->filterTable = $filterTable;
        $this->serializer = $serializer;
        $this->locationService = $locationService;
    }

    /**
     * Возвращает фильтры, доступные в категории.
     *
     * @param Category $category
     *
     * @return FilterCollection
     * @throws Exception
     */
    public function getFilters(Category $category): FilterCollection
    {
        $availablePropIndexByCode = $this->getAvailablePropIndexByCode($category->getIblockId(), $category->getId());

        $availableFilterList = [];

        //Внутренние фильтры, которые нельзя никогда отключить и которые невидимы.
        foreach ($this->getInternalFilters() as $filter) {
            $availableFilterList[] = $filter;
        }

        //Категория - фундаментальный фильтр и есть всегда
        $availableFilterList[] = $category;

        /** @var array $filterFields */
        foreach ($this->getFilterFieldsList() as $filterFields) {

            if (!isset($filterFields['UF_CLASS_NAME']) || !class_exists($filterFields['UF_CLASS_NAME'])) {
                $this->log()->warning(
                    sprintf('Filter class `%s` not found', $filterFields['UF_CLASS_NAME']),
                    $filterFields
                );
                continue;
            }
            $className = $filterFields['UF_CLASS_NAME'];

            /** @var FilterBase $curFilter */
            $curFilter = new $className($filterFields);

            //Если фильтр назначен на свойство, а его для этого раздела не выбрано, то фильтра не будет
            if ($curFilter->getPropCode() != '' && !isset($availablePropIndexByCode[$curFilter->getPropCode()])) {
                continue;
            }

            $availableFilterList[] = $curFilter;
        }

        return new FilterCollection($availableFilterList);
    }

    /**
     * Возвращает категорию каталога с полностью настроенными фильтрами в зависимости от переданного запроса.
     *
     * @param Request $request
     *
     * @return Category
     *
     * @throws BrandNotFoundException
     * @throws CategoryNotFoundException
     * @throws Exception
     * @throws IblockNotFoundException
     * @throws RuntimeException
     */
    public function getCategory(Request $request): Category
    {
        $codePath = trim($request->getPathInfo(), '/');

        /**
         * Корневая категория должна определяться,
         * когда идёт поиск, а также когда идёт запрос каталога по бренду,
         * т.к. поиск товаров ведётся по всему каталогу.
         */
        if ($codePath === 'search') {

            $category = Category::createRoot();
            $this->getFilterHelper()->initCategoryFilters($category, $request);

        } elseif (strpos($codePath, 'brand/') === 0) {

            $category = Category::createRoot();
            $this->getFilterHelper()->initCategoryFilters($category, $request);

            $brand = $this->getBrandByCodePath($codePath);
            $category->withName(
                sprintf(
                    'Товары бренда %s',
                    $brand->getName()
                )
            );

            $brandFilter = $category->getFilters()->filter(
                function (FilterInterface $filter) {
                    return $filter instanceof BrandFilter;
                }
            )->current();

            if (!($brandFilter instanceof BrandFilter)) {
                throw new RuntimeException('Фильтр по бренду не найден среди фильтров корневой категории');
            }

            $brandFilter->setCheckedVariants([$brand->getCode()]);
            $brandFilter->setVisible(false);

        } else {

            $category = $this->getCategoryByCodePath($codePath);
            $this->getFilterHelper()->initCategoryFilters($category, $request);

        }

        return $category;
    }

    /**
     * Возвращает все доступные сортировки и отмечает активную
     *
     * @param Request $request
     *
     * @return Collection|Sorting[]
     */
    public function getSortings(Request $request): Collection
    {
        $currentRegionCode = $this->locationService->getCurrentRegionCode();

        $sortings = [

            (new Sorting())->withValue('popular')
                           ->withName('популярности')
                           ->withRule(['SORT' => ['order' => 'asc']]),

            (new Sorting())->withValue('up-price')
                           ->withName('возрастанию цены')
                           ->withRule(
                               [
                                   'offers.prices.PRICE' => [
                                       'order'         => 'asc',
                                       'mode'          => 'min',
                                       'nested_path'   => 'offers.prices',
                                       'nested_filter' => [
                                           'term' => ['offers.prices.REGION_ID' => $currentRegionCode],
                                       ],

                                   ],
                               ]
                           ),

            (new Sorting())->withValue('down-price')
                           ->withName('убыванию цены')
                           ->withRule(
                               [
                                   'offers.prices.PRICE' => [
                                       'order'         => 'desc',
                                       'mode'          => 'max',
                                       'nested_path'   => 'offers.prices',
                                       'nested_filter' => [
                                           'term' => ['offers.prices.REGION_ID' => $currentRegionCode],
                                       ],

                                   ],
                               ]
                           ),

        ];

        //Если задана строка поиска
        if ($this->getSearchString($request) != '') {
            //Добавить сортировку по релевантности
            array_unshift(
                $sortings,
                (new Sorting())->withValue('relevance')
                               ->withName('релевантности')
                               ->withRule(['_score'])
            );
        }

        $sortingCollection = new ArrayCollection($sortings);

        $selectedSortValue = trim($request->query->get('sort'));

        //Определить выбранную сортировку
        $activeSorting = $sortingCollection->filter(
            function (Sorting $sorting) use ($selectedSortValue) {

                return $sorting->getValue() === $selectedSortValue;

            }
        )->current();

        //Если ничего не выбрано, то по умолчанию выбрать первую
        if (!($activeSorting instanceof Sorting)) {
            $activeSorting = $sortingCollection->first();
        }

        $activeSorting->withSelected(true);

        return $sortingCollection;
    }

    /**
     * Возвращает выбранную сортировку
     *
     * @param Request $request
     *
     * @return Sorting
     * @throws RuntimeException
     */
    public function getSelectedSorting(Request $request): Sorting
    {
        $selectedSorting = $this->getSortings($request)->filter(
            function (Sorting $sorting) {
                return $sorting->isSelected();
            }
        )->first();

        if (!($selectedSorting instanceof Sorting)) {
            throw new RuntimeException('Не удалось обнаружить выбранную сортировку');
        }

        return $selectedSorting;
    }

    /**
     * @param Request $request
     *
     * @return Navigation
     */
    public function getNavigation(Request $request): Navigation
    {
        $navRule = new Navigation();

        $pageNumber = (int)$request->query->get('page');

        if ($pageNumber > 0) {
            $navRule->withPage($pageNumber);
        }

        $pageSize = (int)$request->query->get('pageSize');

        /**
         * Здесь можно задать набор вариантов допустимых значений количества товаров на странице.
         */
        $availablePageSizes = [20, 40];
        if ($pageSize > 0 && in_array($pageSize, $availablePageSizes)) {
            $navRule->withPageSize($pageSize);
        } else {
            $navRule->withPage(reset($availablePageSizes));
        }

        return $navRule;

    }

    /**
     * @param string $codePath
     *
     * @return Category
     * @throws CategoryNotFoundException
     * @throws IblockNotFoundException
     * @throws Exception
     */
    protected function getCategoryByCodePath(string $codePath): Category
    {
        $categoryId = $this->getCategoryIdByCodePath($codePath);
        if ($categoryId <= 0) {
            throw new CategoryNotFoundException(
                sprintf('Категория каталога по пути `%s` не найдена.', $codePath)
            );
        }

        $categoryCollection = (new CategoryQuery())->withFilterParameter('=ID', $categoryId)->exec();
        if ($categoryCollection->isEmpty()) {
            throw new CategoryNotFoundException(
                sprintf('Категория каталога #%d не найдена.', $categoryId)
            );
        }
        if ($categoryCollection->count() > 1) {
            throw new CategoryNotFoundException(
                sprintf('Найдено более одной категории каталога с id %d', $categoryId)
            );
        }

        return $categoryCollection->current();
    }

    /**
     * Возвращает строку поиска, а если она не задана, то пустую строку.
     *
     * @param Request $request
     *
     * @return string
     */
    public function getSearchString(Request $request): string
    {
        return trim($request->query->get('q'));
    }

    /**
     * @return FilterHelper
     */
    public function getFilterHelper(): FilterHelper
    {
        if (is_null($this->filterHelper)) {
            $this->filterHelper = new FilterHelper();
        }

        return $this->filterHelper;
    }

    /**
     * @param string $codePath
     *
     * @return int
     * @throws IblockNotFoundException
     * @throws Exception
     */
    private function getCategoryIdByCodePath(string $codePath): int
    {
        $categoryId = 0;

        $getCategoryIDByCodePath = function () use ($codePath) {
            $categoryId = (int)CIBlockFindTools::GetSectionIDByCodePath(
                IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
                $codePath
            );

            if ($categoryId <= 0) {
                //(Это сбросит запись кеша)
                return null;
            }

            return ['categoryId' => $categoryId];
        };

        $getSectionIDByCodePathResult = (new BitrixCache())
            ->withId(__METHOD__ . ':' . $codePath)
            ->withIblockTag(
                IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS)
            )
            ->resultOf($getCategoryIDByCodePath);

        if (isset($getSectionIDByCodePathResult['categoryId'])) {
            $categoryId = (int)$getSectionIDByCodePathResult['categoryId'];
        }

        return $categoryId;
    }

    /**
     * @param $codePath
     *
     * @return Brand
     * @throws BrandNotFoundException
     */
    private function getBrandByCodePath($codePath): Brand
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($devNull, $brandCode) = explode('/', $codePath);

        $brandCollection = (new BrandQuery())->withFilterParameter('=CODE', $brandCode)->exec();

        if ($brandCollection->isEmpty()) {
            throw new BrandNotFoundException(
                sprintf('Бренд с кодом `%s` не найден.', $brandCode)
            );
        }

        if ($brandCollection->count() > 1) {
            throw new BrandNotFoundException(
                sprintf('Найдено более одного бренда с кодом `%s`', $brandCode)
            );
        }

        return $brandCollection->current();
    }

    /**
     * Возвращает список фильтров (данные из HL-блока, достаточные для создания объектов фильтров).
     *
     * @return array
     * @throws Exception
     */
    private function getFilterFieldsList(): array
    {
        $doGetFilterFieldsList = function () {
            $filterFieldsList = [];

            $dbAllFilterList = $this->filterTable::query()
                                                 ->setSelect(['*'])
                                                 ->setFilter(['UF_ACTIVE' => 1])
                                                 ->setOrder(['UF_SORT' => 'ASC'])
                                                 ->exec();
            while ($filterFields = $dbAllFilterList->fetch()) {
                $filterFieldsList[] = $filterFields;
            }

            return $filterFieldsList;
        };

        return (new BitrixCache())->withId(__METHOD__)
                                  ->withTag('catalog:filters')
                                  ->resultOf($doGetFilterFieldsList);
    }

    /**
     * Возвращает индекс доступных свойств категории по коду свойства.
     *
     * @param int $categoryIblockId
     * @param int $categoryId
     *
     * @return array
     * @throws Exception
     */
    private function getAvailablePropIndexByCode(int $categoryIblockId, int $categoryId)
    {
        $doGetAvailablePropIndexByCode = function () use ($categoryIblockId, $categoryId) {

            /**
             * Запросить информацию о привязках свойств к категориям
             */
            $propertyLinks = $this->getFilterHelper()->getSectionPropertyLinks($categoryIblockId, $categoryId);

            /**
             * Составить индекс по коду свойства и только для свойств, выбранных для "умного фильтра"
             */
            $availablePropIndexByCode = [];
            foreach ($propertyLinks as $propertyLink) {
                if (
                    !isset($propertyLink['SMART_FILTER'])
                    || $propertyLink['SMART_FILTER'] !== BitrixUtils::BX_BOOL_TRUE
                    || !isset($propertyLink['PROPERTY_CODE'])
                ) {
                    continue;
                }
                $availablePropIndexByCode[$propertyLink['PROPERTY_CODE']] = true;
            }

            return $availablePropIndexByCode;
        };

        return (new BitrixCache())->withId(__METHOD__)
                                  ->withTag('catalog:filters')
                                  ->resultOf($doGetAvailablePropIndexByCode);
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

        $queryBuilder = new QueryBuilder();

        $internalFilterCollection = new FilterCollection();

        $internalFilterCollection->add(
            InternalFilter::create(
                'ProductActive',
                $queryBuilder->query()->term(['active' => true])
            )
        );;

        $internalFilterCollection->add(
            InternalFilter::create(
                'BrandActive',
                $queryBuilder->query()->term(['brand.active' => true])
            )
        );

        $internalFilterCollection->add(
            InternalFilter::create(
                'OffersActive',
                $queryBuilder->query()->nested()
                             ->setPath('offers')
                             ->setQuery($queryBuilder->query()->term(['offers.active' => true]))
            )
        );

        $internalFilterCollection->add($this->getRegionInternalFilter());

        return $internalFilterCollection;

    }

    /**
     * @return InternalFilter
     */
    public function getRegionInternalFilter(): InternalFilter
    {
        $currentRegionCode = $this->locationService->getCurrentRegionCode();

        return InternalFilter::create(
            'CurrentRegion',
            (new Nested())->setPath('offers.prices')
                          ->setQuery(new Term(['offers.prices.REGION_ID' => $currentRegionCode]))
        );
    }

}
