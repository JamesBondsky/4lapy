<?php

namespace FourPaws\CatalogBundle\AjaxController;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Model\Sorting;
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
     *
     * @param ValidatorInterface $validator
     * @param SearchService $searchService
     */
    public function __construct(ValidatorInterface $validator, SearchService $searchService)
    {
        $this->validator = $validator;
        $this->searchService = $searchService;
    }

    /**
     * @Route("/", methods={"GET"})
     *
     * @global \CMain $APPLICATION
     * @param Request $request
     * @param ProductListRequest $productListRequest
     *
     * @return JsonResponse
     * @throws ArgumentException
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ApplicationCreateException
     */
    public function infoAction(Request $request, ProductListRequest $productListRequest): JsonResponse
    {
        global $APPLICATION;

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
                        'available' => !$offer->getStocks()->isEmpty(),
                        'byRequest' => $offer->isByRequest(),
                        'pickup' => $product->isPickupAvailable(),
                        'delivery' => $product->isDeliveryAvailable(),
                        'price' => $offer->getPrice(),
                        'oldPrice' => $offer->getOldPrice() ?: $offer->getPrice()
                    ];
                }
            }

            if ($currentOffer) {
                ob_start();
                $deliveries = $APPLICATION->IncludeComponent(
                    'fourpaws:catalog.product.delivery.info',
                    'detail',
                    [
                        'OFFER' => $currentOffer
                    ],
                    false,
                    ['HIDE_ICONS' => 'Y']
                );

                $response['deliveryHtml'] = ob_get_clean();
                $response['products'][$product->getId()][$currentOffer->getId()]['available'] = !empty($deliveries);
            }
        }
        return JsonSuccessResponse::createWithData('', $response);
    }
}
