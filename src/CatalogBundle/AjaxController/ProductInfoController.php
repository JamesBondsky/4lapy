<?php

namespace FourPaws\CatalogBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Model\Sorting;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\CatalogBundle\Dto\ProductListRequest;
use FourPaws\Search\Model\Navigation;
use FourPaws\Search\Model\ProductSearchResult;
use FourPaws\Search\SearchService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ProductInfoController
 *
 * @package FourPaws\CatalogBundle\Controller
 * @Route("/product-info")
 */
class ProductInfoController extends Controller
{
    const MAX_PRODUCTS_PER_REQUEST = 30;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var SearchService
     */
    protected $searchService;

    /**
     * ProductInfoController constructor.
     * @param ValidatorInterface $validator
     * @param SearchService $searchService
     */
    public function __construct(ValidatorInterface $validator, SearchService $searchService)
    {
        $this->validator = $validator;
        $this->searchService = $searchService;
    }

    /**
     * @Route("/detail/", methods={"GET"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function detailAction(Request $request): JsonResponse
    {
        /** @todo доделать ответ */
        $offerId = $request->query->get('offerId', 0);

        /** @var Offer $offer */
        $offer = (new OfferQuery())->withFilterParameter('ID', $offerId)->exec()->first();
        if (!$offer) {
            return JsonErrorResponse::create('Товар не найден');
        }

        /**
        return JsonSuccessResponse::createWithData('', [
            'deliveryHtml' => $this->render(
                'FourPawsCatalogBundle:Catalog:ajax.productDetail.info.html.php',
                ['offer' => $offer]
            ),
            'variantsHtml' => $this->render(
                'FourPawsCatalogBundle:Catalog:ajax.productDetail.info.html.php',

                'FourPawsCatalogBundle:Catalog:productDetail.variants.html.php',
                ['product' => $offer->getProduct()]),
            'isAvailable' => !$offer->getStocks()->isEmpty()
        ]);
         **/
    }

    /**
     * @Route("/", methods={"GET"})
     *
     * @param ProductListRequest $productListRequest
     * @return JsonResponse
     */
    public function snippetsAction(ProductListRequest $productListRequest): JsonResponse
    {
        $result = [];

        if (!$this->validator->validate($productListRequest)->count()) {
            /** @var ProductSearchResult $result */
            $searchResult = $this->searchService->searchProducts(
                $productListRequest->getFilters(),
                new Sorting(),
                new Navigation()
            );

            /** @var Product $product */
            foreach ($searchResult->getProductCollection() as $product) {
                /** @var Offer $offer */
                foreach ($product->getOffers() as $offer) {
                    $result[$product->getId()][$offer->getId()] = [
                        'available' => !$offer->getStocks()->isEmpty()
                    ];
                }
            }
        }
        return JsonSuccessResponse::createWithData('', [
            'products' => $result
        ]);
    }
}
