<?php

namespace FourPaws\CatalogBundle\Controller;

use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use FourPaws\CatalogBundle\Dto\SearchRequest;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\Search\Model\ProductSearchResult;
use FourPaws\Search\SearchService;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CatalogController
 * @package FourPaws\CatalogBundle\Controller
 * @Route("/catalog")
 */
class CatalogController extends Controller
{
    /** @noinspection MoreThanThreeArgumentsInspection
     *
     * @Route("/search/")
     *
     * @param Request                $request
     * @param SearchRequest          $searchRequest
     * @param SearchService          $searchService
     * @param ValidatorInterface     $validator
     * @param GoogleEcommerceService $ecommerceService
     *
     * @return Response
     *
     * @throws ApplicationCreateException
     * @throws Exception
     */
    public function searchAction(Request $request, SearchRequest $searchRequest, SearchService $searchService, ValidatorInterface $validator, GoogleEcommerceService $ecommerceService): Response
    {
        $result = null;

        if (!$validator->validate($searchRequest)->count()) {
            /** @var ProductSearchResult $result */
            $result = $searchService->searchProducts(
                $searchRequest->getCategory()->getFilters(),
                $searchRequest->getSorts()->getSelected(),
                $searchRequest->getNavigation(),
                $searchRequest->getSearchString()
            );
        }

        $categories = (new CategoryQuery())
            ->withFilterParameter('SECTION_ID', false)
            ->exec();

        $tpl = 'FourPawsCatalogBundle:Catalog:search.html.php';

        if ($request->query->get('partial') === 'Y') {
            $tpl = 'FourPawsCatalogBundle:Catalog:search.filter.container.html.php';
        }

        return $this->render($tpl, [
            'request' => $request,
            'productSearchResult' => $result,
            'catalogRequest' => $searchRequest,
            'categories' => $categories,
            'ecommerceService' => $ecommerceService,
        ]);
    }

    /**
     * @Route("/{path}/")
     *
     * @param RootCategoryRequest $rootCategoryRequest
     * @param Request $request
     *
     * @param SearchService $searchService
     * @return Response
     * @throws Exception
     * @throws RuntimeException
     */
    public function rootCategoryAction(RootCategoryRequest $rootCategoryRequest, Request $request, SearchService $searchService): Response
    {
        $result = $searchService->searchProducts(
            $rootCategoryRequest->getCategory()->getFilters(),
            $rootCategoryRequest->getSorts()->getSelected(),
            $rootCategoryRequest->getNavigation(),
            $rootCategoryRequest->getSearchString()
        );

        return $this->render(
            'FourPawsCatalogBundle:Catalog:rootCategory.html.php',
            [
                'rootCategoryRequest' => $rootCategoryRequest,
                'request' => $request,
                'result' => $result,
            ]
        );
    }

    /**
     * @Route("/{path}/", requirements={"path"="[^\.]+(?!\.html)$" })
     *
     * @param Request                $request
     * @param ChildCategoryRequest   $categoryRequest
     * @param SearchService          $searchService
     * @param GoogleEcommerceService $ecommerceService
     *
     * @return Response
     *
     * @throws ApplicationCreateException
     * @throws Exception
     */
    public function childCategoryAction(Request $request, ChildCategoryRequest $categoryRequest, SearchService $searchService, GoogleEcommerceService $ecommerceService): Response
    {
        $result = $searchService->searchProducts(
            $categoryRequest->getCategory()->getFilters(),
            $categoryRequest->getSorts()->getSelected(),
            $categoryRequest->getNavigation(),
            $categoryRequest->getSearchString()
        );

        $tpl = 'FourPawsCatalogBundle:Catalog:catalog.html.php';

        if ($request->query->get('partial') === 'Y') {
            $tpl = 'FourPawsCatalogBundle:Catalog:catalog.filter.container.html.php';
        }

        return $this->render($tpl, [
            'request' => $request,
            'productSearchResult' => $result,
            'catalogRequest' => $categoryRequest,
            'ecommerceService' => $ecommerceService,
        ]);
    }
}
