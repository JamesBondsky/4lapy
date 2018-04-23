<?php

namespace FourPaws\CatalogBundle\AjaxController;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\BasketItem;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Model\Sorting;
use FourPaws\CatalogBundle\Dto\ProductListRequest;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Service\BasketService;
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
     * @var BasketService
     */
    protected $basketService;

    /**
     * ProductInfoController constructor.
     *
     * @param ValidatorInterface $validator
     * @param SearchService $searchService
     * @param BasketService $basketService
     */
    public function __construct(
        ValidatorInterface $validator,
        SearchService $searchService,
        BasketService $basketService
    ) {
        $this->validator = $validator;
        $this->basketService = $basketService;
        $this->searchService = $searchService;
    }

    /**
     * @Route("/", methods={"GET"})
     *
     * @global \CMain $APPLICATION
     *
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

        $cartItems = [];
        $basket = $this->basketService->getBasket();
        /** @var BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            $cartItems[(int)$basketItem->getProductId()] = $basketItem->getQuantity();
        }

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
                    $offer->setProduct($product); /* @todo костыль - в elastic не проставляется ссылка на товар у оффера */
                    if ($offerId && $offer->getId() === $offerId) {
                        $currentOffer = $offer;
                    }
                    $price = ceil($offer->getPrice());
                    $oldPrice = $offer->getOldPrice() ? ceil($offer->getOldPrice()) : $price;
                    $response['products'][$product->getId()][$offer->getId()] = [
                        'available' => $offer->isAvailable(),
                        'byRequest' => $offer->isByRequest(),
                        'pickup' => $product->isPickupAvailable() && !$product->isDeliveryAvailable(),
                        'price' => $price,
                        'oldPrice' => $oldPrice,
                        'inCart' => $cartItems[$offer->getId()] ?? 0
                    ];
                }
            }

            if ($currentOffer) {
                ob_start();
                $APPLICATION->IncludeComponent(
                    'fourpaws:catalog.product.delivery.info',
                    'detail',
                    [
                        'OFFER' => $currentOffer
                    ],
                    false,
                    ['HIDE_ICONS' => 'Y']
                );

                $response['deliveryHtml'] = ob_get_clean();
            }
        }
        return JsonSuccessResponse::createWithData('', $response);
    }
}
