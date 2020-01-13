<?php

namespace FourPaws\CatalogBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use CMain;
use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\BitrixOrm\Collection\ResizeImageCollection;
use FourPaws\BitrixOrm\Model\Share;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\CatalogBundle\Dto\CatalogBrandFilterRequest;
use FourPaws\CatalogBundle\Dto\ChildCategoryFilterRequest;
use FourPaws\CatalogBundle\Dto\ProductListRequest;
use FourPaws\CatalogBundle\Dto\SearchRequest;
use FourPaws\Helpers\WordHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SapBundle\Repository\BasketRulesRepository;
use FourPaws\Search\Model\ProductSearchResult;
use FourPaws\Search\SearchService;
use FourPaws\StoreBundle\Exception\NotFoundException;
use Psr\Log\LoggerAwareInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use WebArch\BitrixCache\BitrixCache;

/**
 * Class ProductInfoController
 *
 * @package FourPaws\CatalogBundle\Controller
 * @Route("/product-info")
 */
class ProductInfoController extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const MAX_PRODUCTS_PER_REQUEST = 30;

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
     * @var LocationService
     */
    protected $locationService;

    /**
     * @var BasketRulesRepository
     */
    protected $basketRulesRepository;

    /**
     * ProductInfoController constructor.
     *
     * @param ValidatorInterface    $validator
     * @param SearchService         $searchService
     * @param LocationService       $locationService
     * @param BasketService         $basketService
     * @param BasketRulesRepository $basketRulesRepository
     */
    public function __construct(
        ValidatorInterface $validator,
        SearchService $searchService,
        LocationService $locationService,
        BasketService $basketService,
        BasketRulesRepository $basketRulesRepository
    ) {
        $this->validator = $validator;
        $this->searchService = $searchService;
        $this->locationService = $locationService;
        $this->basketService = $basketService;
        $this->basketRulesRepository = $basketRulesRepository;
    }

    /**
     * @todo переделать после получения конкретики от клиента
     *
     * @param Offer $offer
     *
     * @return array
     */
    public static function getGroupSets(Offer $offer): array
    {
        $result = [];
        if (
            $offer->isShare()
            &&
            ($sharesOfGroupSet = $offer->getShare()->filter(
                function (Share $e) {
                    return !empty($e->getPropertyJsonGroupSet());
                }
            ))
            &&
            $sharesOfGroupSet->count() > 0
        ) {
            // находим сначала акцию с двумя группами и запоминаем,
            // затем, если находим акцию с тремя - запоминаем и брейкаем, иначе выводим с двумя.
            $groupSet = [];
            $shareOfResultGroupSet = null;
            /** @var Share $share */
            foreach ($sharesOfGroupSet as $share) {
                $current = json_decode($share->getPropertyJsonGroupSet());
                // слотов 2 или 3
                if (\count($current) < 2 || \count($groupSet) > 3) {
                    continue;
                }
                if (empty($groupSet)) {
                    $groupSet = $current;
                    $shareOfResultGroupSet = $share;
                } elseif (\count($current) > \count($groupSet)) {
                    $groupSet = $current;
                    $shareOfResultGroupSet = $share;
                }
                if (\count($groupSet) > 2) {
                    break;
                }
            }
            self::sortGroupSet($offer->getId(), $groupSet);
            $result[] = [
                'share'    => $shareOfResultGroupSet,
                'groupSet' => $groupSet,
            ];
        }
        return $result;
    }

    /**
     *
     *
     * @param int   $offerId
     * @param array $groupSet
     *
     * @return bool
     */
    protected static function sortGroupSet(int $offerId, array &$groupSet): bool
    {
        return usort($groupSet, function (array $a, array $b) use ($offerId) {
            $result = 0;
            if (\in_array($offerId, $a, true)) {
                $result = -1;
            }
            if (\in_array($offerId, $b, true)) {
                if ($result === -1) {
                    //todo throw exception ?? товар поидее не может быть в двух разных группах.
                    $result = 0;
                } else {
                    $result = 1;
                }
            }
            return $result;
        });
    }

    /**
     * @Route("/", methods={"GET"})
     *
     * @param ProductListRequest $productListRequest
     *
     * @return JsonResponse
     *
     * @throws ApplicationCreateException
     * @throws Exception
     * @global CMain $APPLICATION
     */
    public function infoAction(ProductListRequest $productListRequest): JsonResponse
    {
        $validator = $this->validator;
        $getProductInfo = function ($product, $offer) {
            //$tempLogger = LoggerFactory::create('info', 'devProductInfo');
            //$tempLogger->info('getting productInfo. product: ' . $product->getId() . ' - ' . $offer->getId());
            return $this->getProductInfo($product, $offer);
        };

        $locationCode = $this->locationService->getCurrentLocation();

        $response = [
            'products' => [],
        ];
        //$devId = md5(serialize($productListRequest)) . '_' . $locationCode;
        //$tempLogger = LoggerFactory::create('info', 'devProductInfo');
        //$tempLogger->info('try to get cache: ' . $devId);

        $getProductListInfo = static function () use ($productListRequest, $response, $validator, $getProductInfo, $locationCode) {
            //$tempLogger = LoggerFactory::create('info', 'devProductInfo');
            //$tempLogger->info('generate new cache: ' . $devId);
            $currentOffer = null;

            if (!$validator->validate($productListRequest)->count()) {
                /** @var ProductSearchResult $result */
                /** для списка товаров дает небольой выйгрыш отдельное получение офферов*/
                $productIds = $productListRequest->getProductIds();
                /** исправляем проблему с сортировкой */
                sort($productIds, SORT_NUMERIC);
                $getProducts = function () use ($productIds) {
                    $productCollection = (new ProductQuery())->withFilter(['=ID' => $productIds])->exec();
                    /** @var Product $product */
                    $products = [];
                    if ($productCollection->count() === 1) {
                        $product = $productCollection->first();
                        $products[$product->getId()] = $product;
                    } else {
                        foreach ($productCollection as $product) {
                            $products[$product->getId()] = $product;
                        }
                    }
                    return $products;
                };

                $products = $getProducts();

                $offerCollection = (new OfferQuery())->withFilter([
                    '=PROPERTY_CML2_LINK' => $productIds,
                    'ACTIVE'              => 'Y',
                    '>CATALOG_PRICE_2'    => 0,
                ])->exec();

                /** @var Offer $offer */
                /** @var Product $product */
                /** добавляем офферы чтобы е было запроса по всем офферам */
                foreach ($offerCollection as $offer) {
                    $product = $products[$offer->getCml2Link()];
                    $product->addOffer($offer);
                    $offer->setProduct($product);
                }

                /** @var Product $product */
                foreach ($products as $product) {
                    $ratings = [];
                    $sortedOffers = $product->getOffersSorted();
                    foreach ($sortedOffers as $offer) {
                        $rating = 0;
                        if ($offer->isAvailable()) {
                            $rating++;
                            if (!$offer->isByRequest()) {
                                $rating++;
                            }
                        }
                        $ratings[$offer->getId()] = $rating;
                    }

                    /** @var Offer $activeOffer */
                    $activeOffer = $sortedOffers->first();
                    foreach ($sortedOffers as $offer) {
                        if ($ratings[$activeOffer->getId()] < $ratings[$offer->getId()]) {
                            $activeOffer = $offer;
                        }
                    }

                    foreach ($product->getOffers() as $offer) {
                        $cache = new FilesystemCache('', 1800);
                        $cacheKey = 'getProductInfo_' . $product->getId() . '_' . $offer->getId() . '_' . $locationCode;

                        if ($cache->has($cacheKey)) {
                            $responseItem = $cache->get($cacheKey);
                        } else {
                            $responseItem = $getProductInfo($product, $offer);
                            $cache->set($cacheKey, $responseItem);
                        }

//                        $responseItem = (new BitrixCache())
//                            ->withId('getProductInfo_' . $product->getId() . '_' . $offer->getId() . '_' . $locationCode)
//                            ->withTag('productInfo')
//                            ->withTime(1800) // 30 минут
//                            ->resultOf(static function () use ($product, $offer, $getProductInfo) {
//                                return $getProductInfo($product, $offer);
//                            });
                        //$responseItem = $getProductInfo($product, $offer);
                        //$tempLogger = LoggerFactory::create('info', 'devProductInfo');
                        //$tempLogger->info('product info: ', $responseItem);
                        //$responseItem['inCart'] = $cartItems[$offer->getId()] ?? 0;
                        $responseItem['active'] = $activeOffer->getId() === $offer->getId();
                        $response['products'][$product->getId()][$offer->getId()] = $responseItem;
                    }
                }
            }

            return $response;
        };

        $cache = new FilesystemCache('', 3600);
        $currentMethod = explode('::', __METHOD__)[1];
        $cacheKey = $currentMethod . '_' . md5(serialize($productListRequest)) . '_' . $locationCode;
//
        if ($cache->has($cacheKey)) {
            $response = $cache->get($cacheKey);
        } else {
            $response = $getProductListInfo();
            $cache->set($cacheKey, $response);
        }

//        $response = (new BitrixCache())
//            ->withId(__METHOD__ . '_' . md5(serialize($productListRequest)) . '_' . $locationCode)
//            ->withTag('infoAction')
//            ->withTime(3600) // 60 минут
//            ->resultOf($getProductListInfo);

        if ($response['products']) {
            $cartItems = $this->basketService->getBasketProducts();
        }

        foreach ($response['products'] as $productId => $product) {
            /** @var Offer $offer */
            foreach ($product as $offerId => $offer) {
                $response['products'][$productId][$offerId]['inCart'] = $cartItems[$offerId] ?? 0;
            }

        }

        return JsonSuccessResponse::createWithData('', $response);
    }

    /**
     * @Route("/product/deliverySet/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     * @global CMain $APPLICATION
     */
    public function infoProductDeliveryAction(Request $request): JsonResponse
    {
        $response = [];

        $currentOffer = null;
        $requestedOfferId = (int)$request->get('offer', 0);

        $currentOffer = OfferQuery::getById($requestedOfferId);

        if ($currentOffer) {
            global $APPLICATION;
            ob_start();
            $APPLICATION->IncludeComponent(
                'fourpaws:catalog.product.delivery.info',
                'detail',
                [
                    'OFFER' => $currentOffer,
                ],
                false,
                ['HIDE_ICONS' => 'Y']
            );

            $response['deliveryHtml'] = ob_get_clean();
        }
        return JsonSuccessResponse::createWithData('', $response);
    }

    /**
     * @Route("/groupSet/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @throws ArgumentException
     * @throws \InvalidArgumentException
     *
     * @throws SystemException
     * @return JsonResponse
     */
    public function getGroupSetAction(Request $request): JsonResponse
    {
        $offerId = (int)$request->get('offerId', 0);
        $groupIndex = (int)$request->get('index');
        $requestedOffer = false;
        if ($offerId && null !== $groupIndex) {
            $requestedOffer = OfferQuery::getById($offerId);
        }
        if ($requestedOffer) {
            /** @var Offer $requestedOffer */
            $groupSets = self::getGroupSets($requestedOffer);
            $offerIds = [];
            if (isset($groupSets[0]['groupSet'][$groupIndex]) && !empty($groupSets[0]['groupSet'][$groupIndex])) {
                $offerIds = $groupSets[0]['groupSet'][$groupIndex];
                /** @var Share $share */
                $share = $groupSets[0]['share'];
            }
            /** @noinspection UnSafeIsSetOverArrayInspection */
            if (!empty($offerIds) && isset($share)) {
                $offers = (new OfferQuery())->withFilter(['=ID' => $offerIds])->exec();
                /** @var Offer $offer */
                foreach ($offers as $offer) {
                    /** @var ResizeImageCollection $images */
                    $images = $offer->getResizeImages(140, 140);
                    if (null !== $image = $images->first()) {
                        $image = (string)$image;
                    } else {
                        $image = '';
                    }
                    /** @var Product $product */
                    $product = $offer->getProduct();
                    $name = '<strong>' . $product->getBrandName() . '</strong> ' . \lcfirst(\trim($product->getName()));
                    if (0 < $weight = $offer->getCatalogProduct()->getWeight()) {
                        $weight = WordHelper::showWeight($weight);
                    } else {
                        $weight = '';
                    }
                    $items[] = [
                        'id'         => $offer->getId(),
                        'price'      => $offer->getPrice(),
                        'link'       => $offer->getLink(),
                        'image'      => $image,
                        'name'       => $name,
                        'additional' => $weight,
                    ];
                }

                if (
                    ($basketRule = $this->basketRulesRepository->findOneByXmlId($share->getXmlId()))
                    &&
                    ($actionsArray = $basketRule->getActions())
                    &&
                    \count($actionsArray['CHILDREN']) === 1
                    &&
                    $actionsArray['CHILDREN'][0]['CLASS_ID'] === 'ADV:DetachedRowDiscount'
                ) {
                    $discountPercent = (float)$actionsArray['CHILDREN'][0]['DATA']['Value'];
                }
            }
        } else {
            $response = JsonErrorResponse::createWithData(
                'Товар не найден',
                [],
                200,
                ['reload' => true]
            );
        }

        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (!isset($response) && isset($discountPercent) && isset($items)) {
            $data = [
                'discount' => $discountPercent,
                'title'    => 'Выберите товар',
                'items'    => $items,
            ];
            $response = JsonSuccessResponse::createWithData(
                '',
                $data,
                200,
                ['reload' => false]
            );
        }

        return $response;
    }

    /**
     * @Route("/count-by-filter-brand/", methods={"GET", "POST"})
     *
     * @param CatalogBrandFilterRequest $catalogBrandRequest
     *
     * @return JsonResponse
     */
    public function getCountItemsByFilterBrand(CatalogBrandFilterRequest $catalogBrandRequest): JsonResponse
    {
        $count = 0;
        $logger = LoggerFactory::create('ajaxFilter');
        try {
            $productSearchResult = $this->searchService->searchProducts(
                $catalogBrandRequest->getCategory()->getFilters(),
                $catalogBrandRequest->getSorts()->getSelected(),
                $catalogBrandRequest->getNavigation(),
                $catalogBrandRequest->getSearchString()
            );
            $count = $productSearchResult->getResultSet()->getTotalHits();
        } catch (Exception $e) {
            $logger->error('Ошибка подгрузки количества итемов в фильтре ' . $e->getMessage());
        }
        return JsonSuccessResponse::createWithData('подгрузка количества успешна',
            [
                'filterButtonText' => 'Показать ' . $count . ' ' . WordHelper::declension($count,
                        ['товар', 'товара', 'товаров']),
            ]);
    }

    /**
     * @Route("/count-by-filter-search/", methods={"GET", "POST"})
     *
     * @param SearchRequest $searchRequest
     *
     * @return JsonResponse
     */
    public function getCountItemsByFilterSearch(SearchRequest $searchRequest): JsonResponse
    {
        $count = 0;
        $logger = LoggerFactory::create('ajaxFilter');
        try {
            $productSearchResult = $this->searchService->searchProducts(
                $searchRequest->getCategory()->getFilters(),
                $searchRequest->getSorts()->getSelected(),
                $searchRequest->getNavigation(),
                $searchRequest->getSearchString()
            );
            $count = $productSearchResult->getResultSet()->getTotalHits();
        } catch (Exception $e) {
            $logger->error('Ошибка подгрузки количества итемов в фильтре ' . $e->getMessage());
        }
        return JsonSuccessResponse::createWithData('подгрузка количества успешна',
            [
                'filterButtonText' => 'Показать ' . $count . ' ' . WordHelper::declension($count,
                        ['товар', 'товара', 'товаров']),
            ]);
    }

    /**
     * @Route("/count-by-filter-list/", methods={"GET", "POST"})
     *
     * @param ChildCategoryFilterRequest $categoryRequest
     *
     * @return JsonResponse
     */
    public function getCountItemsByFilterList(ChildCategoryFilterRequest $categoryRequest): JsonResponse
    {
        $count = 0;
        $logger = LoggerFactory::create('ajaxFilter');
        try {
            $productSearchResult = $this->searchService->searchProducts(
                $categoryRequest->getCategory()->getFilters(),
                $categoryRequest->getSorts()->getSelected(),
                $categoryRequest->getNavigation(),
                $categoryRequest->getSearchString()
            );
            $count = $productSearchResult->getResultSet()->getTotalHits();
        } catch (Exception $e) {
            $logger->error('Ошибка подгрузки количества итемов в фильтре ' . $e->getMessage());
        }
        return JsonSuccessResponse::createWithData('подгрузка количества успешна',
            [
                'filterButtonText' => 'Показать ' . $count . ' ' . WordHelper::declension($count,
                        ['товар', 'товара', 'товаров']),
            ]);
    }

    /**
     * Возвращает инф-у об оффере
     *
     * available - доступно на складе (не важно наличие или доставка)
     * byRequest - только под заказ
     * price - цена
     * oldPrice - цена без скидок
     * pickup - только самовывоз
     *
     * $offer->isPickupAvailable() - Не ставить в начало условия, он генерирует много PHP-кода и может отрабатывать по 0.5с
     *
     * @param Product $product
     * @param Offer $offer
     *
     * @return array
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws NotFoundException
     */
    private function getProductInfo(Product $product, Offer $offer): array
    {
        $available = $offer->isAvailable();
        $price = $offer->getCatalogPrice();
        $oldPrice = $offer->getOldPrice() ? $offer->getCatalogOldPrice() : $price;
        $isByRequest = $offer->isByRequest();
        $pickupOnly = $available && !$offer->isDeliverable() && $product->isPickupAvailable() && $offer->isPickupAvailable();

        $responseItem = [
            'available' => $available,
            'byRequest' => $isByRequest,
            'price'     => $price,
            'oldPrice'  => $oldPrice,
            'pickup'    => $pickupOnly,
        ];

        return $responseItem;
    }
}
