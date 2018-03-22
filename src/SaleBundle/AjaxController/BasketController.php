<?php
declare(strict_types=1);
/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\AjaxController;

use Bitrix\Main\Grid\Declension;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\BitrixOrm\Collection\ResizeImageCollection;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\SaleBundle\Exception\BaseExceptionInterface;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\BasketViewService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BasketController
 *
 * @package FourPaws\SaleBundle\Controller
 * @Route("/basket")
 */
class BasketController extends Controller
{
    private $basketService;
    /**
     * @var BasketViewService
     */
    private $basketViewService;

    /**
     * BasketController constructor.
     *
     * @param BasketService     $basketService
     * @param BasketViewService $basketViewService
     */
    public function __construct(BasketService $basketService, BasketViewService $basketViewService)
    {
        $this->basketService = $basketService;
        $this->basketViewService = $basketViewService;
    }

    /**
     * @Route("/add/", methods={"GET", "POST"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\LoaderException
     * @throws \RuntimeException
     *
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function addAction(Request $request): JsonResponse
    {
        $offerId = (int)$request->get('offerId', 0);
        if ($offerId === 0) {
            $offerId = (int)$request->get('offerid', 0);
        }
        $quantity = (int)$request->get('quantity', 1);

        try {

            $this->basketService->addOfferToBasket($offerId, $quantity);
            $data = [
                'remainQuantity' => 10,
                'miniBasket'     => $this->basketViewService->getMiniBasketHtml(true),
                'disableAdd'     => false,
            ];
            $response = JsonSuccessResponse::createWithData(
                'Товар добавлен в корзину',
                $data,
                200,
                ['reload' => false]
            );

        } catch (BaseExceptionInterface $e) {
            $response = JsonErrorResponse::create(
                $e->getMessage(),
                200,
                [],
                ['reload' => true]
            );
        }

        return $response;
    }

    /**
     * @Route("/delete/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @throws \Exception
     * @throws \Bitrix\Main\ObjectNotFoundException
     *
     * @return JsonErrorResponse|JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $basketId = (int)$request->get('basketId', 0);
        try {
            $this->basketService->deleteOfferFromBasket($basketId);
            $data = [
                'basket'     => $this->basketViewService->getBasketHtml(true),
                'miniBasket' => $this->basketViewService->getMiniBasketHtml(true),
                'fastOrder'  => $this->basketViewService->getFastOrderHtml(true),
            ];
            $response = JsonSuccessResponse::createWithData(
                '',
                $data,
                200,
                ['reload' => false]
            );
        } catch (NotFoundException $e) {
            $response = JsonErrorResponse::create(
                $e->getMessage(),
                200,
                [],
                ['reload' => true]
            );
        } catch (BaseExceptionInterface $e) {
            $response = JsonErrorResponse::create(
                $e->getMessage(),
                200,
                [],
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
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Exception
     *
     * @return JsonErrorResponse|JsonResponse
     */
    public function updateAction(Request $request)
    {
        $items = $request->get('items', []);
        /** fix для быстрого заказа */
        if (empty($items)) {
            $items[] = ['basketId' => $request->get('basketId'), 'quantity' => $request->get('quantity')];
        }

        /** @noinspection BadExceptionsProcessingInspection */
        try {
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
                'basket'     => $this->basketViewService->getBasketHtml(true),
                'miniBasket' => $this->basketViewService->getMiniBasketHtml(true),
                'fastOrder'  => $this->basketViewService->getFastOrderHtml(true),
            ];

            $response = JsonSuccessResponse::createWithData(
                '',
                $data,
                200,
                ['reload' => false]
            );

        } catch (BaseExceptionInterface $e) {
            $response = JsonErrorResponse::create(
                $e->getMessage(),
                200,
                [],
                ['reload' => false]                     // todo TRUE !!!
            );
        }
        return $response;
    }

    /**
     * @Route("/gift/get/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @throws \RuntimeException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\NotSupportedException
     *
     * @return JsonErrorResponse|JsonResponse
     */
    public function getGiftListAction(Request $request)
    {
        $discountId = (int)$request->get('discountId', 0);
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
                $name = '<strong>' . $product->getBrandName() . '</strong> ' . lcfirst(trim($product->getName()));
                $items[] = [
                    'id'         => $offer->getId(),
                    'actionId'   => $discountId,
                    'image'      => $image,
                    'name'       => $name,
                    'additional' => '', // todo ###
                ];

            }
            $unselectedCount = $this->basketService->getAdder()->getExistGiftsQuantity($giftGroup, false);
            $giftDeclension = new Declension('подарок', 'подарка', 'подарков');
            $data = [
                'count' => $unselectedCount,
                'title' => 'Выберете ' . $unselectedCount . ' ' . $giftDeclension->get($unselectedCount),
                'items' => $items,
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
     * @throws \RuntimeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Exception
     *
     * @return JsonErrorResponse|JsonResponse
     */
    public function selectGiftAction(Request $request)
    {
        $response = null;
        $offerId = (int)$request->get('offerId', 0);
        $discountId = (int)$request->get('actionId', 0);
        try {
            $this->basketService->getAdder()->selectGift($offerId, $discountId);
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
                    'basket' => $this->basketViewService->getBasketHtml(true),
                ],
                200,
                ['reload' => false]
            );
        }

        return $response;
    }

    /**
     * @Route("/gift/refuse/", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Exception
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\NotSupportedException
     *
     * @return JsonErrorResponse|JsonResponse
     */
    public function refuseGiftAction(Request $request)
    {
        $response = null;
        $giftBasketId = (int)$request->get('giftId', 0);

        /** @noinspection BadExceptionsProcessingInspection */
        try {
            $gift = $this->basketService->getAdder()->getExistGifts(null, true);
            if (!isset($gift[$giftBasketId])) {
                throw new NotFoundException('Подарок не найден');
            }
            $gift = $gift[$giftBasketId];
            if ($gift['quantity'] === 1) {
                $this->basketService->deleteOfferFromBasket($giftBasketId);
            } else {
                $this->basketService->updateBasketQuantity($giftBasketId, $gift['quantity'] - 1);
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
                    'basket' => $this->basketViewService->getBasketHtml(true),
                ],
                200,
                ['reload' => false]
            );
        }

        return $response;
    }
}
