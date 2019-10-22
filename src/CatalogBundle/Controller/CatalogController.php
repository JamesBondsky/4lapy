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
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\EcommerceBundle\Service\DataLayerService;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\EcommerceBundle\Service\RetailRocketService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\Search\Helper\IndexHelper;
use FourPaws\Search\Model\ProductSearchResult;
use FourPaws\Search\SearchService;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

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
     * @var RetailRocketService
     */
    private $retailRocketService;
    /**
     * @var DataLayerService
     */
    private $dataLayerService;
    /**
     * @var LocationService;
     */
    private $locationService;
    /**
     * @var DeliveryService;
     */
    private $deliveryService;

    /**
     * CatalogController constructor.
     *
     * @param SearchService          $searchService
     * @param ValidatorInterface     $validator
     * @param GoogleEcommerceService $ecommerceService
     * @param CatalogLandingService  $landingService
     * @param RetailRocketService    $retailRocketService
     * @param DataLayerService       $dataLayerService
     * @param LocationService        $locationService
     * @param DeliveryService        $deliveryService
     */
    public function __construct(
        SearchService $searchService,
        ValidatorInterface $validator,
        GoogleEcommerceService $ecommerceService,
        CatalogLandingService $landingService,
        RetailRocketService $retailRocketService,
        DataLayerService $dataLayerService,
        LocationService $locationService,
        DeliveryService $deliveryService
    )
    {
        $this->searchService = $searchService;
        $this->validator = $validator;
        $this->ecommerceService = $ecommerceService;
        $this->retailRocketService = $retailRocketService;
        $this->landingService = $landingService;
        $this->dataLayerService = $dataLayerService;
        $this->locationService = $locationService;
        $this->deliveryService = $deliveryService;
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

        //костыль для заказчика
        $searchString = mb_strtolower($searchRequest->getSearchString());

        $searchString = IndexHelper::getAlias($searchString);

        if (!$this->validator->validate($searchRequest)
                             ->count()) {
            /** @var ProductSearchResult $result */
            $result = $this->searchService->searchProducts(
                $searchRequest->getCategory()->getFilters(),
                $searchRequest->getSorts()->getSelected(),
                $searchRequest->getNavigation(),
                $searchString
            );
        }

        $categories = (new CategoryQuery())
            ->withFilterParameter('SECTION_ID', false)
            ->exec();

        $retailRocketViewScript = $searchRequest->getCategory()->getId()
            ? \sprintf(
                '<script>%s</script>',
                $this->retailRocketService->renderCategoryView($searchRequest->getCategory()->getId())
            )
            : '';

        $tpl = 'FourPawsCatalogBundle:Catalog:search.html.php';

        if ($request->query->get('partial') === 'Y') {
            $tpl = 'FourPawsCatalogBundle:Catalog:search.filter.container.html.php';
        }

        return $this->render($tpl, [
            'request'                => $request,
            'productSearchResult'    => $result,
            'catalogRequest'         => $searchRequest,
            'categories'             => $categories,
            'ecommerceService'       => $this->ecommerceService,
            'dataLayerService'       => $this->dataLayerService,
            'retailRocketViewScript' => $retailRocketViewScript
        ]);
    }

    /**
     * @Route(
     *      "/{path}/",
     *      condition="request.get('landing', null) !== null",
     *      name="category.landing"
     * )
     *
     * @param RootCategoryRequest  $rootCategoryRequest
     * @param ChildCategoryRequest $categoryRequest
     * @param Request              $request
     *
     * @return Response
     */
    public function categoryLandingAction(RootCategoryRequest $rootCategoryRequest, ChildCategoryRequest $categoryRequest, Request $request): Response
    {
        $categoryRequest->setCategory($rootCategoryRequest->getLanding()->setActiveLandingCategory(true));
        $categoryRequest->setCurrentPath($rootCategoryRequest->getLanding()->getSectionPageUrl());

        return $this->forward('FourPawsCatalogBundle:Catalog:childCategory', \compact('request', 'categoryRequest'));
    }

    /**
     * @todo место для вашего Middleware, глубокоуважаемые
     *
     * @param RootCategoryRequest $rootCategoryRequest
     * @param Request             $request
     *
     * @return Response
     * @Route("/{path}/")
     *
     */
    public function filterSetAction(RootCategoryRequest $rootCategoryRequest, Request $request): Response
    {
        if ($rootCategoryRequest->getFilterSetId()) {
            $fSetRequest = Request::create(
                $request->getUriForPath($rootCategoryRequest->getFilterSetTarget())
            );
            $fSetRequest->request->set('filterset', $rootCategoryRequest->getFilterSetId());

            if ($request->query->get('partial') === 'Y') {
                $fSetRequest->query->replace($request->query->all());
            }

            return $this->get('http_kernel')->handle($fSetRequest);
        }

        return $this->forward('FourPawsCatalogBundle:Catalog:rootCategory', \compact('rootCategoryRequest', 'request'));
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
            $rootCategoryRequest->getCategory()->getFilters(),
            $rootCategoryRequest->getSorts()->getSelected(),
            $rootCategoryRequest->getNavigation(),
            $rootCategoryRequest->getSearchString()
        );

        $retailRocketViewScript = $rootCategoryRequest->getCategory()->getId()
            ? \sprintf(
                '<script>%s</script>',
                $this->retailRocketService->renderCategoryView($rootCategoryRequest->getCategory()->getId())
            )
            : '';

        return $this->render('FourPawsCatalogBundle:Catalog:rootCategory.html.php', \compact('rootCategoryRequest', 'request', 'result', 'retailRocketViewScript'));
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
        $category = $categoryRequest->getCategory();

        $result = $this->searchService->searchProducts(
            $category->getFilters(),
            $categoryRequest->getSorts()->getSelected(),
            $categoryRequest->getNavigation(),
            $categoryRequest->getSearchString()
        );

        if ($this->landingService->isLanding($request) && $category->isActiveLandingCategory()) {
            foreach ($categoryRequest->getLandingCollection() as $landing) {
                if ($category->getId() === $landing->getId()) {
                    $landing->setActiveLandingCategory(true);

                    break;
                }
            }
        }

        // для блока "бесплатная примерка" определяем доступную доставку, чтобы в блоке вывести цены
        $availableDelivery = null;
        $locationCode = $this->locationService->getCurrentLocation();
        $deliveries = $this->deliveryService->getByLocation($locationCode);

        if ($deliveries && !empty($deliveries)) {
            foreach ($this->deliveryService->getByLocation($locationCode) as $delivery) {
                if ($this->deliveryService->isDelivery($delivery)) {
                    $availableDelivery = $delivery;
                    break;
                }
            }
        }

        try {
            $productWithMinPrice = $this->searchService->searchOneWithMinPrice($category->getFilters());
        } catch (Throwable $e) {
            $productWithMinPrice = false;
        }

        $retailRocketViewScript = \sprintf(
            '<script>%s</script>',
            $this->retailRocketService->renderCategoryView($category->getId())
        );

        $tpl = 'FourPawsCatalogBundle:Catalog:catalog.html.php';

        if ($request->query->get('partial') === 'Y') {
            $tpl = 'FourPawsCatalogBundle:Catalog:catalog.filter.container.html.php';
        }

        /*
         * todo использовать cookie битрикса или symfony
         * битрикс еще не подключен, а в конце шаблонов стоит die();, следовательно с помощью symfony выставить cookie не получится
         */
        setcookie('clear_pet_size_filter', null, -1, '/');

        return $this->render($tpl, [
            'productSearchResult'    => $result,
            'catalogRequest'         => $categoryRequest,
            'ecommerceService'       => $this->ecommerceService,
            'request'                => $request,
            'landingService'         => $this->landingService,
            'dataLayerService'       => $this->dataLayerService,
            'retailRocketViewScript' => $retailRocketViewScript,
            'productWithMinPrice'    => $productWithMinPrice,
            'availableDelivery'      => $availableDelivery,
        ]);
    }
}
