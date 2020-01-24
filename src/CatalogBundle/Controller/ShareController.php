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
use FourPaws\CatalogBundle\Service\ShareService;

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
    
    /** @var ShareService */
    private $shareService;
    
    
    /**
     * CatalogController constructor.
     *
     * @param SearchService  $searchService
     * @param ProductService $productService
     * @param FilterHelper   $filterHelper
     * @param SortService    $sortService
     * @param ShareService   $shareService
     */
    public function __construct(
        SearchService $searchService,
        ProductService $productService,
        FilterHelper $filterHelper,
        SortService $sortService,
        ShareService $shareService
    ) {
        $this->searchService  = $searchService;
        $this->productService = $productService;
        $this->filterHelper   = $filterHelper;
        $this->sortService    = $sortService;
        $this->shareService   = $shareService;
    }
    
    /**
     * @Route("/{share}/")
     *
     * @param Request                $request
     * @param CatalogShareRequest    $catalogShareRequest
     * @param GoogleEcommerceService $ecommerceService
     * @param DataLayerService       $dataLayerService
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
    public function detailAction(
        Request $request,
        CatalogShareRequest $catalogShareRequest,
        GoogleEcommerceService $ecommerceService,
        DataLayerService $dataLayerService
    ): Response {
        $arParams = $this->shareService->getParams($catalogShareRequest->getShare()->getId(), $catalogShareRequest->getShare());
        
        $searchQuery = $this->productService->getProductXmlIdsByShareId($catalogShareRequest->getShare()->getId());
        $sort = $catalogShareRequest->getSorts()->getSelected();
        
        $cacheArr = [
            'sorts'         => (array)$sort,
            'category_code' => $catalogShareRequest->getShare()->getCode(),
            'navigation'    => (array)$catalogShareRequest->getNavigation(),
        ];
        
        $cacheKey = md5(implode('_', $cacheArr));
        
        $cache = new FilesystemCache('', 3600 * 2);
        
        if ($cache->has($cacheKey)) {
            $result = $cache->get($cacheKey);
        } else {
            $result = $this->searchService->searchProducts(
                $catalogShareRequest->getCategory()->getFilters(),
                $sort,
                $catalogShareRequest->getNavigation(),
                $searchQuery
            );
            $cache->set($cacheKey, $result);
        }
        
        return $this->render('FourPawsCatalogBundle:Catalog:share.detail.html.php', [
            'request'             => $request,
            'productSearchResult' => $result,
            'searchService'       => $this->sortService,
            'catalogRequest'      => $catalogShareRequest,
            'ecommerceService'    => $ecommerceService,
            'dataLayerService'    => $dataLayerService,
            'arParams'            => $arParams,
        ]);
    }
}
