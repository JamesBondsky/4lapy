<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $recommendedItems */
/** @var array $alsoItems */
if ((!\is_array($recommendedItems) || empty($recommendedItems)) || (!\is_array($alsoItems) || empty($alsoItems))) {
    return;
} ?>

<p class="b-food__text b-food__text--recomend">Мы рекомендуем</p>
<div class="b-common-wrapper b-common-wrapper--visible js-catalog-wrapper">
    <?php foreach ($recommendedItems as $product) {
    $APPLICATION->IncludeComponent(
            'fourpaws:catalog.element.snippet',
            '',
            ['PRODUCT' => $product]
        );
} ?>
    <div class="b-common-item b-common-item--q-food js-product-item"><span class="b-common-item__sticker-wrap"
                                                                           style="background-color:;data-background:;"><img
                    class="b-common-item__sticker"
                    src="images/inhtml/s-gift.svg"
                    alt=""
                    role="presentation"></span><span class="b-common-item__image-wrap"><img class="b-common-item__image js-weight-img"
                                                                                            src="images/content/hills-cat.jpg"
                                                                                            alt="Хиллс"
                                                                                            title=""></span>
        <div class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                         href="javascript:void(0);"
                                                         title=""><span class="b-clipped-text b-clipped-text--three"><span><strong>Хиллс  </strong>корм для кошек тунец стерилайз</span></span></a>
            <div class="b-common-item__rank"><a class="b-common-item__rank-text"
                                                href="javascript:void(0);"
                                                title="Оставьте отзыв">Оставьте
                                                                       отзыв</a><span class="b-common-item__rank-text b-common-item__rank-text--red">4+1 в подарок при покупке</span>
            </div>
            <div class="b-weight-container b-weight-container--list">
                <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                   href="javascript:void(0);"
                   title=""></a>
                <ul class="b-weight-container__list">
                    <li class="b-weight-container__item"><a class="b-weight-container__link js-price active-link"
                                                            href="javascript:void(0);"
                                                            data-price="2 585"
                                                            data-image="images/content/hills-cat.jpg"
                                                            data-offerid="1779">3,5 кг</a></li>
                    <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                            href="javascript:void(0);"
                                                            data-price="2 585"
                                                            data-image="images/content/brit.png"
                                                            data-offerid="5865">8 кг</a></li>
                    <li class="b-weight-container__item"><a class="b-weight-container__link js-price unavailable-link"
                                                            href="javascript:void(0);"
                                                            data-price="2 585"
                                                            data-image="images/content/brit.png"
                                                            data-offerid="1943">12 кг</a></li>
                </ul>
            </div>
            <div class="b-common-item__moreinfo">
                <div class="b-common-item__packing">Упаковка <strong>8шт.</strong></div>
                <div class="b-common-item__country">Страна производства <strong>Нидерланды</strong></div>
                <div class="b-common-item__order">Только под заказ</div>
                <div class="b-common-item__pickup">Самовызов</div>
            </div>
            <a class="b-common-item__add-to-cart js-basket-add"
               href="javascript:void(0);"
               data-url="/json/ajax-sale-basket-add.json"
               title=""
               data-offerid="3358"><span class="b-common-item__wrapper-link"><span class="b-cart"><span class="b-icon b-icon--cart"><svg
                                    class="b-icon__svg"
                                    viewBox="0 0 16 16 "
                                    width="16px"
                                    height="16px"><use class="b-icon__use" xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                            class="b-common-item__price js-price-block">2 585</span><span class="b-common-item__currency"> <span
                                class="b-ruble">₽</span></span></span></a>
            <div class="b-common-item__additional-information">
                <div class="b-common-item__benefin">
                    <span class="b-common-item__prev-price">100 <span class="b-ruble b-ruble--prev-price">₽</span></span><span
                            class="b-common-item__discount"><span class="b-common-item__disc">Скидка</span><span class="b-common-item__discount-price">200</span><span
                                class="b-common-item__currency"> <span class="b-ruble b-ruble--discount">₽</span></span></span>
                </div>
            </div>
        </div>
    </div>
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
                ['PRODUCT' => $product]
            );
} ?>
    </div>
</section>