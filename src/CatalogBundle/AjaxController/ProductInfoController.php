<?php

namespace FourPaws\CatalogBundle\AjaxController;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\Internals\BasketTable;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\BitrixOrm\Collection\ResizeImageCollection;
use FourPaws\BitrixOrm\Model\Share;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\CatalogBundle\Dto\ProductListRequest;
use FourPaws\Helpers\WordHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SapBundle\Repository\BasketRulesRepository;
use FourPaws\Search\Model\ProductSearchResult;
use FourPaws\Search\SearchService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use WebArch\BitrixCache\BitrixCache;

/**
 * Class ProductInfoController
 *
 * @package FourPaws\CatalogBundle\Controller
 * @Route("/product-info")
 */
class ProductInfoController extends Controller
{
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
     * @var BasketRulesRepository
     */
    protected $basketRulesRepository;

    /**
     * ProductInfoController constructor.
     *
     * @param ValidatorInterface    $validator
     * @param SearchService         $searchService
     * @param BasketService         $basketService
     * @param BasketRulesRepository $basketRulesRepository
     */
    public function __construct(
        ValidatorInterface $validator,
        SearchService $searchService,
        BasketService $basketService,
        BasketRulesRepository $basketRulesRepository
    ) {
        $this->validator = $validator;
        $this->basketService = $basketService;
        $this->searchService = $searchService;
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
     * @param Request            $request
     * @param ProductListRequest $productListRequest
     *
     * @return JsonResponse
     * @throws \InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws \Exception
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @global \CMain            $APPLICATION
     *
     */
    public function infoAction(Request $request, ProductListRequest $productListRequest): JsonResponse
    {
        $response = [
            'products' => [],
        ];

        $currentOffer = null;

        $cartItems = [];
        $res = BasketTable::query()->setSelect(['PRODUCT_ID', 'QUANTITY'])->setFilter([
            'FUSER_ID' => Fuser::getId(),
            'ORDER_ID' => null,
            'LID'      => SITE_ID,
        ])->exec();
        while ($basketItem = $res->fetch()) {
            $cartItems[(int)$basketItem['PRODUCT_ID']] = (float)$basketItem['QUANTITY'];
        }

        /** @var LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');
        $location = $locationService->getCurrentLocation();

        if (!$this->validator->validate($productListRequest)->count()) {
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

            /** не кешируем выборку, если будет свободная оператива в memcache можно кешануть на день */
//            $bitrixCache = new BitrixCache();
//            $bitrixCache
//                ->withId(__METHOD__ . '_location_' . '_product_' . implode('-', $productIds).'_location_'.$location);
//            foreach ($productIds as $productId) {
//                $bitrixCache->withTag('catalog:product:' . $productId);
//                $bitrixCache->withTag('iblock:item:' . $productId);
//            }
//            $products = $bitrixCache->resultOf($getProducts);
            $products = $getProducts();

            /** кешировать нельзя так как мы не знаем id для сброса кеша */
            $offerCollection = (new OfferQuery())->withFilter([
                '=PROPERTY_CML2_LINK' => $productIds,
                'ACTIVE'              => 'Y',
            ])->exec();

            /** @var Offer $offer */
            /** @var Product $product */
            /** добавляем офферы чтобы е было запроса по всем офферам */
            foreach ($offerCollection as &$offer) {
                $product = $products[$offer->getCml2Link()];
                $product->addOffer($offer);
                $offer->setProduct($product);
            }
            unset($product, $offer);

            foreach ($offerCollection as $offer) {
                $product = $products[$offer->getCml2Link()];

                $getResponseItem = function () use ($product, $offer) {
                    $price = ceil($offer->getPrice());
                    $oldPrice = $offer->getOldPrice() ? ceil($offer->getOldPrice()) : $price;
                    $responseItem = [
                        'available' => $offer->isAvailable(),
                        'byRequest' => $offer->isByRequest(),
                        'price'     => $price,
                        'oldPrice'  => $oldPrice,
                        'pickup'    => false,
                    ];
                    if ($responseItem['available']) {
                        $responseItem['pickup'] = $product->isPickupAvailable() && !$product->isDeliveryAvailable();
                    }
                    return $responseItem;
                };

                $bitrixCache = new BitrixCache();
                $bitrixCache
                    ->withId(__METHOD__ . '_product_' . $offer->getCml2Link() . '_offer_' . $offer->getId() . '_location_' . $location);
                $bitrixCache->withTag('catalog:product:' . $product->getId());
                $bitrixCache->withTag('iblock:item:' . $product->getId());
                $bitrixCache->withTag('catalog:offer:' . $offer->getId());
                $bitrixCache->withTag('iblock:item:' . $offer->getId());
                $bitrixCache->withTime(24*60*60);//кешируем на сутки
                $responseItem = $bitrixCache->resultOf($getResponseItem);

                $responseItem['inCart'] = $cartItems[$offer->getId()] ?? 0;

                $response['products'][$product->getId()][$offer->getId()] = $responseItem;
            }
        }
        return JsonSuccessResponse::createWithData('', $response);
    }

    /**
     * @Route("/product/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     * @global \CMain $APPLICATION
     */
    public function infoProductAction(Request $request): JsonResponse
    {
        $currentOffer = null;
        $offerId = (int)$request->get('offer', 0);

        /** @var LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');
        $location = $locationService->getCurrentLocation();

        $currentOffer = OfferQuery::getById($offerId);

        if ($currentOffer !== null) {
            $getResponse = function () use ($currentOffer) {
                $response = [
                    'products' => [],
                ];

                /** @var Offer $offer */
                /** @var Offer $currentOffer */
                if ($currentOffer !== null) {
                    $response['products'][$currentOffer->getCml2Link()][$currentOffer->getId()] = [
                        'available' => $currentOffer->isAvailable(),
                    ];
                }

                return $response;
            };

            $bitrixCache = new BitrixCache();
            $bitrixCache
                ->withId('available_response_offer_' . $offerId . '_location_' . $location);
            if ($offerId > 0) {
                $bitrixCache->withTag('catalog:offer:' . $offerId);
                $bitrixCache->withTag('iblock:item:' . $offerId);
                $bitrixCache->withTime(24*60*60);//кешируем на сутки
            }
            $response = $bitrixCache->resultOf($getResponse);
        } else {
            $response = [
                'products' => [],
            ];
        }

        return JsonSuccessResponse::createWithData('', $response);
    }

    /**
     * @Route("/product/deliverySet/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     * @global \CMain $APPLICATION
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
                        'link'      => $offer->getLink(),
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
}
