<?php

namespace FourPaws\SaleBundle\Discount;


use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use FourPaws\External\Exception\ManzanaPromocodeUnavailableException;
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
     * Manzana constructor.
     *
     * @param BasketService $basketService
     * @param ManzanaPosService $manzanaPosService
     * @param UserService $userService
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
        $this->promocode = $promocode;
    }

    /**
     * @throws RuntimeException
     * @throws ManzanaPromocodeUnavailableException
     */
    public function calculate()
    {
        $basket = $this->basketService->getBasket();

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
            } else {
                $response = $this->manzanaPosService->processCheque($request);
            }
        } catch (ExecuteException $e) {
            dump($e);
            $this->log()->error(
                \sprintf(
                    'Manzana error: %s',
                    $e->getMessage()
                )
            );
        }

        $this->recalculateBasketFromResponse($basket, $response);
    }

    /**
     * @param Basket $basket
     * @param SoftChequeResponse $response
     */
    public function recalculateBasketFromResponse(Basket $basket, SoftChequeResponse $response): void {
        $manzanaItems = $response->getItems();

        /**
         * @var BasketItem $item
         */
        foreach ($basket as $item) {
            $itemXmlId = \preg_replace('~^(.*#)~', '', $item->getField('PRODUCT_XML_ID'));
            dump($manzanaItems, $itemXmlId);die;
        }
    }

    /**
     * @param SoftChequeResponse $response
     * @param string $promocode
     *
     * @throws ManzanaPromocodeUnavailableException
     */
    public function checkPromocodeByResponse(SoftChequeResponse $response, string $promocode) {
        dump([$response->getCoupons(), $promocode, $response]);

        throw new ManzanaPromocodeUnavailableException(
            \sprintf(
                'Promocode %s is not found or unavailable in current context',
                $this->promocode
            )
        );
    }
}
