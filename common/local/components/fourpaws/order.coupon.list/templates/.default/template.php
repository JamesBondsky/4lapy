<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Decorators\SvgDecorator;

/**
 * @global CMain                 $APPLICATION
 * @var array                    $arParams
 * @var array                    $arResult
 * @var CBitrixComponent         $component
 * @var CBitrixComponentTemplate $this
 * @var string                   $templateName
 * @var string                   $componentPath
 */

/** @var bool $show */
$show = $arResult['SHOW'];
/** @var ArrayCollection $coupons */
$coupons = $arResult['COUPONS'];
/** @var ArrayCollection $offers */
$offers = $arResult['OFFERS'];

?>

<? if ($show && $coupons->count() > 0) { ?>
    <div class="b-stock__coupons" data-basket-coupons>
        <button type="button" class="b-stock__coupons-btn" data-basket-coupons-toogle>Мои купоны</button>
        <div class="b-stock__coupons-popup loading" data-basket-coupons-popup>
            <div class="b-stock__coupons-list">
                <? foreach ($coupons as $coupon) { ?>
                    <?
                    /** @var ArrayCollection $offers */
                    $offer = $offers->filter(function ($offer) use ($coupon) {
                        return $offer['ID'] === $coupon['UF_OFFER'];
                    })->first();
                    ?>
                    <div class="b-stock__coupons-item">
                        <div class="b-stock__coupon no-delete">
                            <div class="b-stock__coupon-caption"><?= $offer['~NAME'] ?></div>

                            <? if ($arResult['APPLY_COUPON'] == $coupon['UF_PROMO_CODE']) { ?>
	                            <button type="button" class="b-stock__coupon-btn active" data-basket-coupon-toogle="<?= $coupon['UF_PROMO_CODE'] ?>">Отменить</button>
                            <? } else { ?>
                                <button type="button" class="b-stock__coupon-btn" data-basket-coupon-toogle="<?= $coupon['UF_PROMO_CODE'] ?>">Применить</button>
                            <? } ?>
                        </div>
                    </div>
                <? } ?>
            </div>
        </div>
    </div>
<? } ?>