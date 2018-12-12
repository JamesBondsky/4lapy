<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Bitrix\Sale\BasketItem;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\SaleBundle\Service\BasketService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class UserCartController
 * @package FourPaws\MobileApiBundle\Controller\v0
 */
class UserCartController extends FOSRestController
{
    /**
     * @var BasketService
     */
    private $basketService;

    public function __construct(BasketService $basketService)
    {
        $this->basketService = $basketService;
    }

    /**
     * @Rest\Get("/user_cart/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     */
    public function getUserCartAction()
    {
        $result = array(
            'cart_param' => array(
                'card' => '',
                'card_used' => '',
                'delivery_type' => '',
                'delivery_place' => '',
                'delivery_date' => '',
                'delivery_time_range' => '',
                'pickup_place' => ''
            ),
            'cart_calc' => array(
                'total_price' => array(
                    'actual' => $this->basketService->getBasket()->getPrice(),
                    'old' => ''
                ),
                'price_details' => array(
                    array(
                        'id' => 'cart_price',
                        'title' => 'Товаров в корзине на сумму',
                        'value' => $this->basketService->getBasket()->getPrice()
                    ),
                    array(
                        'id' => 'delivery',
                        'title' => 'Стоимость доставки',
                        'value' => ''
                    )
                ),
                'card_details' => array(
                    array(
                        'id' => 'bonus_add',
                        'title' => 'Начислено',
                        'value' => ''
                    ),
                    array(
                        'id' => 'bonus_sub',
                        'title' => 'Списано',
                        'value' => ''
                    )
                )
            )
        );

        /**
         * @var $basketItem BasketItem
         */
        foreach ($this->basketService->getBasket() as $basketItem) {
            $offer = OfferQuery::getById($basketItem->getProductId());
            $result['cart_param']['goods'][] = [
                'goods' => $offer->toArray(),
                'qty' => $basketItem->getQuantity()
            ];
        }


        /*
       if($promocode) {
           $arResult['cart_calc']['promocode_result'] = ($promocode and $promocode_result["result"])?$promocode:'';
       }
       */

        return (new ApiResponse())->setData($result);
    }

}
