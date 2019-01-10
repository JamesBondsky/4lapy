<?php
declare(strict_types=1);
/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Grid\Declension;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Exception;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\BitrixOrm\Collection\ResizeImageCollection;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\EcommerceBundle\Preset\Bitrix\SalePreset;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\External\Exception\ManzanaPromocodeUnavailableException;
use FourPaws\Helpers\WordHelper;
use FourPaws\SaleBundle\Discount\Manzana;
use FourPaws\SaleBundle\Exception\BaseExceptionInterface;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponStorageInterface;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\BasketViewService;
use InvalidArgumentException as BaseInvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BasketController
 *
 * @package FourPaws\SaleBundle\Controller
 * @Route("/basket")
 */
class BasketController extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    private $basketService;
    /**
     * @var BasketViewService
     */
    private $basketViewService;
    /**
     * @var Manzana
     */
    private $manzana;
    /**
     * @var CouponStorageInterface
     */
    private $couponStorage;
    /**
     * @var GoogleEcommerceService
     */
    private $ecommerceService;
    /**
     * @var SalePreset
     */
    private $salePreset;

    /**
     * BasketController constructor.
     *
     * @param BasketService $basketService
     * @param BasketViewService $basketViewService
     * @param Manzana $manzana
     * @param CouponStorageInterface $couponStorage
     * @param GoogleEcommerceService $ecommerceService
     * @param SalePreset $salePreset
     */
    public function __construct(
        BasketService $basketService,
        BasketViewService $basketViewService,
        Manzana $manzana,
        CouponStorageInterface $couponStorage,
        GoogleEcommerceService $ecommerceService,
        SalePreset $salePreset
    )
    {
        $this->basketService = $basketService;
        $this->basketViewService = $basketViewService;
        $this->manzana = $manzana;
        $this->couponStorage = $couponStorage;
        $this->ecommerceService = $ecommerceService;
        $this->salePreset = $salePreset;
    }

    /**
     * @Route("/add/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @throws ArgumentNullException
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @throws LoaderException
     * @throws RuntimeException
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function addAction(Request $request): JsonResponse
    {
        $offerId = (int)$request->get('offerId', 0);
        if ($offerId === 0) {
            $offerId = (int)$request->get('offerid', 0);
        }
        $quantity = (int)$request->get('quantity', 1);

        try {
            $basketItem = $this->basketService->addOfferToBasket($offerId, $quantity);
            // @todo костыль - иначе в миникорзине не будет картинки нового товара
            $this->basketService->getOfferCollection(true);
            $data = [
                'remainQuantity' => 10,
                'miniBasket' => $this->basketViewService->getMiniBasketHtml(true),
                'disableAdd' => false
            ];

            $temporaryItem = clone $basketItem;
            $temporaryItem->setFieldNoDemand('QUANTITY', $quantity);
            $data['command'] = $this->ecommerceService->renderScript(
                $this->salePreset->createAddFromBasketItem($temporaryItem),
                false
            );

            $response = JsonSuccessResponse::createWithData(
                'Товар добавлен в корзину',
                $data,
                200,
                ['reload' => false]
            );

        } catch (BaseExceptionInterface $e) {
            $response = JsonErrorResponse::createWithData(
                $e->getMessage(),
                [],
                200,
                ['reload' => true]
            );
        }

        return $response;
    }

    /**
     * @Route("/bulkAdd/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws ArgumentNullException
     * @throws Exception
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectNotFoundException
     *
     * @return JsonResponse
     */
    public function bulkAddAction(Request $request): JsonResponse
    {
        $offers = (array)$request->get('offers', []);
        $offers = \array_filter(\array_map('\intval', $offers));
        $itemCollection = [];

        if (empty($offers)) {
            $response = JsonErrorResponse::createWithData(
                'Не переданы товары',
                [],
                200,
                ['reload' => true]
            );
        } else {
            foreach ($offers as $offerId) {
                try {
                    $item = $this->basketService->addOfferToBasket($offerId);
                    $temporary = clone $item;
                    $temporary->setFieldNoDemand('QUANTITY', 1);
                    $itemCollection[] = $temporary;
                } catch (BaseExceptionInterface $e) {
                    $response = JsonErrorResponse::createWithData(
                        $e->getMessage(),
                        [],
                        200,
                        ['reload' => false]
                    );
                }
            }
        }

        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (!isset($response)) {
            // @todo костыль - иначе в миникорзине не будет картинки нового товара
            $this->basketService->getOfferCollection(true);

            if ($itemCollection) {
                $data['command'] = $this->ecommerceService->renderScript(
                    $this->salePreset->createAddFromBasketItemCollection($itemCollection),
                    false
                );
            }

            $data = [
                'miniBasket' => $this->basketViewService->getMiniBasketHtml(true),
            ];

            $response = JsonSuccessResponse::createWithData(
                'Набор добавлен в корзину',
                $data,
                200,
                ['reload' => false]
            );
        }

        return $response;
    }

    /**
     * @Route("/bulkAddBundle/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @throws RuntimeException
     * @throws ArgumentNullException
     * @throws ArgumentException
     * @throws Exception
     * @throws LoaderException
     * @throws ObjectNotFoundException
     *
     * @return JsonResponse
     */
    public function bulkAddBundleAction(Request $request): JsonResponse
    {
        $offers = (array)$request->get('offerId', []);
        $itemCollection = [];

        if (empty($offers)) {
            $response = JsonErrorResponse::createWithData(
                'Не переданы товары',
                [],
                200,
                ['reload' => true]
            );
        } else {
            foreach ($offers as $offer) {
                $explode = explode('_', $offer);
                $offerId = (int)$explode[0];
                $quantity = (int)$explode[1];

                try {
                    $item = $this->basketService->addOfferToBasket($offerId, $quantity);
                    $temporary = clone $item;
                    $temporary->setFieldNoDemand('QUANTITY', $quantity);
                    $itemCollection[] = $temporary;
                } catch (BaseExceptionInterface $e) {
                    $response = JsonErrorResponse::createWithData(
                        $e->getMessage(),
                        [],
                        200,
                        ['reload' => false]
                    );
                }
            }
        }

        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (!isset($response)) {
            // @todo костыль - иначе в миникорзине не будет картинки нового товара
            $this->basketService->getOfferCollection(true);

            $data = [
                'miniBasket' => $this->basketViewService->getMiniBasketHtml(true),
            ];

            if ($itemCollection) {
                $data['command'] = $this->ecommerceService->renderScript(
                    $this->salePreset->createAddFromBasketItemCollection($itemCollection),
                    false
                );
            }

            $response = JsonSuccessResponse::createWithData(
                'Комплект добавлен в корзину',
                $data,
                200,
                ['reload' => false]
            );
        }

        return $response;
    }

    /**
     * @Route("/promo/apply/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws RuntimeException
     */
    public function applyPromoCodeAction(Request $request): JsonResponse
    {
        $promoCode = $request->get('promoCode');

        try {
            $promoCode = \htmlspecialchars($promoCode);

            $this->manzana->setPromocode($promoCode);
            $this->manzana->calculate();
            $this->couponStorage->clear();
            $this->couponStorage->save($promoCode);

            $result = JsonSuccessResponse::createWithData(
                'Промокод применен',
                [],
                200,
                ['reload' => true]
            );
        } catch (ManzanaPromocodeUnavailableException $e) {
            /**
             * Возвращаем ответ
             */
        } catch (Exception $e) {
            $this->log()->error(
                \sprintf(
                    'Promo code apply exception: %s', // в английском "промокод" пишется в два слова
                    $e->getMessage()
                )
            );
        }

        if (null === $result) {
            $result = JsonErrorResponse::create(
                'Промокод не существует или не применим к вашей корзине',
                200,
                [],
                ['reload' => false]
            );
        }
        return $result;
    }

    /**
     * @Route("/promo/delete/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws RuntimeException
     */
    public function deletePromoCodeAction(Request $request): JsonResponse
    {
        $promoCode = $request->get('promoCodeId');
        $result = null;

        try {
            $promoCode = \htmlspecialchars($promoCode);

            $this->couponStorage->delete($promoCode);
            $this->couponStorage->clear();

            $result = JsonSuccessResponse::createWithData(
                'Промокод удален',
                [],
                200,
                ['reload' => true]
            );
        } catch (Exception $e) {
            $this->log()->error(
                \sprintf(
                    'Promo code apply exception: %s', // в английском "промокод" пишется в два слова
                    $e->getMessage()
                )
            );
        }

        if (null === $result) {
            $result = JsonErrorResponse::create(
                'Промокод не найден',
                200,
                [],
                ['reload' => false]
            );
        }
        return $result;
    }

    /**
     * @Route("/delete/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @throws \RuntimeException
     * @throws ArgumentNullException
     * @throws ArgumentException
     * @throws Exception
     * @throws ObjectNotFoundException
     *
     * @return JsonErrorResponse | JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $basketId = (int)$request->get('basketId', 0);
        $isFastOrder = $request->get('fastOrder', 'n') === 'y';

        try {
            $basketItem = $this->basketService->getBasket()->getItemById($basketId);
            if ($basketItem) {
                $basketItem = clone $basketItem;
            }

            $result = $this->basketService->deleteOfferFromBasket($basketId);
            $data = [
                'basket' => $this->basketViewService->getBasketHtml(true),
                'miniBasket' => $this->basketViewService->getMiniBasketHtml(true),
                'fastOrder' => $isFastOrder ? $this->basketViewService->getFastOrderHtml(true) : '',
            ];

            if ($result) {
                $data['command'] = $this->ecommerceService->renderScript(
                    $this->salePreset->createRemoveFromBasketItem($basketItem),
                    false
                );
            }

            $response = JsonSuccessResponse::createWithData(
                '',
                $data,
                200,
                ['reload' => $this->basketService->getBasket()->count() === 0]
            );
        } catch (NotFoundException | BaseExceptionInterface $e) {
            $response = JsonErrorResponse::createWithData(
                $e->getMessage(),
                [],
                200,
                ['reload' => true]
            );
        }

        return $response;
    }

    /**
     * @Route("/update/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @return JsonErrorResponse | JsonResponse
     */
    public function updateAction(Request $request)
    {
        /** @noinspection BadExceptionsProcessingInspection */
        try {
            $items = $request->get('items', []);
            $isFastOrder = $request->get('fastOrder', 'n') === 'y';

            /** fix для быстрого заказа */
            if (empty($items)) {
                $items[] = ['basketId' => $request->get('basketId'), 'quantity' => $request->get('quantity', 1)];
            }

            if (!\is_array($items)) {
                throw new InvalidArgumentException('Wrong basket parameters');
            }

            foreach ($items as $item) {
                if (!$item['basketId'] || !$item['quantity']) {
                    // todo wat? :)
                    continue;
                }
                // todo изменять только то что нужно изменять
                $this->basketService->updateBasketQuantity((int)$item['basketId'], (int)$item['quantity']);
            }

            $data = [
                'basket' => $this->basketViewService->getBasketHtml(true),
                'miniBasket' => $this->basketViewService->getMiniBasketHtml(true),
                'fastOrder' => $isFastOrder ? $this->basketViewService->getFastOrderHtml(true) : '',
            ];

            $response = JsonSuccessResponse::createWithData(
                '',
                $data,
                200,
                ['reload' => false]
            );
        } catch (BaseExceptionInterface | ArgumentOutOfRangeException | Exception $e) {
            $response = JsonErrorResponse::create(
                $e->getMessage(),
                200,
                [],
                ['reload' => false]
            );
        }

        return $response;
    }

    /**
     * @Route("/gift/get/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @throws InvalidArgumentException
     * @throws BaseInvalidArgumentException
     * @throws RuntimeException
     * @throws ObjectNotFoundException
     * @throws NotSupportedException
     * @throws Exception
     *
     * @return JsonErrorResponse|JsonResponse
     */
    public function getGiftListAction(Request $request)
    {
        $discountId = (int)$request->get('discountId', 0);
        $availableGifts = (int)$request->get('availableGifts', 0);
        $response = null;
        try {
            $giftGroup = $this->basketService->getGiftGroupOfferCollection($discountId);
        } catch (BaseExceptionInterface $e) {
            $response = JsonErrorResponse::create(
                $e->getMessage(),
                200,
                [],
                ['reload' => true]
            );
        }
        if (null === $response) {
            $items = [];
            /** @var OfferCollection $offerCollection */
            /** @noinspection PhpUndefinedVariableInspection */
            $offerCollection = $giftGroup['list'];
            /** @var Offer $offer */
            foreach ($offerCollection as $offer) {
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
                $link = '';
                if (strpos($offer->getXmlId(), '1') === 0) {
                    $link = $offer->getLink();
                }
                $items[] = [
                    'id' => $offer->getId(),
                    'actionId' => $discountId,
                    'image' => $image,
                    'name' => $name,
                    'additional' => $weight,
                    'link' => $link,
                ];

            }

            $giftDeclension = new Declension('подарок', 'подарка', 'подарков');
            $data = [
                'count' => $availableGifts,
                'title' => 'Выберите <span data-count-gifts-popup="true">' . $availableGifts . '</span> ' . $giftDeclension->get($availableGifts),
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
     * @Route("/gift/select/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @throws RuntimeException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws Exception
     *
     * @return JsonErrorResponse|JsonResponse
     */
    public function selectGiftAction(Request $request)
    {
        $response = null;
        $offerId = (int)$request->get('offerId', 0);
        $discountId = (int)$request->get('actionId', 0);
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->basketService->getAdder('gift')->selectGift($offerId, $discountId);
        } catch (BaseExceptionInterface $e) {
            $response = JsonErrorResponse::create(
                $e->getMessage(),
                200,
                [],
                ['reload' => true]
            );
        }
        if (null === $response) {
            $response = JsonSuccessResponse::createWithData(
                '',
                [
                    'giftId' => 9001,
                    'basket' => $this->basketViewService->getBasketHtml(true)
                ],
                200,
                ['reload' => true] // todo разобраться почему это нужно на stage
            );
        }

        return $response;
    }

    /**
     * @Route("/gifts/select/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @throws RuntimeException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws Exception
     *
     * @return JsonErrorResponse|JsonResponse
     */
    public function selectGiftsAction(Request $request)
    {
        $response = null;
        $offers = $request->get('offers', 0);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->basketService->getAdder('gift')->selectGifts($offers);
        } catch (BaseExceptionInterface $e) {
            $response = JsonErrorResponse::create(
                $e->getMessage(),
                200,
                [],
                ['reload' => true]
            );
        }

        if (null === $response) {
            $response = JsonSuccessResponse::createWithData(
                '',
                [
                    'giftId' => 9001,
                    'basket' => $this->basketViewService->getBasketHtml(true)
                ],
                200,
                ['reload' => true] // todo разобраться почему это нужно на stage
            );
        }

        return $response;
    }

    /**
     * @Route("/gift/refuse/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @throws ArgumentNullException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws Exception
     * @throws ObjectNotFoundException
     * @throws NotSupportedException
     *
     * @return JsonErrorResponse|JsonResponse
     */
    public function refuseGiftAction(Request $request)
    {
        $response = null;
        $giftBasketId = (int)$request->get('giftId', 0);
        $quantity = (int)$request->get('quantity', 1);

        /** @noinspection BadExceptionsProcessingInspection */
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $gift = $this->basketService->getAdder('gift')->getExistGifts(null, true);
            if (!isset($gift[$giftBasketId])) {
                throw new NotFoundException('Подарок не найден');
            }
            $gift = $gift[$giftBasketId];
            if ($gift['quantity'] == $quantity) {
                $this->basketService->deleteOfferFromBasket($giftBasketId);
            } else {
                $this->basketService->updateBasketQuantity($giftBasketId, $gift['quantity'] - $quantity);
            }
        } catch (BaseExceptionInterface $e) {
            $response = JsonErrorResponse::create(
                $e->getMessage(),
                200,
                [],
                ['reload' => true]
            );
        }
        if (null === $response) {
            $response = JsonSuccessResponse::createWithData(
                '',
                [
                    'giftId' => 9001,
                    'basket' => $this->basketViewService->getBasketHtml(true)
                ],
                200,
                ['reload' => true] // todo разобраться зачем это на стейдж
            );
        }

        return $response;
    }
}
