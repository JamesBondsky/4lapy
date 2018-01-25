<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $recommendedItems */
/** @var array $alsoItems */
if ((!\is_array($recommendedItems) || empty($recommendedItems)) || (!\is_array($alsoItems) || empty($alsoItems))) {
    return;
}
global $APPLICATION; ?>

<p class="b-food__text b-food__text--recomend">Мы рекомендуем</p>
<div class="b-common-wrapper b-common-wrapper--visible js-catalog-wrapper">
    <?php foreach ($recommendedItems as $product) {
        $APPLICATION->IncludeComponent(
            'fourpaws:catalog.element.snippet',
            '',
            [
                'PRODUCT'    => $product,
                'CACHE_TIME' => 0,
            ]
        );
    } ?>
</div>
<div class="b-line b-line--q-food">
</div>
<section class="b-common-section">
    <div class="b-common-section__title-box b-common-section__title-box--q-food">
        <h2 class="b-title b-title--q-food">Так же вам подойдёт
        </h2>
    </div>
    <div class="b-common-section__content b-common-section__content--q-food js-q-food-product">
        <?php foreach ($alsoItems as $product) {
            $APPLICATION->IncludeComponent(
                'fourpaws:catalog.element.snippet',
                '',
                [
                    'PRODUCT'    => $product,
                    'CACHE_TIME' => 0,
                ]
            );
        } ?>
    </div>
</section>