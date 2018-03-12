<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var string $backUrl
 * @var string $needAddPhone
 * @var array $delBasketIds*/ ?>
<div class="b-registration__content b-registration__content--moiety b-registration__content--step" style="width:100% !important;">
    <div class="b-registration__text-instruction">В вашей корзине есть товары на сумму <?=$sum?> руб. Объединить товары в одну корзину?</div>
    <form class="b-registration__form js-form-validation js-ajax-form" data-url="/ajax/user/auth/login/" method="post">
        <input type="hidden" name="action" value="unionBasket">
        <input type="hidden" name="backurl" value="<?=$backUrl?>" class="js-no-valid">
        <input type="hidden" name="needAddPhone" value="<?=$needAddPhone?>" class="js-no-valid">
        <?if(!empty($delBasketIds)){
            foreach ($delBasketIds as $delBasketId) {?>
                <input type="hidden" name="del_basket_items[]" value="<?=$delBasketId?>">
            <?}
        }?>
        <input type="submit" name="confirm_yes" value="Да" class="b-button b-button--social b-button--full-width">
        <input type="submit" name="confirm_no" value="Нет" class="b-button b-button--social b-button--full-width">
    </form>
</div>