<?php

namespace FourPaws\Test\Tests\Catalog;

use FourPaws\App\Application;
use FourPaws\Catalog\CatalogService;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\Search\SearchService;
use FourPaws\Test\Tests\TestBase;
use Symfony\Component\HttpFoundation\Request;

class SearchTest extends TestBase
{
    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * @var CatalogService
     */
    protected $catalogService;

    /**
     * CatalogTest constructor.
     *
     * @param null $name
     * @param array $data
     * @param string $dataName
     *
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->catalogService = Application::getInstance()->getContainer()->get('catalog.service');
        $this->searchService = Application::getInstance()->getContainer()->get('search.service');
    }

    /**
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Exception
     * @throws \PHPUnit_Framework_Exception
     * @throws \RuntimeException
     */
    public function testProductSearch()
    {
        $expectedCategory = (new CategoryQuery)->withFilter(['=CODE' => 'unsorted'])->exec()->current();

        $this->assertInstanceOf(Category::class, $expectedCategory);

        $parameters = [
            //TODO Попробовать позже сортировку по цене
            'sort'     => 'popular',
            'page'     => 2,
            'pageSize' => 40,

            'Brand'   => 'proplan,royal-kanin',

            //Эдалт
            'PetAge'  => '20996e50d5458b12e04108ce66b2d108',

            //Мелкий
            'PetSize' => 'd45c1a3e47c180b16be0e29dc87e8281',

        ];

        $request = Request::create(
            'http://4lapy.ru' . $expectedCategory->getSectionPageUrl(),
            'GET',
            $parameters
        );

        /**
         * 1 По запросу определить категорию с настроенными фильтрами.
         */
        $actualCategory = $this->catalogService->getCategory($request);

        /**
         * 2 По запросу определить сортировку
         */
        $actualSorting = $this->catalogService->getSelectedSorting($request);

        /**
         * 3 По запросу определить постраничную навигацию
         */
        $actualNavigation = $this->catalogService->getNavigation($request);

        /**
         * 4 По запросу определить наличие строки для поиска
         */
        $actualSearchString = $this->catalogService->getSearchString($request);

        /**
         * 5 Запросить поиск товаров
         */
        $productSearchResult = $this->searchService->searchProducts(
            $actualCategory->getFilters(),
            $actualSorting,
            $actualNavigation,
            $actualSearchString
        );

        /**
         * 6 Вывести на странице фильтры, т.к. они теперь не только выбраны,
         * но и схлопнуты на основе аггрегаций из Elasticsearch
         */
        $actualFilterCollection = $actualCategory->getFilters();

        /**
         * 7 Получить коллекцию товаров
         */
        $productCollection = $productSearchResult->getProductCollection();

    }
}
