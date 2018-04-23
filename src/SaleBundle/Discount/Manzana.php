<?php
/** @todo Класс работает в контексте текущего юзера, а должен уметь работать в контексте разных юзеров на одном хите */

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Discount;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use FourPaws\External\Exception\ManzanaPromocodeUnavailableException;
use FourPaws\External\Manzana\Dto\ChequePosition;
use FourPaws\External\Manzana\Dto\Coupon;
use FourPaws\External\Manzana\Dto\SoftChequeResponse;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\ManzanaPosService;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\UserService;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

/**
 * Class Manzana
 *
 * @package FourPaws\SaleBundle\Discount
 */
class Manzana implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var BasketService
     */
    private $basketService;
    /**
     * @var ManzanaPosService
     */
    private $manzanaPosService;
    /**
     * @var string
     */
    private $promocode = '';
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var float
     */
    private $discount = 0.0;

    /**
     * Manzana constructor.
     *
     * @param BasketService     $basketService
     * @param ManzanaPosService $manzanaPosService
     * @param UserService       $userService
     */
    public function __construct(BasketService $basketService, ManzanaPosService $manzanaPosService, UserService $userService)
    {
        $this->basketService = $basketService;
        $this->manzanaPosService = $manzanaPosService;
        $this->userService = $userService;
    }

    /**
     * @param string $promocode
     */
    public function setPromocode(string $promocode): void
    {
        $this->promocode = trim($promocode);
    }

    /**
     * @throws RuntimeException
     * @throws ManzanaPromocodeUnavailableException
     * @throws ArgumentOutOfRangeException
     */
    public function calculate()
    {
        $basket = $this->basketService->getBasket();

        if (!$basket->count()) {
            /**
             * Empty basket
             */
            return;
        }

        $price = $basket->getPrice();

        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $user = $this->userService->getCurrentUser();
            $card = $user->getDiscountCardNumber();
        } catch (NotAuthorizedException $e) {
            $card = '';
        }

        $request = $this->manzanaPosService->buildRequestFromBasket($basket, $card);

        try {
            if ($this->promocode) {
                $response = $this->manzanaPosService->processChequeWithCoupons($request, $this->promocode);

                $this->checkPromocodeByResponse($response, $this->promocode);

                /**
                 * @todo переделать костыль
                 */
                $this->saveCouponDiscount($response);
            } else {
                $response = $this->manzanaPosService->processCheque($request);
            }

            $this->recalculateBasketFromResponse($basket, $response);
            $this->discount = $price - $basket->getPrice();
        } catch (ExecuteException $e) {
            $this->log()->error(
                \sprintf(
                    'Manzana recalculate error: %s',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @param Basket             $basket
     * @param SoftChequeResponse $response
     *
     * @throws ArgumentOutOfRangeException
     */
    public function recalculateBasketFromResponse(Basket $basket, SoftChequeResponse $response): void
    {
        $manzanaItems = $response->getItems();

        /**
         * @var BasketItem $item
         */
        foreach ($basket as $item) {
            $basketCode = (int)str_replace('n', '', $item->getBasketCode());

            $manzanaItems->map(function (ChequePosition $position) use ($basketCode, $item) {
                if ($position->getChequeItemNumber() === $basketCode) {
                    $price = $position->getSummDiscounted() / $position->getQuantity();

                    /** @noinspection PhpInternalEntityUsedInspection */
                    $item->setFieldsNoDemand([
                        'BASE_PRICE' => $item->getBasePrice(),
                        'PRICE' => $price,
                        'DISCOUNT_PRICE' => $item->getBasePrice() - $price,
                        'CUSTOM_PRICE' => 'Y',
                    ]);
                }
            });
        }
    }

    /**
     * @param SoftChequeResponse $response
     * @param string             $promocode
     *
     * @throws ManzanaPromocodeUnavailableException
     */
    public function checkPromocodeByResponse(SoftChequeResponse $response, string $promocode)
    {
        $applied = false;

        if ($response->getCoupons()) {
            $applied = $response->getCoupons()->filter(function (Coupon $coupon) use ($promocode) {
                    return $coupon->isApplied() && $coupon->getNumber() === $promocode;
                })->count() > 0;
        }

        if (!$applied) {
            throw new ManzanaPromocodeUnavailableException(
                \sprintf(
                    'Promocode %s is not found or unavailable in current context',
                    $this->promocode
                )
            );
        }
    }

    /**
     * @param SoftChequeResponse $response
     */
    private function saveCouponDiscount(SoftChequeResponse $response)
    {
        $this->basketService->setPromocodeDiscount($response->getSumm() - $response->getSummDiscounted());
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @param int $discount
     *
     * @return Manzana
     */
    public function setDiscount(int $discount): Manzana
    {
        $this->discount = $discount;

        return $this;
    }
}
