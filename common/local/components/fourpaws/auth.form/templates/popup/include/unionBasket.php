<?php

use FourPaws\Helpers\WordHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var string $backUrl
 * /** @var float $sum
 * @var string $needAddPhone
 * @var array  $delBasketIds
 */
$ajaxUrl = '/ajax/user/auth/login/'; ?>
<div class="b-registration__content b-registration__content--moiety b-registration__content--step b-cart-combination">
    <div class="b-registration__text-instruction">В вашей корзине есть товары на
        сумму <?= WordHelper::numberFormat($sum) ?> руб. Объединить товары в одну корзину?
    </div>
    <form class="b-registration__form">
        <input type="button" name="confirm_yes" value="Да"
               class="b-button b-button--social b-button--full-width js-ajax-item" data-url="<?= $ajaxUrl ?>"
               data-action="unionBasket" data-backurl="<?= $backUrl ?>"
               data-need_add_phone="<?= $needAddPhone ?>"
            <?= !empty($delItemsByUnion) ? 'data-del_items_by_union="' . implode(',', $delItemsByUnion) . '"' : '' ?>
            <?= !empty($delItemsByUnionKeys) ? 'data-del_items_by_union_keys="' . implode(',', $delItemsByUnionKeys) . '"' : '' ?>
            <?= !empty($addQuantityByUnion) ? 'data-add_quantity_by_union="' . json_encode($addQuantityByUnion) . '"' : '' ?>
        >
        <input type="button" name="confirm_no" value="Нет"
               class="b-button b-button--social b-button--full-width js-ajax-item" data-url="<?= $ajaxUrl ?>"
               data-action="notUnionBasket" data-backurl="<?= $backUrl ?>" data-need_add_phone="<?= $needAddPhone ?>"
            <?= !empty($delBasketIds) ? 'data-del_basket_items="' . implode(',', $delBasketIds) . '"' : '' ?>
            <?= !empty($delBasketKeys) ? 'data-del_basket_items_by_keys="' . implode(',', $delBasketKeys) . '"' : '' ?>
        >
    </form>
</div>