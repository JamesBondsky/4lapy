<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global CMain $APPLICATION
 * @var array    $arResult
 */

?>
<div class="b-account-tab-top">
    <div class="b-account-tab-top__title">Наиболее часто заказываемые вами товары</div>
    <div class="b-common-wrapper b-common-wrapper--stock b-common-wrapper--account-top">
        <?php if (\is_array($arResult['PRODUCTS']) && !empty($arResult['PRODUCTS'])) {
            foreach ($arResult['PRODUCTS'] as $id => $product) {
                $APPLICATION->IncludeComponent(
                    'fourpaws:catalog.element.snippet',
                    '',
                    [
                        'PRODUCT'       => $product,
                        'CURRENT_OFFER' => $arResult['OFFERS'][$id],
                    ]
                );
            } ?>
        <?php } else { ?>
            Вы еще не делали ни одного заказа
        <?php } ?>
    </div>
</div>