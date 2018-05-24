<?php

namespace FourPaws\CatalogBundle\Controller;

use Exception;
use FourPaws\CatalogBundle\Dto\CatalogBrandRequest;
use FourPaws\CatalogBundle\Exception\RuntimeException;
use FourPaws\Search\SearchService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BrandController
 * @package FourPaws\CatalogBundle\Controller
 * @Route("/brand")
 */
class BrandController extends Controller
{
    /**
     * @Route("/{brand}/")
     *
     * @param Request             $request
     * @param CatalogBrandRequest $catalogBrandRequest
     * @param SearchService       $searchService
     *
     * @return Response
     * @throws ServiceNotFoundException
     * @throws Exception
     * @throws RuntimeException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function detailAction(
        Request $request,
        CatalogBrandRequest $catalogBrandRequest,
        SearchService $searchService
    ): Response {
        $result = $searchService->searchProducts(
            $catalogBrandRequest->getCategory()->getFilters(),
            $catalogBrandRequest->getSorts()->getSelected(),
            $catalogBrandRequest->getNavigation(),
            $catalogBrandRequest->getSearchString()
        );

        return $this->render('FourPawsCatalogBundle:Catalog:brand.detail.html.php', [
            'request'             => $request,
            'catalogRequest'      => $catalogBrandRequest,
            'productSearchResult' => $result,
            'searchService' => $searchService,
        ]);
    }
}
