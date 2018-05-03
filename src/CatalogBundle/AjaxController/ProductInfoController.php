<?php

namespace FourPaws\CatalogBundle\AjaxController;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\BasketItem;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\BitrixOrm\Collection\ResizeImageCollection;
use FourPaws\BitrixOrm\Model\Share;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Model\Sorting;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\CatalogBundle\Dto\ProductListRequest;
use FourPaws\Helpers\WordHelper;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SapBundle\Repository\BasketRulesRepository;
use FourPaws\Search\Model\Navigation;
use FourPaws\Search\Model\ProductSearchResult;
use FourPaws\Search\SearchService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
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
     * @param ValidatorInterface $validator
     * @param SearchService $searchService
     * @param BasketService $basketService
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
     * @Route("/", methods={"GET"})
     *
     * @global \CMain $APPLICATION
     *
     * @param Request $request
     * @param ProductListRequest $productListRequest
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws ArgumentException
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ApplicationCreateException
     *
     * @return JsonResponse
     */
    public function infoAction(Request $request, ProductListRequest $productListRequest): JsonResponse
    {
        $response = [
            'products' => []
        ];

        $requestedOfferId = (int)$request->query->get('offer', 0);
        $currentOffer = null;

        $cartItems = [];
        $basket = $this->basketService->getBasket();
        /** @var BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            $cartItems[(int)$basketItem->getProductId()] = $basketItem->getQuantity();
        }

        if (!$this->validator->validate($productListRequest)->count()) {
            /** @var ProductSearchResult $result */
            /** для списка товаров дает небольой выйгрыш отдельное получение офферов*/
            $productCollection = (new ProductQuery())->withFilter(['=ID' => $productListRequest->getProductIds()])->exec();
            /** @var Product $product */
            $products = [];
            if($productCollection->count() === 1) {
                $product = $productCollection->first();
                $products[$product->getId()] = $product;
            } else{
                foreach ($productCollection as $product) {
                    $products[$product->getId()] = $product;
                }
            }
            $offerCollection = (new OfferQuery())->withFilter(['=PROPERTY_CML2_LINK'=>$productListRequest->getProductIds(), 'ACTIVE' => 'Y'])->exec();

            /** @var Offer $offer */
            foreach ($offerCollection as $offer) {
                $offer->setProduct($products[$offer->getCml2Link()]);
                    $offerId = $offer->getId();
                    if ($requestedOfferId && $offerId === $requestedOfferId) {
                        $currentOffer = $offer;
                    }
                    $price = ceil($offer->getPrice());
                    $oldPrice = $offer->getOldPrice() ? ceil($offer->getOldPrice()) : $price;
                    $responseItem = [
                        'available' => $offer->isAvailable(),
                        'byRequest' => $offer->isByRequest(),
                        'price' => $price,
                        'oldPrice' => $oldPrice,
                        'inCart' => $cartItems[$offerId] ?? 0,
                        'pickup' => false
                    ];
                    if($responseItem['available']){
                        $responseItem['pickup'] = $product->isPickupAvailable() && !$product->isDeliveryAvailable();
                    }
                    $response['products'][$product->getId()][$offerId] = $responseItem;
            }

            if ($currentOffer) {
                $time = microtime();
                global $APPLICATION;
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

    /**
     * @Route("/groupSet/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \InvalidArgumentException
     *
     * @throws \Bitrix\Main\SystemException
     * @return JsonResponse
     */
    public function getGroupSetAction(Request $request): JsonResponse
    {
        $offerId = (int)$request->get('offerId', 0);
        $groupIndex = (int)$request->get('index');
        $requestedOffer = false;
        if ($offerId && null !== $groupIndex) {
            $requestedOffer = (new OfferQuery())->withFilter(['=ID' => $offerId])->exec()->first();
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
                    $images = $offer->getResizeImages(110, 110);
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
                        'id' => $offer->getId(),
                        'price' => $offer->getPrice(),
                        'image' => $image,
                        'name' => $name,
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
                'title' => 'Выберите товар',
                'items' => $items
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
                'share' => $shareOfResultGroupSet,
                'groupSet' => $groupSet,
            ];
        }
        return $result;
    }

    /**
     *
     *
     * @param int $offerId
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
}
