<?if (!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true) {
    die();
}
/**
 * Популярные бренды на главной странице сайта
 *
 * @updated: 25.12.2017
 */
$this->setFrameMode(true);

if (!$arResult['ITEMS']) {
    return;
}

?><div class="b-common-section__title-box b-common-section__title-box--popular-brand">
    <h2 class="b-title b-title--popular-brand"><?=\Bitrix\Main\Localization\Loc::getMessage('POPULAR_BRANDS_HOME.TITLE')?></h2>
    <a class="b-link b-link--title b-link--title" href="<?=$arResult['LIST_PAGE_URL']?>" title="<?=\Bitrix\Main\Localization\Loc::getMessage('POPULAR_BRANDS_HOME.ALL_LINK_TITLE')?>">
        <span class="b-link__text b-link__text--title"><?=\Bitrix\Main\Localization\Loc::getMessage('POPULAR_BRANDS_HOME.ALL_LINK')?></span>
        <span class="b-link__mobile b-link__mobile--title"><?=\Bitrix\Main\Localization\Loc::getMessage('POPULAR_BRANDS_HOME.ALL')?></span>
        <span class="b-icon"><?php
            echo new \FourPaws\Decorators\SvgDecorator('icon-arrow-right', 6, 10);
        ?></span>
    </a>
</div>
<div class="b-common-section__content b-common-section__content--popular-brand">
    <div class="b-popular-brand"><?php
        foreach ($arResult['ITEMS'] as $arItem) {
            ?><div class="b-popular-brand-item">
                <a class="b-popular-brand-item__link" title="<?=$arItem['NAME']?>" href="<?=$arItem['DETAIL_PAGE_URL']?>"><?php
                    if ($arItem['PRINT_PICTURE']) {
                        ?><img class="b-popular-brand-item__image js-image-wrapper" src="<?=$arItem['PRINT_PICTURE']['SRC']?>" alt="<?=$arItem['NAME']?>" title="<?=$arItem['NAME']?>"><?php
                    }
                ?></a>
            </div><?php
        }
    ?></div>
</div><?php
