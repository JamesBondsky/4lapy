<?php

namespace FourPaws\CatalogBundle\Controller;

use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\CatalogBundle\Dto\CatalogBrandRequest;
use FourPaws\CatalogBundle\Exception\RuntimeException as CatalogRuntimeException;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\Search\SearchService;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

/**
 * Class BrandController
 *
 * @package FourPaws\CatalogBundle\Controller
 *
 * @Route("/brand")
 */
class BrandController extends Controller
{
    /**
     * @Route("/")
     *
     * @return Response
     */
    public function listAction(): Response
    {
        return $this->render('FourPawsCatalogBundle:Catalog:brand.list.html.php', []);
    }

    /**
     * @Route("/{brand}/")
     *
     * @param Request $request
     * @param CatalogBrandRequest $catalogBrandRequest
     * @param SearchService $searchService
     * @param GoogleEcommerceService $ecommerceService
     *
     * @return Response
     *
     * @throws UnexpectedValueException
     * @throws ServiceCircularReferenceException
     * @throws CatalogRuntimeException
     * @throws RuntimeException
     * @throws Exception
     * @throws ApplicationCreateException
     */
    public function detailAction(
        Request $request,
        CatalogBrandRequest $catalogBrandRequest,
        SearchService $searchService,
        GoogleEcommerceService $ecommerceService
    ): Response
    {
        $result = $searchService->searchProducts(
            $catalogBrandRequest->getCategory()->getFilters(),
            $catalogBrandRequest->getSorts()->getSelected(),
            $catalogBrandRequest->getNavigation(),
            $catalogBrandRequest->getSearchString()
        );

        return $this->render('FourPawsCatalogBundle:Catalog:brand.detail.html.php', [
            'request' => $request,
            'catalogRequest' => $catalogBrandRequest,
            'productSearchResult' => $result,
            'searchService' => $searchService,
            'ecommerceService' => $ecommerceService,
        ]);
    }
}
