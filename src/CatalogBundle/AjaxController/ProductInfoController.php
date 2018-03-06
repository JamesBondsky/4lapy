<?php

namespace FourPaws\CatalogBundle\AjaxController;

use FourPaws\App\Application;
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
use Symfony\Component\Templating\DelegatingEngine;
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
     * @var DelegatingEngine
     */
    protected $renderer;

    /**
     * ProductInfoController constructor.
     * @param ValidatorInterface $validator
     * @param SearchService $searchService
     */
    public function __construct(ValidatorInterface $validator, SearchService $searchService)
    {
        $this->validator = $validator;
        $this->searchService = $searchService;

        $container = Application::getInstance()->getContainer();
        if ($container->has('templating')) {
            $this->renderer = $container->get('templating');
        } elseif ($container->has('twig')) {
            $this->renderer = $container->get('twig');
        } else {
            throw new \LogicException('You can not use the "render" method if the Templating Component or the Twig Bundle are not available.');
        }
    }

    /**
     * @Route("/", methods={"GET"})
     *
     * @param ProductListRequest $productListRequest
     * @return JsonResponse
     */
    public function infoAction(Request $request, ProductListRequest $productListRequest): JsonResponse
    {
        $response = [
            'products' => []
        ];

        $offerId = (int)$request->query->get('offer', 0);
        $currentOffer = null;

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
                    if ($offerId && $offer->getId() === $offerId) {
                        $currentOffer = $offer;
                    }
                    $response['products'][$product->getId()][$offer->getId()] = [
                        'available' => !$offer->getStocks()->isEmpty()
                    ];
                }
            }

            if ($currentOffer) {
                $response['deliveryHtml'] = $this->renderer->render(
                    'FourPawsCatalogBundle:Catalog:ajax.productDetail.info.html.php',
                    ['offer' => $offer]
                );
            }
        }
        return JsonSuccessResponse::createWithData('', $response);
    }
}
