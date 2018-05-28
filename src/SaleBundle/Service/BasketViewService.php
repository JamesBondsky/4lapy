<?php

namespace FourPaws\SaleBundle\Service;

use FourPaws\SaleBundle\Discount\Utils\Manager;

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
        global $APPLICATION;

        \ob_start();
        Manager::disableExtendsDiscount();
        $APPLICATION->IncludeComponent(
            'fourpaws:basket.mini',
            '',
            [
                'COMPONENT_TEMPLATE' => '',
                'PATH_TO_BASKET' => '/cart/',
                'PATH_TO_ORDER' => '/sale/order/',
                'IS_AJAX' => $isAjax,
            ],
            false,
            ['HIDE_ICONS' => 'Y']
        );
        Manager::enableExtendsDiscount();
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
                'TYPE' => 'innerForm',
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
                'BASKET' => $this->basketService->getBasket(),
            ],
            false,
            ['HIDE_ICONS' => 'Y']
        );

        return \ob_get_clean();
    }
}
