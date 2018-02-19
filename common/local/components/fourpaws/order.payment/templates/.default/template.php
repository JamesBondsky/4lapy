<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arResult
 */

?>
<div class="b-container">
    <?php if ($arResult['IS_SUCCESS'] === 'Y') { ?>
        <h1 class="b-title b-title--h1 b-title--order">Переход на страницу оплаты заказа
        </h1>
        <div class="b-order b-order--top-line">
            <div class="b-order__text-block">Сейчас вы будете перенаправлены на страницу оплата заказа...
            </div>
        </div>
    <?php } else { ?>
        <h1 class="b-title b-title--h1 b-title--order">Ошибка при оплате заказа
        </h1>
        <div class="b-order b-order--top-line">
            <div class="b-order__text-block">
                Попробуйте оплатить заказ еще раз сейчас или в личном кабинете. Позвоните на горячую линию по номеру 8
                800 770-00-22, сообщите о проблеме, и наши операторы помогут вам
            </div>
        </div>
    <?php } ?>
</div>
