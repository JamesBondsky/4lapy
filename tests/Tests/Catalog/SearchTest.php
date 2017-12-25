<?php

namespace FourPaws\Test\Tests\Catalog;

use FourPaws\Test\Tests\TestBase;

class SearchTest extends TestBase
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
//     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
//     * @throws \Exception
//     * @throws \PHPUnit_Framework_Exception
//     * @throws \RuntimeException
//     */
//    public function testProductSearch()
//    {
//        $currentRegionCode = $this->locationService->getCurrentRegionCode();
//
//        $expectedCategory = (new CategoryQuery)->withFilter(['=CODE' => 'unsorted'])->exec()->current();
//
//        $this->assertInstanceOf(Category::class, $expectedCategory);
//
//        $parameters = [
//            'sort'     => 'up-price',
//            'page'     => 1,
//            'pageSize' => 20,
//
//            'Brand'   => 'proplan,royal-kanin',
//
//            //Эдалт
//            'PetAge'  => '20996e50d5458b12e04108ce66b2d108',
//
//            //Мелкий
//            'PetSize' => 'd45c1a3e47c180b16be0e29dc87e8281',
//
//            // //Унисекс
//            // 'PetGender' => '6cdd5db762dd7664f9fc769ee39538b2',
//
//            'PriceFrom' => 100,
//            'PriceTo'   => 9000,
//
//        ];
//
//        $request = Request::create(
//            'http://4lapy.ru' . $expectedCategory->getSectionPageUrl(),
//            'GET',
//            $parameters
//        );
//
//        /**
//         * 1 По запросу определить категорию с настроенными фильтрами.
//         */
//        $actualCategory = $this->catalogService->getCategory($request);
//
//        /**
//         * 2 По запросу определить сортировку
//         */
//        $actualSorting = $this->catalogService->getSelectedSorting($request);
//
//        /**
//         * 3 По запросу определить постраничную навигацию
//         */
//        $actualNavigation = $this->catalogService->getNavigation($request);
//
//        /**
//         * 4 По запросу определить наличие строки для поиска
//         */
//        $actualSearchString = $this->catalogService->getSearchString($request);
//
//        /**
//         * 5 Запросить поиск товаров
//         */
//        $productSearchResult = $this->searchService->searchProducts(
//            $actualCategory->getFilters(),
//            $actualSorting,
//            $actualNavigation,
//            $actualSearchString
//        );
//
//        /**
//         * 6 Вывести на странице фильтры, т.к. они теперь не только выбраны,
//         * но и схлопнуты на основе аггрегаций из Elasticsearch
//         */
//        /** @noinspection PhpUnusedLocalVariableInspection */
//        $actualFilterCollection = $actualCategory->getFilters();
//
//        /**
//         * 7 Получить коллекцию товаров
//         */
//        $productCollection = $productSearchResult->getProductCollection();
//
//        $lastPrice = -1;
//        /** @var Product $product */
//        foreach ($productCollection as $product) {
//            /** @var Offer $offer */
//            foreach ($product->getOffers() as $offer) {
//
//                $this->assertGreaterThanOrEqual(
//                    $lastPrice,
//                    $offer->getPrice($currentRegionCode)->getPrice(),
//                    'Price sort is asc.'
//                );
//
//            }
//        }
//    }
}
