<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

if (!\is_array($arResult['ITEMS']) || empty($arResult['ITEMS'])) {
    return;
}

/*if ($arResult['ECOMMERCE_VIEW_SCRIPT']) {
    echo $arResult['ECOMMERCE_VIEW_SCRIPT'];
}*/ ?>
<section class="b-promo-banner">
    <div class="b-container">
        <div class="b-promo-banner__list js-promo-banner">
	        <? foreach ($arResult['ITEMS'] as $key => $item) { ?>
		        <div class="b-promo-banner-item<?= $item['MOD']['ADDITIONAL_CLASSES'] ?>">
	                <div class="b-promo-banner-item__content">
	                    <div class="b-promo-banner-item__left">
	                        <div class="b-promo-banner-item__logo"></div>
	                        <div class="b-promo-banner-item__img">
	                            <img src="<?= $item['PREVIEW_PICTURE']['SRC'] ?>" alt=""/>
	                        </div>
	                    </div>
	                    <div class="b-promo-banner-item__descr"><?= $item['PREVIEW_TEXT'] ?></div>
	                    <div class="b-promo-banner-item__link-wrap">
	                        <a class="b-promo-banner-item__link" href="<?= $item['DISPLAY_PROPERTIES']['LINK']['VALUE'] ?>">Подробнее</a>
	                    </div>
	                </div>
	            </div>
	        <? } ?>
        </div>
    </div>
</section>