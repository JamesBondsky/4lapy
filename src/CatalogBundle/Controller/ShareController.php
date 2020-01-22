<?php

namespace FourPaws\CatalogBundle\Controller;

use Bitrix\Main\ArgumentException;
use Elastica\Exception\InvalidException;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\CatalogBundle\Dto\CatalogShareRequest;
use FourPaws\CatalogBundle\Service\FilterHelper;
use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\CatalogBundle\Service\SortService;
use FourPaws\MobileApiBundle\Services\Api\ProductService;
use FourPaws\CatalogBundle\Exception\RuntimeException as CatalogRuntimeException;
use FourPaws\EcommerceBundle\Service\DataLayerService;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\Search\Model\Navigation;
use FourPaws\Search\SearchService;
use InvalidArgumentException;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

/**
 * Class ShareController
 *
 * @package FourPaws\CatalogBundle\Controller
 *
 * @Route("/shares")
 */
class ShareController extends Controller
{
    /**
     * @var SearchService
     */
    private $searchService;
    
    /**
     * @var ProductService
     */
    private $productService;
    
    /**
     * @var FilterHelper
     */
    private $filterHelper;
    
    /** @var SortService */
    private $sortService;
    
    
    /**
     * CatalogController constructor.
     *
     * @param SearchService  $searchService
     * @param ProductService $productService
     * @param FilterHelper   $filterHelper
     * @param SortService   $sortService
     */
    public function __construct(
        SearchService $searchService,
        ProductService $productService,
        FilterHelper $filterHelper,
        SortService $sortService
    ) {
        $this->searchService  = $searchService;
        $this->productService = $productService;
        $this->filterHelper   = $filterHelper;
        $this->sortService   = $sortService;
    }
    
    /**
     * @Route("/{share}/")
     *
     * @param Request                $request
     * @param CatalogShareRequest    $catalogShareRequest
     *
     * @return Response
     *
     * @throws InvalidArgumentException
     * @throws InvalidException
     * @throws ArgumentException
     * @throws UnexpectedValueException
     * @throws ServiceCircularReferenceException
     * @throws CatalogRuntimeException
     * @throws RuntimeException
     * @throws Exception
     * @throws ApplicationCreateException
     */
    public function detailAction(Request $request, CatalogShareRequest $catalogShareRequest): Response
    {
        $searchQuery = $this->productService->getProductXmlIdsByShareId($catalogShareRequest->getShare()->getId());
        
        $category = new \FourPaws\Catalog\Model\Category();
        $this->filterHelper->initCategoryFilters($category, $request);
        $filters = $category->getFilters();
    
        $sort = $this->sortService->getSorts('popular', strlen($searchQuery) > 0)->getSelected();
    
        $nav = (new Navigation())
            ->withPage(1)
            ->withPageSize(10);
        
        // $cacheArr = [
        //     'sorts'         => $sort,
        //     'category_code' => $catalogShareRequest->getShare()->getCode(),
        //     'navigation'    => $nav,
        // ];
        
        // $cacheKey = md5(implode('_', $cacheArr));
        //
        // $cache = new FilesystemCache('', 3600 * 2);
        //
        // if ($cache->has($cacheKey)) {
        //     $result = $cache->get($cacheKey);
        // } else {
            $result = $this->searchService->searchProducts(
                $filters,
                $sort,
                $nav,
                $searchQuery
            );
            // $cache->set($cacheKey, $result);
        // }
        
        return $this->render('FourPawsCatalogBundle:Catalog:share.detail.html.php', [
            'request'                => $request,
            'productSearchResult'    => $result,
            'catalogShareRequest'    => $catalogShareRequest,
        ]);
    }
}
