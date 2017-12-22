<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();
/**
 * Популярные бренды на главной странице сайта
 *
 * @updated: 22.12.2017
 */
$this->setFrameMode(true);

if(!$arResult['ITEMS']) {
	return;
}

?><div class="b-common-section__title-box b-common-section__title-box--popular-brand">
	<h2 class="b-title b-title--popular-brand"><?=\Bitrix\Main\Localization\Loc::getMessage('POPULAR_BRANDS_HOME.TITLE')?></h2>
	<a class="b-link b-link--title b-link--title" href="<?=$arResult['LIST_PAGE_URL']?>" title="<?=\Bitrix\Main\Localization\Loc::getMessage('POPULAR_BRANDS_HOME.ALL_LINK_TITLE')?>">
		<span class="b-link__text b-link__text--title"><?=\Bitrix\Main\Localization\Loc::getMessage('POPULAR_BRANDS_HOME.ALL_LINK')?></span>
		<span class="b-link__mobile b-link__mobile--title"><?=\Bitrix\Main\Localization\Loc::getMessage('POPULAR_BRANDS_HOME.ALL')?></span>
		<span class="b-icon">
			<svg class="b-icon__svg" viewBox="0 0 6 10 " width="6px" height="10px">
				<use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-arrow-right"></use>
			</svg>
		</span>
	</a>
</div>
<div class="b-common-section__content b-common-section__content--popular-brand">
	<div class="b-popular-brand"><?
		foreach($arResult['ITEMS'] as $arItem) {
			?><div class="b-popular-brand-item">
				<a class="b-popular-brand-item__link" title="<?=$arItem['NAME']?>" href="<?=$arItem['DETAIL_PAGE_URL']?>"><?
					if($arItem['PRINT_PICTURE']) {
						?><img class="b-popular-brand-item__image js-image-wrapper" src="<?=$arItem['PRINT_PICTURE']['SRC']?>" alt="<?=$arItem['NAME']?>" title="<?=$arItem['NAME']?>"><?
					}
				?></a>
			</div><?
		}
	?></div>
</div><?
