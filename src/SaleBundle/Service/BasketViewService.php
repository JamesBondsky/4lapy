<?php
/**
 * Created by PhpStorm.
 * Date: 15.01.2018
 * Time: 16:38
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Service;


/**
 * Class BasketViewService
 * @package FourPaws\SaleBundle\Service
 */
class BasketViewService
{
    /**
     * @var BasketService
     */
    private $basketService;

    /**
     * BasketViewService constructor.
     *
     * @param BasketService $basketService
     */
    public function __construct(BasketService $basketService)
    {
        $this->basketService = $basketService;
    }

    /**
     * @param bool $isAjax
     *
     * @return string
     */
    public function getMiniBasketHtml(bool $isAjax = false): string
    {
        return ''; //debug
        global $APPLICATION;

        \ob_start();

        $APPLICATION->IncludeComponent(
            'fourpaws:basket',
            'header.basket',
            [
                'COMPONENT_TEMPLATE'   => 'header.basket',
                'PATH_TO_BASKET'       => '/cart/',
                'PATH_TO_ORDER'        => '/sale/order/',
                'MINI_BASKET'        => true,
                'IS_AJAX'              => $isAjax,
            ],
            false,
            ['HIDE_ICONS' => 'Y']
        );

        return \ob_get_clean();
    }

    /**
     * @param bool $isAjax
     *
     * @return string
     */
    public function getFastOrderHtml(bool $isAjax = false): string
    {
        global $APPLICATION;

        \ob_start();

        $APPLICATION->IncludeComponent(
            'fourpaws:fast.order',
            '',
            [
                'TYPE'    => 'innerForm',
                'IS_AJAX' => $isAjax,
            ],
            null,
            ['HIDE_ICONS' => 'Y']
        );

        return \ob_get_clean();
    }

    /**
     *
     *
     * @param bool $isAjax
     *
     * @return string
     */
    public function getBasketHtml(bool $isAjax = false): string
    {
        global $APPLICATION;

        \ob_start();

        $APPLICATION->IncludeComponent(
            'fourpaws:basket',
            '',
            [
                'IS_AJAX' => $isAjax,
                'BASKET'  => $this->basketService->getBasket(),
            ],
            false,
            ['HIDE_ICONS' => 'Y']
        );

        return \ob_get_clean();
    }
}
