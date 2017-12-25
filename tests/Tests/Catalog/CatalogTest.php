<?php

namespace FourPaws\Test\Tests\Catalog;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Elastica\Aggregation\AbstractAggregation;
use Elastica\Query;
use Elastica\QueryBuilder;
use FourPaws\App\Application;
use FourPaws\Catalog\CatalogService;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Model\Brand;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Filter\BrandFilter;
use FourPaws\Catalog\Model\Filter\PetAgeFilter;
use FourPaws\Catalog\Model\Filter\PetGenderFilter;
use FourPaws\Catalog\Model\Filter\PetSizeFilter;
use FourPaws\Catalog\Model\Sorting;
use FourPaws\Catalog\Model\Variant;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\Location\LocationService;
use FourPaws\Search\SearchService;
use FourPaws\Test\Tests\TestBase;
use PHPUnit\Framework\Assert;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class CatalogTest extends TestBase
{
//    /**
//     * @var SearchService
//     */
//    private $searchService;
//
//    /**
//     * @var CatalogService
//     */
//    protected $catalogService;
//
//    /**
//     * @var LocationService
//     */
//    private $locationService;
//
//    /**
//     * CatalogTest constructor.
//     *
//     * @param null $name
//     * @param array $data
//     * @param string $dataName
//     *
//     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
//     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
//     */
//    public function __construct($name = null, array $data = [], $dataName = '')
//    {
//        parent::__construct($name, $data, $dataName);
//        $this->catalogService = Application::getInstance()->getContainer()->get('catalog.service');
//        $this->searchService = Application::getInstance()->getContainer()->get('search.service');
//        $this->locationService = Application::getInstance()->getContainer()->get('location.service');
//    }
//
//    /**
//     * @throws IblockNotFoundException
//     * @throws \Exception
//     * @throws RuntimeException
//     */
//    public function testCategoryRecognition()
//    {
//        $expectedCategory = self::$applicationManager->getCatalogHelper()->getExistingCategory();
//
//        $request = Request::create(
//            'http://4lapy.ru' . $expectedCategory->getSectionPageUrl(),
//            'GET',
//            [
//                'A' => 1,
//                'B' => 'C',
//            ]
//        );
//
//        $actualCategory = $this->catalogService->getCategory($request);
//
//        Assert::assertEquals(
//            $expectedCategory->getId(),
//            $actualCategory->getId(),
//            'Expected category must be recognized.'
//        );
//
//    }
//
//    /**
//     * @throws IblockNotFoundException
//     * @throws \Exception
//     * @throws \PHPUnit_Framework_Exception
//     * @throws RuntimeException
//     */
//    public function testSearchCategoryRecognition()
//    {
//
//        $request = Request::create(
//            'http://4lapy.ru/search',
//            'GET',
//            [
//                'A' => 1,
//                'B' => 'C',
//            ]
//        );
//
//        $actualCategory = $this->catalogService->getCategory($request);
//
//        $this->isRootCategory($actualCategory);
//
//    }
//
//    /**
//     * @throws IblockNotFoundException
//     * @throws \Exception
//     * @throws \PHPUnit_Framework_Exception
//     * @throws RuntimeException
//     */
//    public function testBrandCategoryRecognition()
//    {
//        $request = Request::create(
//            'http://4lapy.ru/brand/akana/',
//            'GET',
//            [
//                'A' => 1,
//                'B' => 'C',
//            ]
//        );
//
//        $actualCategory = $this->catalogService->getCategory($request);
//
//        $this->isRootCategory($actualCategory);
//    }
//
//    /**
//     * @throws IblockNotFoundException
//     * @throws \Exception
//     * @throws \PHPUnit_Framework_Exception
//     * @throws RuntimeException
//     */
//    public function testCategoryFiltersState()
//    {
//
//        $brandList = self::$applicationManager->getCatalogHelper()->getRandomBrands(5)->toArray();
//
//        $expectedBrandCodeList = array_map(
//            function (Brand $brand) {
//                return $brand->getCode();
//            },
//            $brandList
//        );
//
//        /** @var Category $someCategory */
//        $someCategory = (new CategoryQuery())->withNav(['nTopCount' => 1])
//                                             ->exec()
//                                             ->current();
//
//        Assert::assertInstanceOf(
//            Category::class,
//            $someCategory,
//            'Expected category should exists.'
//        );
//
//        $parameters = [
//            'Brand' => implode(',', $expectedBrandCodeList),
//        ];
//        $request = Request::create(
//            'http://4lapy.ru' . $someCategory->getSectionPageUrl(),
//            'GET',
//            $parameters
//        );
//
//        $category = $this->catalogService->getCategory($request);
//
//        $actualBrandCodeList = [];
//
//        /** @var \FourPaws\Catalog\Model\Filter\Abstraction\FilterBase $filter */
//        foreach ($category->getFilters() as $filter) {
//
//            if ($filter instanceof BrandFilter) {
//
//                /** @var \FourPaws\Catalog\Model\Variant $variant */
//                foreach ($filter->getCheckedVariants() as $variant) {
//                    $actualBrandCodeList[] = $variant->getValue();
//                }
//            }
//
//        }
//
//        Assert::assertNotNull($actualBrandCodeList);
//
//        Assert::assertEquals(
//            $expectedBrandCodeList,
//            $actualBrandCodeList,
//            'All and only expected brands are selected',
//            0.0,
//            10,
//            true
//        );
//
//    }
//
//    /**
//     * @throws IblockNotFoundException
//     * @throws \Exception
//     * @throws RuntimeException
//     */
//    public function testFilterRule()
//    {
//        $expectedCategory = self::$applicationManager->getCatalogHelper()->getExistingCategory();
//
//        $brandList = self::$applicationManager->getCatalogHelper()->getRandomBrands(3)->toArray();
//
//        $expectedBrandCodeList = array_values(
//            array_map(
//                function (Brand $brand) {
//                    return $brand->getCode();
//                },
//                $brandList
//            )
//        );
//
//        $expectedCategoryIdList = array_values(
//            array_map(
//                function (Variant $variant) {
//                    return $variant->getValue();
//                },
//                $expectedCategory->getAllVariants()->toArray()
//            )
//        );
//
//        $queryBuilder = new QueryBuilder();
//
//        $expectedFilterRule = $queryBuilder->query()->bool();
//
//        $currentRegionCode = $this->locationService->getCurrentRegionCode();
//
//        $expectedFilterRule->addFilter($queryBuilder->query()->term(['active' => true,]));
//        $expectedFilterRule->addFilter($queryBuilder->query()->term(['brand.active' => true,]));
//        $expectedFilterRule->addFilter(
//            $queryBuilder->query()->nested()
//                         ->setPath('offers')
//                         ->setQuery($queryBuilder->query()->term(['offers.active' => true]))
//        );
//        $expectedFilterRule->addFilter(
//            $queryBuilder->query()->nested()
//                         ->setPath('offers.prices')
//                         ->setQuery($queryBuilder->query()->term(['offers.prices.REGION_ID' => $currentRegionCode]))
//        );
//        $expectedFilterRule->addFilter($queryBuilder->query()->terms('sectionIdList', $expectedCategoryIdList));
//        $expectedFilterRule->addFilter($queryBuilder->query()->terms('brand.CODE', $expectedBrandCodeList));
//
//        $parameters = [
//            'Brand' => implode(',', $expectedBrandCodeList),
//        ];
//
//        $request = Request::create(
//            'http://4lapy.ru' . $expectedCategory->getSectionPageUrl(),
//            'GET',
//            $parameters
//        );
//
//        $actualCategory = $this->catalogService->getCategory($request);
//
//        $actualFilterRule = $this->searchService->getFullQueryRule($actualCategory->getFilters(), '');
//
//        $this->assertEquals(
//            $expectedFilterRule->toArray(),
//            $actualFilterRule->toArray(),
//            'Filter rules match',
//            0.0,
//            10,
//            true
//        );
//    }
//
//    /**
//     * @throws RuntimeException
//     * @throws IblockNotFoundException
//     * @throws \Exception
//     */
//    public function testAggRule()
//    {
//        $filterCollection = new FilterCollection();
//
//        /**
//         * Выбрать какую-нибудь категорию
//         */
//        $categoryFilter = self::$applicationManager->getCatalogHelper()->getExistingCategory();
//        $checkedCategoriesValues = array_map(
//            function (Variant $variant) {
//                return $variant->getValue();
//            },
//            $categoryFilter->getCheckedVariants()->toArray()
//        );
//        $filterCollection->add($categoryFilter);
//
//        /**
//         * Выбрать три случайных бренда
//         */
//        $brandFilter = new BrandFilter();
//        $checkedBrands = self::$applicationManager->getCatalogHelper()->getRandomCheckedVariants($brandFilter, 3);
//        $checkedBrandValues = array_map(
//            function (Variant $variant) {
//                return $variant->getValue();
//            },
//            $checkedBrands->toArray()
//        );
//        $filterCollection->add($brandFilter);
//
//        /**
//         * Выбрать 1 случайный возраст животного
//         */
//        $petAgeFilter = new PetAgeFilter();
//        $checkedPetAges = self::$applicationManager->getCatalogHelper()->getRandomCheckedVariants($petAgeFilter, 1);
//        $checkedPetAgeValues = array_map(
//            function (Variant $variant) {
//                return $variant->getValue();
//            },
//            $checkedPetAges->toArray()
//        );
//        $filterCollection->add($petAgeFilter);
//
//        /**
//         * Выбрать 2 случайных размера животного
//         */
//        $petSizeFilter = new PetSizeFilter();
//        $checkedPetSizes = self::$applicationManager->getCatalogHelper()->getRandomCheckedVariants($petSizeFilter, 2);
//        $checkedPetSizeValues = array_map(
//            function (Variant $variant) {
//                return $variant->getValue();
//            },
//            $checkedPetSizes->toArray()
//        );
//        $filterCollection->add($petSizeFilter);
//
//        /**
//         * И просто взять фильтр по полу животного
//         */
//        $petGenderFilter = new PetGenderFilter();
//        $filterCollection->add($petGenderFilter);
//
//        $expectedAggRule = [
//
//            //Пол не выбран - обычная аггрегация
//            $petGenderFilter->getFilterCode() => [
//                'terms' => [
//                    'field' => $petGenderFilter->getRuleCode(),
//                    'size'  => 9999,
//                ],
//            ],
//
//            //Глобальная для выбранных фильтров
//            'glob'                            => [
//                //Глобальная аггрегация должна быть пустым объектом
//                'global' => (object)[],
//                'aggs'   => [
//
//                    //Для категории: субфильтр по бренду, возрасту, размеру
//                    'subFilter_1'  => [
//                        'filter' => ['terms' => [$brandFilter->getRuleCode() => $checkedBrandValues]],
//                        'aggs'   => [
//                            'subFilter_2' => [
//                                'filter' => ['terms' => [$petAgeFilter->getRuleCode() => $checkedPetAgeValues]],
//                                'aggs'   => [
//                                    'subFilter_3' => [
//                                        'filter' => [
//                                            'terms' => [
//                                                $petSizeFilter->getRuleCode() => $checkedPetSizeValues,
//                                            ],
//                                        ],
//                                        'aggs'   => [
//                                            $categoryFilter->getFilterCode() => [
//                                                'terms' => ['field' => $categoryFilter->getRuleCode(), 'size' => 9999],
//                                            ],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//                    ],
//
//                    //Для бренда: субфильтр по категории, возрасту и размеру
//                    'subFilter_4'  => [
//                        'filter' => ['terms' => [$categoryFilter->getRuleCode() => $checkedCategoriesValues]],
//                        'aggs'   => [
//                            'subFilter_5' => [
//                                'filter' => ['terms' => [$petAgeFilter->getRuleCode() => $checkedPetAgeValues]],
//                                'aggs'   => [
//                                    'subFilter_6' => [
//                                        'filter' => [
//                                            'terms' => [
//                                                $petSizeFilter->getRuleCode() => $checkedPetSizeValues,
//                                            ],
//                                        ],
//                                        'aggs'   => [
//                                            $brandFilter->getFilterCode() => [
//                                                'terms' => ['field' => $brandFilter->getRuleCode(), 'size' => 9999],
//                                            ],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//                    ],
//
//                    //Для возраста: субфильтр по категории, бренду, размеру
//                    'subFilter_7'  => [
//                        'filter' => ['terms' => [$categoryFilter->getRuleCode() => $checkedCategoriesValues]],
//                        'aggs'   => [
//                            'subFilter_8' => [
//                                'filter' => ['terms' => [$brandFilter->getRuleCode() => $checkedBrandValues]],
//                                'aggs'   => [
//                                    'subFilter_9' => [
//                                        'filter' => [
//                                            'terms' => [
//                                                $petSizeFilter->getRuleCode() => $checkedPetSizeValues,
//                                            ],
//                                        ],
//                                        'aggs'   => [
//                                            $petAgeFilter->getFilterCode() => [
//                                                'terms' => ['field' => $petAgeFilter->getRuleCode(), 'size' => 9999],
//                                            ],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//                    ],
//
//                    //Для размера: по категории, бренду и возрасту
//                    'subFilter_10' => [
//                        'filter' => ['terms' => [$categoryFilter->getRuleCode() => $checkedCategoriesValues]],
//                        'aggs'   => [
//                            'subFilter_11' => [
//                                'filter' => ['terms' => [$brandFilter->getRuleCode() => $checkedBrandValues]],
//                                'aggs'   => [
//                                    'subFilter_12' => [
//                                        'filter' => ['terms' => [$petAgeFilter->getRuleCode() => $checkedPetAgeValues]],
//                                        'aggs'   => [
//                                            $petSizeFilter->getFilterCode() => [
//                                                'terms' => ['field' => $petSizeFilter->getRuleCode(), 'size' => 9999],
//                                            ],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//                    ],
//                ],
//            ],
//        ];
//
//        $actualQuery = new Query();
//        $this->searchService->getAggsHelper()->setAggs($actualQuery, $filterCollection);
//
//        $actualAggRule = [];
//
//        /** @var AbstractAggregation $aggregation */
//        foreach ($actualQuery->getParam('aggs') as $aggregation) {
//            $actualAggRule[$aggregation->getName()] = $aggregation->toArray();
//        }
//
//        $this->assertEquals(
//            $expectedAggRule,
//            $actualAggRule,
//            'AggRule matches',
//            0.0,
//            10,
//            true
//        );
//
//    }
//
//    /**
//     * @throws RuntimeException
//     */
//    public function testSortSelecting()
//    {
//        $currentRegionCode = $this->locationService->getCurrentRegionCode();
//
//        $rule = [
//            'offers.prices.PRICE' => [
//                'order'         => 'asc',
//                'mode'          => 'min',
//                'nested_path'   => 'offers.prices',
//                'nested_filter' => ['term' => ['offers.prices.REGION_ID' => $currentRegionCode]],
//            ],
//        ];
//        $expectedSorting = (new Sorting())->withValue('up-price')
//                                          ->withName('возрастанию цены')
//                                          ->withRule($rule)
//                                          ->withSelected(true);
//
//        $request = Request::create(
//            'http://4lapy.ru/bzzz/',
//            'GET',
//            ['sort' => 'up-price']
//        );
//
//        $actualSorting = $this->catalogService->getSelectedSorting($request);
//
//        $this->assertEquals($expectedSorting, $actualSorting, 'Sorting selected correctly');
//    }
//
//    public function testNavRule()
//    {
//        $expectedFrom = 140;
//        $expectedSize = 20;
//
//        $request = Request::create(
//            'http://4lapy.ru/bzzz/',
//            'GET',
//            [
//                'page'     => '8',
//                'pageSize' => '20',
//            ]
//        );
//
//        $actualNavRule = $this->catalogService->getNavigation($request);
//
//        $this->assertEquals($expectedFrom, $actualNavRule->getFrom(), 'Correct from');
//        $this->assertEquals($expectedSize, $actualNavRule->getSize(), 'Correct size');
//
//    }
//
//    /**
//     * @param $actualCategory
//     *
//     * @throws \PHPUnit_Framework_Exception
//     * @throws IblockNotFoundException
//     */
//    private function isRootCategory($actualCategory)
//    {
//        /** @var Category $actualCategory */
//        Assert::assertInstanceOf(Category::class, $actualCategory);
//        Assert::assertEquals(
//            Category::createRoot()->getId(),
//            $actualCategory->getId(),
//            'Excpected category must be of root type.'
//        );
//    }
}
