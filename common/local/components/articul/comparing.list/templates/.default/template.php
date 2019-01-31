<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>


<div class="b-container">
    <h1 class="b-title b-title--block b-title--h1-compare"><?=$arParams['TEXT_HEADER']?></h1>
    <hr class="b-hr b-hr--compare">
</div>

<? if(empty($arResult['PRODUCTS'])): ?>
    <div class="b-container">
        <div>На данный момент нет товаров, подходящих для сравнения.</div>
    </div>
<? else: ?>

<div class="b-container">
    <section class="b-common-section">
        <div class="b-common-section__title-box">
            <h2 class="b-title b-title--h2-compare"><?=$arParams['TEXT_SELECT_BRAND']?></h2>
        </div>
        <div class="b-common-section__content b-common-section__content--compare" data-slider-brand-feed-comparison="true">
            <div class="b-popular-brand__wrap b-popular-brand__wrap--feed-comparison">
                <div class="b-popular-brand">
                    <? foreach($arResult['BRANDS'] as $arBrand): ?>
                    <div class="b-popular-brand-item">
                        <a class="b-popular-brand-item__link b-popular-brand-item__link--compare" title="<?=$arBrand['NAME']?>" href="javascript:void(0);" data-item-brand-feed-compare="<?=$arBrand['ID']?>">
                            <img class="b-popular-brand-item__image" alt="<?=$arBrand['NAME']?>" title="<?=$arBrand['NAME']?>" src="<?=$arBrand['PREVIEW_PICTURE']['SRC']?>">
                        </a>
                    </div>
                    <? endforeach ?>
                </div>
            </div>
        </div>
    </section>

    <section class="b-common-section hide" data-section-products-feed-compare="true">
        <div class="b-common-section__title-box">
            <h2 class="b-title b-title--h2-compare"><?=$arParams['TEXT_SELECT_PRODUCT']?></h2>
        </div>
        <? foreach($arResult['PRODUCTS'] as $brandId => $arProducts): ?>
        <div class="b-common-section__content b-common-section__content--compare hide" data-products-feed-compare="<?=$brandId?>">
            <? foreach($arProducts as $arProduct): ?>
            <div class="b-common-item js-product-item" id="bx_<?=$arProduct['ID']?>" data-productid="<?=$arProduct['ID']?>">

                <? /* Шильдик */ ?>
                <? if(!empty($arProduct['IMAGE_MARK'])){
                    echo sprintf($arProduct['MARK_TEMPLATE'], $arProduct['IMAGE_MARK']);
                }
                ?>

                <span class="b-common-item__image-wrap">
                    <a class="b-common-item__image-link js-item-link" href="<?=$arProduct['OFFERS'][0]['DETAIL_PAGE_URL']?>">
                        <img src="<?=$arResult['IMAGES'][$arProduct['OFFERS'][0]['IMAGE']]?>" class="b-common-item__image js-weight-img" alt="<?=$arProduct['NAME']?>" title="">
                    </a>
                </span>
                <div class="b-common-item__info-center-block">
                    <a class="b-common-item__description-wrap js-item-link track-recommendation" href="<?=$arProduct['OFFERS'][0]['DETAIL_PAGE_URL']?>">
                        <span class="b-clipped-text b-clipped-text--three"><span>
                        <span class="span-strong"><?=$arResult['BRANDS'][$arProduct['BRAND_ID']]['NAME']?></span> <?=$arProduct['NAME']?></span>
                        </span>
                    </a>

                    <? /* Разный вес */ ?>
                    <div class="b-weight-container b-weight-container--list">
                        <a class="b-weight-container__link  b-weight-container__link--mobile js-mobile-select js-select-mobile-package" href="javascript:void(0);"><?=$arProduct['OFFERS'][0]['WEIGHT']?></a>
                        <div class="b-weight-container__dropdown-list__wrapper">
                            <div class="b-weight-container__dropdown-list"></div>
                        </div>
                        <ul class="b-weight-container__list">
                            <? foreach($arProduct['OFFERS'] as $i => $arOffer): ?>
                            <li class="b-weight-container__item">
                                <a data-weight-products-compare="true" data-offerid="<?=$arOffer['ID']?>" data-image="<?=$arResult['IMAGES'][$arOffer['IMAGE']]?>" data-name="<?=$arOffer['NAME']?>" data-link="<?=$arOffer['DETAIL_PAGE_URL']?>" href="javascript:void(0)" class="b-weight-container__link js-price <?=(!$i) ? 'active-link' : ''?>">
                                    <?=$arOffer['WEIGHT']?></a>
                            </li>
                            <? endforeach ?>
                        </ul>
                    </div>
                </div>
                <div class="b-common-item__btn-compare-wrap">
                    <a data-btn-compare-products-compare="true" class="b-common-item__btn-compare" href="<?=$arProduct['OFFERS'][0]['DETAIL_PAGE_URL']?>">
                        <?=$arParams['TEXT_BUTTON']?>
                    </a>
                </div>
            </div>
            <? endforeach ?>
        </div>
        <? endforeach ?>
    </section>
</div>

<? endif ?>