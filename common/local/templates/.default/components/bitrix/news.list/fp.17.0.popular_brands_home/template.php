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

?><div class="b-common-section__content b-common-section__content--popular-brand">
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
