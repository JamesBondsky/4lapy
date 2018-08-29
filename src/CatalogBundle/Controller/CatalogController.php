<?php

namespace FourPaws\CatalogBundle\Controller;

use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use FourPaws\CatalogBundle\Dto\SearchRequest;
use FourPaws\CatalogBundle\Exception\RuntimeException as CatalogRuntimeException;
use FourPaws\CatalogBundle\Service\CatalogLandingService;
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
 *
 * @package FourPaws\CatalogBundle\Controller
 *
 * @Route("/catalog")
 */
class CatalogController extends Controller
{
    /**
     * @var SearchService
     */
    private $searchService;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var GoogleEcommerceService
     */
    private $ecommerceService;
    /**
     * @var CatalogLandingService
     */
    private $landingService;

    /**
     * CatalogController constructor.
     *
     * @param SearchService          $searchService
     * @param ValidatorInterface     $validator
     * @param GoogleEcommerceService $ecommerceService
     * @param CatalogLandingService  $landingService
     */
    public function __construct(SearchService $searchService, ValidatorInterface $validator, GoogleEcommerceService $ecommerceService, CatalogLandingService $landingService)
    {
        $this->searchService = $searchService;
        $this->validator = $validator;
        $this->ecommerceService = $ecommerceService;
        $this->landingService = $landingService;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     *
     * @Route("/search/")
     *
     * @param Request       $request
     * @param SearchRequest $searchRequest
     *
     * @return Response
     *
     * @throws CatalogRuntimeException
     * @throws RuntimeException
     * @throws ApplicationCreateException
     * @throws Exception
     */
    public function searchAction(Request $request, SearchRequest $searchRequest): Response
    {
        $result = null;

        if (!$this->validator->validate($searchRequest)
            ->count()) {
            /** @var ProductSearchResult $result */
            $result = $this->searchService->searchProducts(
                $searchRequest->getCategory()
                    ->getFilters(),
                $searchRequest->getSorts()
                    ->getSelected(),
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
            'request'             => $request,
            'productSearchResult' => $result,
            'catalogRequest'      => $searchRequest,
            'categories'          => $categories,
            'ecommerceService'    => $this->ecommerceService,
        ]);
    }

    /**
     * @Route(
     *      "/{path}/",
     *      condition="request.get('landing', null) !== null",
     *      name="category.landing"
     * )
     *
     * @param RootCategoryRequest $rootCategoryRequest
     *
     * @return Response
     */
    public function categoryLandingAction(RootCategoryRequest $rootCategoryRequest): Response
    {
        return $this->redirect($this->landingService->getLandingDefaultPage($rootCategoryRequest), 301);
    }

    /**
     * @Route("/{path}/")
     *
     * @param RootCategoryRequest $rootCategoryRequest
     * @param Request             $request
     *
     * @return Response
     *
     * @throws CatalogRuntimeException
     * @throws ApplicationCreateException
     * @throws Exception
     * @throws RuntimeException
     */
    public function rootCategoryAction(RootCategoryRequest $rootCategoryRequest, Request $request): Response
    {
        $result = $this->searchService->searchProducts(
            $rootCategoryRequest->getCategory()
                ->getFilters(),
            $rootCategoryRequest->getSorts()
                ->getSelected(),
            $rootCategoryRequest->getNavigation(),
            $rootCategoryRequest->getSearchString()
        );

        return $this->render(
            'FourPawsCatalogBundle:Catalog:rootCategory.html.php',
            [
                'rootCategoryRequest' => $rootCategoryRequest,
                'request'             => $request,
                'result'              => $result,
            ]
        );
    }

    /**
     * @Route("/{path}/", requirements={"path"="[^\.]+(?!\.html)$" })
     *
     * @param Request              $request
     * @param ChildCategoryRequest $categoryRequest
     *
     * @return Response
     *
     * @throws RuntimeException
     * @throws CatalogRuntimeException
     * @throws ApplicationCreateException
     * @throws Exception
     */
    public function childCategoryAction(Request $request, ChildCategoryRequest $categoryRequest): Response
    {
        $result = $this->searchService->searchProducts(
            $categoryRequest->getCategory()
                ->getFilters(),
            $categoryRequest->getSorts()
                ->getSelected(),
            $categoryRequest->getNavigation(),
            $categoryRequest->getSearchString()
        );

        $tpl = 'FourPawsCatalogBundle:Catalog:catalog.html.php';

        if ($request->query->get('partial') === 'Y') {
            $tpl = 'FourPawsCatalogBundle:Catalog:catalog.filter.container.html.php';
        }

        return $this->render($tpl, [
            'request'             => $request,
            'productSearchResult' => $result,
            'catalogRequest'      => $categoryRequest,
            'ecommerceService'    => $this->ecommerceService,
        ]);
    }
}
