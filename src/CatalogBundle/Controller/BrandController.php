<?php

namespace FourPaws\CatalogBundle\Controller;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\CatalogBundle\Dto\CatalogBrandRequest;
use FourPaws\CatalogBundle\Exception\RuntimeException;
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
     * @param Request             $request
     * @param CatalogBrandRequest $catalogBrandRequest
     *
     * @return Response
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function detailAction(Request $request, CatalogBrandRequest $catalogBrandRequest) : Response
    {
        $result = App::getInstance()->getContainer()->get('search.service')->searchProducts(
            $catalogBrandRequest->getCategory()->getFilters(),
            $catalogBrandRequest->getSorts()->getSelected(),
            $catalogBrandRequest->getNavigation(),
            $catalogBrandRequest->getSearchString()
        );
        
        return $this->render('FourPawsCatalogBundle:Catalog:brand.detail.html.php', [
            'request'             => $request,
            'catalogRequest' => $catalogBrandRequest,
            'productSearchResult' => $result
        ]);
    }
}
