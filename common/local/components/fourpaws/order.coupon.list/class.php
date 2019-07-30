<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Service\PersonalOffersService;
use FourPaws\SaleBundle\Discount\Manzana;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponSessionStorage;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponStorageInterface;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use FourPaws\SaleBundle\AjaxController\BasketController;

class FourPawsOrderCouponListComponent extends CBitrixComponent
{
    /**
     * @var CurrentUserProviderInterface
     */
    private $userService;
    /**
     * @var PersonalOffersService $personalOffersService
     */
    protected $personalOffersService;

    /**
     * @var CouponSessionStorage $couponsStorage
     */
    private $couponsStorage;

    /**
     * @var BasketService $basketService
     */
    private $basketService;

    /**
     * @var Manzana
     */
    private $manzana;

    /**
     * @var BasketController
     */
    private $basketController;

    /**
     * FourPawsOrderCouponListComponent constructor.
     *
     * @param CBitrixComponent|null $component
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);

        $serviceContainer = Application::getInstance()->getContainer();
        $this->userService = $serviceContainer->get(CurrentUserProviderInterface::class);
        $this->personalOffersService = $serviceContainer->get('personal_offers.service');
        $this->couponsStorage = $serviceContainer->get(CouponStorageInterface::class);
        $this->basketService = $serviceContainer->get(BasketService::class);
        $this->manzana = $serviceContainer->get(Manzana::class);
        $this->basketController = $serviceContainer->get(BasketController::class);
    }

    public function executeComponent()
    {
        $this->arResult['SHOW'] = true;
        $this->arResult['COUPONS'] = [];
        try {
            $userID = $this->userService->getCurrentUserId();
            $offers = null;

            $offersCoupons = $this->personalOffersService->getActiveUserCoupons($userID);

            /** @var ArrayCollection $coupons */
            $coupons = $offersCoupons['coupons'];
            if (!$coupons->isEmpty()) {
                $couponsIdsCollection = $coupons
                    ->filter(static function ($coupon) {
                        return !$coupon['PERSONAL_COUPON_USER_COUPONS_UF_SHOWN'];
                    })
                    ->map(static function ($coupon) {
                        return $coupon['PERSONAL_COUPON_USER_COUPONS_ID'];
                    });
                $couponsIds = $couponsIdsCollection->getValues();
                if ($couponsIds) {
                    $this->personalOffersService->setCouponShownStatus($couponsIds); // установка флага просмотренности купонов
                }
            }

            $this->arResult['COUPONS'] = $offersCoupons['coupons'];
            $this->arResult['OFFERS'] = $offersCoupons['offers'];
        } catch (NotAuthorizedException|ObjectPropertyException|ArgumentException|LoaderException|SystemException|InvalidArgumentException|Exception|IblockNotFoundException $e) {
            $this->arResult['SHOW'] = false;
        }

        if ($this->arResult['SHOW']) {
            $allowPromos = [];
            $promocodes = [];
            foreach ($this->arResult['COUPONS'] as $coupon) {
                $promocodes[] = $coupon['UF_PROMO_CODE'];
            }
            if (count($promocodes)) {
                $allowPromos = $this->manzana->getAllowPromocodes($promocodes);
                foreach ($this->arResult['COUPONS'] as $key => $coupon) {
                    if (!in_array($coupon['UF_PROMO_CODE'], $allowPromos)) {
                        //unset($this->arResult['COUPONS'][$key]);
                    }
                }
            }
            $this->setCoupon($allowPromos);
        } else {
            $this->setCoupon();
        }

        $this->includeComponentTemplate();
    }

    /**
     * Set coupon and coupon discount
     *
     * @param array|null $usablePromocodes
     * @return void
     */
    private function setCoupon(?array $usablePromocodes = []): void
    {
        $this->arResult['APPLY_COUPON'] = $this->couponsStorage->getApplicableCoupon() ?? '';
        $this->arResult['APPLY_COUPON_DISCOUNT'] = !empty($this->arResult['COUPON']) ? $this->basketService->getPromocodeDiscount() : 0;
        if ($this->arResult['APPLY_COUPON']) {
            $this->arResult['COUPON_USED'] = true;
        } else {
            $this->arResult['COUPON_USED'] = false;
        }
        $this->arResult['USABLE_PROMO_CODES'] = $usablePromocodes;
    }

}