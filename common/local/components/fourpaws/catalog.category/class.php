<?php

namespace FourPaws\Components;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Bitrix\Iblock\Component\Tools;
use FourPaws\App\Application;
use FourPaws\Catalog\Exception\BrandNotFoundException;
use FourPaws\Catalog\Exception\CategoryNotFoundException;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Search\Model\Navigation;
use FourPaws\Search\SearchService;
use Symfony\Component\HttpFoundation\Request;

class CatalogCategory extends \CBitrixComponent
{
    /**
     * @var \FourPaws\Catalog\CatalogService
     */
    private $catalogService;

    /**
     * @var Request
     */
    private $symfonyRequest;

    /**
     * @var SearchService
     */
    private $searchService;

    public function __construct(\CBitrixComponent $component = null)
    {
        parent::__construct($component);

        $this->catalogService = Application::getInstance()->getContainer()->get('catalog.service');
        $this->searchService = Application::getInstance()->getContainer()->get('search.service');
        $this->symfonyRequest = Request::createFromGlobals();
    }

    public function executeComponent()
    {
        $result = $this->searchService->searchProducts(
            $this->catalogService->getFilters($this->getCategory()),
            $this->catalogService->getSelectedSorting($this->symfonyRequest),
            $this->catalogService->getNavigation($this->symfonyRequest),
            $this->catalogService->getSearchString($this->symfonyRequest)
        );

        dump($result);

        return;


        if (!$category) {
            Tools::process404('', true, true, true);
        }

        if ($this->startResultCache(false, [$category->getId(), $navigation->getPage(), $navigation->getPageSize()])) {
            parent::executeComponent();

            $this->catalogService->getSelectedSorting($this->symfonyRequest);

            $this->arResult['PRODUCTS'] = $this->processFilter();

            $this->includeComponentTemplate();
        }
    }

    /**
     * @throws \RuntimeException
     * @throws IblockNotFoundException
     * @throws \Exception
     * @throws BrandNotFoundException
     * @throws CategoryNotFoundException
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->catalogService->getCategory($this->symfonyRequest);
    }

    /**
     * @return Navigation
     */
    protected function getNavRule(): Navigation
    {
        return $this->catalogService->getNavigation($this->symfonyRequest);
    }

    protected function processFilter()
    {
        /**
         * @todo implement logic
         */

        return (new ProductQuery())
            ->withFilterParameter('SECTION_ID', $this->arParams['SECTION_ID'])
            ->withFilterParameter('INCLUDE_SUBSECTIONS', 'Y')
            ->withNav([
                'iNumPage'  => 1,
                'nPageSize' => 30,
            ])
            ->exec();
    }
}
